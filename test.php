 curl -v https://api.xsolla.com/merchant/merchants/42474/token \
    -X POST \
    -u 42474:tEJkcscBigcbdvbo \
    -H 'Content-Type:application/json' \
    -H 'Accept: application/json' \
    -d '
    {
        "user": {
            "id": {
                "value": "1234567",
                "hidden": true
            },
            "email": {
                "value": "email@example.com"
            },
            "country": {
                "value": "US",
                "allow_modify": true
            }
        },
        "settings": {
            "project_id": 24237
            "language": "en",
            "mode": "sandbox"
        },
        "custom_parameters": {
            "user_level": 80,
            "registration_date": "2014-09-01T19:25:25+04:00"
        }
    }'
