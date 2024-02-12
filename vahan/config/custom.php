<?php
return [
    'low_credit_limit' => 50,
    'max_credit_limit' => 20000000,
    //api for ac_auth_bridge
    'rc_authbridge' => [
        'rc_authbridge_url' => 'https://rc-webapi.edas.tech/rc_authbridge/Api/leadVahan',
        'rc_authbridge_token' => '7e3b270222252b2dadd547fb',
    ],
    //api for challan 
    'challan' => [
        'challan_url' => 'https://rc-webapi.edas.tech/challan/Api/leadVahan',
        'challan_token' => '7e3b270222252b2dadd547fb',
    ],
    'challan_chassis' => [
        'challan_url' => 'https://api.emptra.com/emptra/vehicleChallanInfo',
        'clientId' => 'a91c056f0527e1afddefc8021e5e6729:898e2dc71d7f0ed9dcda62e95ddf0cd4',
        'secretKey' => 'lvX99tB5L6hx7oTFfASgor4tLZcYEZXFR2Z2n0BaBzp69omlNf6TlxxneAI6LooPF',
    ],

    //api for driving license
    'license' => [
        'license_url' => 'https://rc-webapi.edas.tech/DL_VAHAN_API/Api/CheckVahan',
        'license_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9',
    ],
    'invincible' => [
        'rc' => [
            'url' => 'https://api.invincibleocean.com/invincible/vehicleByChassis',
            'clientId' => 'a91c056f0527e1afddefc8021e5e6729:898e2dc71d7f0ed9dcda62e95ddf0cd4',
            'secretKey' => 'lvX99tB5L6hx7oTFfASgor4tLZcYEZXFR2Z2n0BaBzp69omlNf6TlxxneAI6LooPF',
            'api_id' => '10',
            'api_name' => 'rc',
            'vender' => 'invincible'
        ],
        'rc_chassis' => [
            'url' => 'https://api.invincibleocean.com/invincible/vehicleByChassis',
            'clientId' => 'a91c056f0527e1afddefc8021e5e6729:898e2dc71d7f0ed9dcda62e95ddf0cd4',
            'secretKey' => 'lvX99tB5L6hx7oTFfASgor4tLZcYEZXFR2Z2n0BaBzp69omlNf6TlxxneAI6LooPF',
            'api_id' => '9',
            'api_name' => 'rc_chassis',
            'vender' => 'invincible'
        ],
        'challan_chassis' => [
            'challan_url' => 'https://api.emptra.com/emptra/vehicleChallanInfo',
            'clientId' => 'a91c056f0527e1afddefc8021e5e6729:898e2dc71d7f0ed9dcda62e95ddf0cd4',
            'secretKey' => 'lvX99tB5L6hx7oTFfASgor4tLZcYEZXFR2Z2n0BaBzp69omlNf6TlxxneAI6LooPF',
            'api_id' => '8',
            'api_name' => 'challan_chassis',
            'vender' => 'invincible'
        ]
    ],
    'signzy' => [
        'rc' => [
            'url' => 'https://signzy.tech/api/v2/patrons/6412a199573cb600295a8f1f/vehicleregistrations',
            'Authorization' => 'sZHwxSnw3CLjvPesW3sT0ePjddFs66G00zKhx7SQt7bkfGMHQ5TKVvWZFcyVCkO2',
            'api_id' => '1',
            'api_name' => 'rc',
            'vender' => 'signzy'
        ],
        'challan' => [
            'url' => 'https://signzy.tech/api/v2/patrons/6412a199573cb600295a8f1f/vehicleregistrations',
            'Authorization' => 'sZHwxSnw3CLjvPesW3sT0ePjddFs66G00zKhx7SQt7bkfGMHQ5TKVvWZFcyVCkO2',
            'api_id' => '2',
            'api_name' => 'challan',
            'vender' => 'signzy'
        ],
        'license' => [
            'url' => 'https://preproduction.signzy.tech/api/v2/snoops',
            'accessToken' => '7ut5ejo4quodooho6a3r2h555n939caso',
            'itemId' => '645cc8f11fb1350203242095',
            'api_id' => '6',
            'api_name' => 'license',
            'vender' => 'signzy',
            'service' => 'Identity',
            'task' => 'fetch'
        ]
    ],
    'authbridge' => [
        'rc' => [
            'encrypted_string_url' => 'https://www.truthscreen.com/InstantSearch/encrypted_string',
            'utilitysearch_url' => 'https://www.truthscreen.com/api/v2.2/utilitysearch',
            'decrypt_encrypted_string_url' => 'https://www.truthscreen.com/InstantSearch/decrypt_encrypted_string',
            'username' => 'prod_tataaig@edas.tech',
            'doc_type' => '372',
            'api_id' => '4',
            'api_name' => 'rc',
            'vender' => 'authbridge'
        ],
        'challan' => [
            'encrypted_string_url' => 'https://www.truthscreen.com/InstantSearch/encrypted_string',
            'utilitysearch_url' => 'https://www.truthscreen.com/EChallanApi/echallanRc',
            'decrypt_encrypted_string_url' => 'https://www.truthscreen.com/InstantSearch/decrypt_encrypted_string',
            'username' => 'prod_tataaig@edas.tech',
            'doc_type' => '487',
            'api_id' => '5',
            'api_name' => 'challan',
            'vender' => 'authbridge'
        ],
        'license' => [
            'encrypted_string_url' => 'https://www.truthscreen.com/InstantSearch/encrypted_string',
            'utilitysearch_url' => 'https://www.truthscreen.com/api/v2.2/idsearch',
            'decrypt_encrypted_string_url' => 'https://www.truthscreen.com/InstantSearch/decrypt_encrypted_string',
            'username' => 'test@edas.tech',
            'docType' => '326',
            'api_id' => '7',
            'api_name' => 'license',
            'vender' => 'authbridge'
        ],
        'pancard' => [
            'encrypted_string_url' => 'https://www.truthscreen.com/v1/apicall/encrypt',
            'utilitysearch_url' => 'https://www.truthscreen.com/v1/apicall/nid/panComprehensive',
            'decrypt_encrypted_string_url' => 'https://www.truthscreen.com/v1/apicall/decrypt',
            'username' => 'test@edas.tech',
            'docType' => '523',
            'api_id' => '19',
            'api_name' => 'pancard',
            'vender' => 'authbridge'
        ],
        'ocr' => [
            'token_url' => 'https://www.truthscreen.com/api/v2.2/idocr/token',
            'token_decrypt_url' => 'https://www.truthscreen.com/InstantSearch/decrypt_encrypted_string',
            'encrypted_url' => 'https://www.truthscreen.com/api/v2.2/idocr/tokenEncrypt',
            'verify_url' => 'https://www.truthscreen.com/api/v2.2/idocr/verify',
            'decrypt_encrypted_string_url' => 'https://www.truthscreen.com/InstantSearch/decrypt_encrypted_string',
            'username' => 'test@edas.tech',
            'docType' => '326',
            'api_id' => '18',
            'api_name' => 'pan_ocr',
            'vender' => 'authbridge'
        ],
    ],
    'edas_internal' => [
        'rc_logic' => [
            'url' => 'https://rc-webapi.edas.tech/taig/api/leadVahan',
            'token' => '7e3b270222252b2dadd547fb',
            'api_id' => '12',
            'api_name' => 'rc_logic',
            'vender' => 'edas_internal'
        ]
    ],
    'rto' => [
        'challan' =>[
                'url' => 'http://api.inbillsolutions.com/bbuser/vehicle_challan_detail',
                'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyZW1haWwiOiJ2aWJpbi52aW5jZW50QGVkYXMudGVjaCIsImlhdCI6MTY4ODE4ODY4NCwiZXhwIjoxNzE5NzI0Njg0fQ.42d_Ot0VN37Iy3Siwb9gTn9_mxoZ8yR_ZTQnNE7qKB8',
                'user_id' => '649fb70cb24c5f4d788169f0',
                'api_name' => 'challan',
                'vender' => 'rto',
                'api_id' => '13',
        ],
        'rc' =>[
            'url' => 'http://api.inbillsolutions.com/bbuser/vehicle_registration_detail_prime_reverse',
            'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyZW1haWwiOiJ2aWJpbi52aW5jZW50QGVkYXMudGVjaCIsImlhdCI6MTY4ODE4ODY4NCwiZXhwIjoxNzE5NzI0Njg0fQ.42d_Ot0VN37Iy3Siwb9gTn9_mxoZ8yR_ZTQnNE7qKB8',
            'user_id' => '649fb70cb24c5f4d788169f0',
            //aslo require engine number in body
            'api_name' => 'rc',
            'vender' => 'rto',
            'api_id' => '12',
        ]
        ],
    'digitap' =>[
        'license' =>[
            'url' => 'https://svcdemo.digitap.work/validation/kyc/v1/dl',
            'Authorization' => 'Basic NTMzNDU1Mjc6Q1JqSDR4TXgySFVXTzRZMXp1NmtmSUxTemplY0pBYkc=',
            'api_name' => 'license',
            'vender' => 'digitap',
            'client_ref_num' => '1234',
            'api_id' => '17',
        ]
    ]

];

?>