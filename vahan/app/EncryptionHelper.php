
<?php

function customEncrypt($data, $key)
{
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encryptedData = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $encryptedData);
}

function customDecrypt($data, $key)
{
    $data = base64_decode($data);
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $ivLength);
    $encryptedDataWithoutIV = substr($data, $ivLength);
    return openssl_decrypt($encryptedDataWithoutIV, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
}

?>