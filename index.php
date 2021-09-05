<?php
include_once ('../core/base.php');
$header = 'Buy currency';
include_once ('../core/func.php');


falseauth();
include_once ('../core/head.php');
if (isset($_GET['gold'])){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'https://api.xsolla.com/merchant/merchants/42474/token');
    $h = array("Content-Type: application/json");
    curl_setopt($curl, CURLOPT_HTTPHEADER, $h);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, '42474:FCRrmtVf0ijbeATb');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl, CURLOPT_POST, true);
    $json = array("user" => array("id" => array("value" => $user['id'], "hidden" => true)), "settings" => array("project_id" => , "mode" => "));
    $json = json_encode($json);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
    $token = json_decode(curl_exec($curl))->token;
    curl_close($curl);
    if (isset($token)) {
       header("Location:https://sandbox-secure.xsolla.com/paystation2/?access_token=$token");
     //   header("Location:https://secure.xsolla.com/paystation2/?access_token=$token");
        exit;
    } else {
      $_SESSION['error']='Ошибка платежной системы - обратитесь к администратору';
        header("Location:/xsolla/");
        exit;
    }
}
$title = 'Золото';
//Подключаем шапку //
include '../system/h.php';
echo "<div class='block'>";
echo "</div>";
echo "<div class='block'>";
echo "<div class='center'><a href='?gold' class='btn2'>Перейти к оплате</a></div><br>";
echo "</div>";


include_once ('../core/foot.php');
?>
