<?php
function check_ip() 
    { 
    $white_ip = array( 
        '94.103.26.177', 
        '94.103.26.178', 
        '94.103.26.179', 
        '94.103.26.180', 
        '94.103.26.181', 
        '94.103.26.182', 
        '159.255.220.241', 
        '159.255.220.242', 
        '159.255.220.243', 
        '159.255.220.244', 
        '159.255.220.245', 
        '159.255.220.246', 
        '159.255.220.247', 
        '159.255.220.248', 
        '159.255.220.249', 
        '159.255.220.250', 
        '159.255.220.251', 
        '159.255.220.252', 
        '159.255.220.253', 
        '159.255.220.254', 
        '185.30.20.17', 
        '185.30.20.18', 
        '185.30.20.19', 
        '185.30.20.20', 
        '185.30.20.21', 
        '185.30.20.22', 
        '185.30.21.17', 
        '185.30.21.18', 
        '185.30.21.19', 
        '185.30.21.20', 
        '185.30.21.21', 
        '185.30.21.22' 
        ); 
    if (!in_array($_SERVER['REMOTE_ADDR'], $white_ip)) return false; 
    return true; 
} 

// ответ для xsolla 
function response($id = 400, $code = '', $message = '') 
    { 
    header ("HTTP/1.1 $id"); 
    $json = array("error" => array("code" => $code, "message" => $message)); 
    echo json_encode($json); 
    exit; 
    } 

// проверка существования игрока 
function check_user($id) 
    { 
    global $db; 
    $id = abs(intval($id)); 

    if ($id == 0) return false; 

    $sql = $db->prepare('SELECT COUNT(*) FROM `users` WHERE `id` = ? LIMIT 1'); 
    $sql->execute([$id]); 
    if ($sql->fetchColumn() > 0) return true; 

    return false; 
    } 

// проверка подписи 
function check_sign($req, $xsolla, $sign) 
    { 
    $sign_my = 'Signature '.sha1($req.$xsolla); 

    if ($sign_my != $sign) return false; 
    return true; 
    } 

// успешный платеж 
function payment($t_id, $p_date, $p_curr, $p_amount, $id_user, $v_name, $v_count) 
    { 
    global $db; 
    $sql = $db->prepare("INSERT INTO `xsolla_payment` (`transaction_id`, 
                                                       `payment_date`, 
                                                       `payment_currency`, 
                                                       `payment_amount`, 
                                                       `id_user`, 
                                                       `currency_name`, 
                                                       `currency_count`) VALUES (?, ?, ?, ?, ?, ?, ?)"); 
    $sql->execute([$t_id, $p_date, $p_curr, $p_amount, $id_user, $v_name, $v_count]); 
        if ($v_name == 'gold') { 
        $sql = $db->prepare('SELECT COUNT(*) FROM `users` WHERE `id` = ? LIMIT 1'); 
        $sql->execute([$id_user]); 
            if ($sql->fetchColumn() > 0) { 
                $sql = $db->prepare('UPDATE `users` SET `gold` = `gold` + ? WHERE `id` = ? LIMIT 1'); 
                $sql->execute(array($v_count, $id_user)); 
                return true; 
            } else { 
                return false; 
            } 
        } else { 
            return false; 
        } 
    } 


// отмена платежа 
function refund($p_curr, $p_amount, $id_user, $v_name, $v_count) 
    { 
    global $db; 
    $sql = $db->prepare("INSERT INTO `xsolla_payment` (`transaction_id`, 
                                                       `payment_date`, 
                                                       `payment_currency`, 
                                                       `payment_amount`, 
                                                       `id_user`, 
                                                       `currency_name`, 
                                                       `currency_count`) VALUES (?, ?, ?, ?, ?, ?, ?)"); 
    $sql->execute([0, 'REFUSAL_TO_PAY', $p_curr, $p_amount, $id_user, $v_name, $v_count]); 
    return true; 
    } 


if (!check_ip()) response(500, 'INVALID_IP'); 


define('DB_HOST', 'localhost');                # Host;
define('DB_PORT', '3306');                     # Port;
define('DB_NAME', 'epicwar_lrwrp');                # Name;
define('DB_USER', 'epicwar_lrwrp');                # User;
define('DB_PASS', 'PP2Zk0Pm5st574vQFGxm');              # Password;
define('XSOLLA_CODE', 'p3Wu6ipzJpFC9o6e'); // секретный код проекта

// входящие заголовки 
$header = getallheaders(); 

// print_r($header); 

// проверка подписи 
if (!check_sign($HTTP_RAW_POST_DATA, XSOLLA_CODE, $header['Authorization'])) response(400, 'INVALID_SIGNATURE'); 

// входящие данные 
$req = json_decode($HTTP_RAW_POST_DATA); 

try { 
    $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=UTF8", DB_USER, DB_PASS); 
} catch (PDOException $e) { 
    response(500, 'INVALID_BD'); 
} 

// тип запроса 
$type = $req->notification_type; 
// ид пользователя 
$id = $req->user->id; 

if ($type == 'user_validation') { 
    if (check_user($id)) response(204); 
    else response(400 , 'INVALID_USER'); 
} elseif ($type == 'payment') { 
//     Название валюты 
    $valuta_name = $req->purchase->virtual_currency->name; 
//     Количество валюты 
    $valuta_count = $req->purchase->virtual_currency->quantity; 
//     Ид платежа в xsolla 
    $transaction_id = $req->transaction->id; 
//     Дата платежа в xsolla 
    $payment_date = $req->transaction->payment_date; 
//     Валюта платежа 
    $payment_currency = $req->payment_details->payment->currency; 
//     Сумма платежа в валюте 
    $payment_amount = $req->payment_details->payment->amount; 

//     Зачисляем валюту пользователю 
    if (payment($transaction_id, $payment_date, $payment_currency, $payment_amount, $id, $valuta_name, $valuta_count)) { 
    response(204); 
    } else { 
    response(400, 'INVALID_PARAMETER'); 
    } 
} elseif ($type == 'refund') { 
//     Название валюты 
    $valuta_name = $req->purchase->virtual_currency->name; 
//     Количество валюты 
    $valuta_count = $req->purchase->virtual_currency->quantity; 
//     Валюта платежа 
    $payment_currency = $req->purchase->virtual_currency->currency; 
//     Сумма платежа в валюте 
    $payment_amount = $req->purchase->virtual_currency->amount; 

//     Вносим отмененный платеж в статистику 
    if (refund($payment_currency, $payment_amount, $id, $valuta_name, $valuta_count)) { 
    response(204); 
    } else { 
    response(400, 'INVALID_PARAMETER'); 
    } 
} else { 
    response(400, 'INCORRECT_INVOICE'); 
} 
