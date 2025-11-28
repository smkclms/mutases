<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

function send_wa($number, $message)
{
    $url = "https://waapi.smkn1cilimus.my.id/send"; // IP server WA kamu
    $token = "RAHASIA-123"; // token API dari node

    $data = array(
        "number" => $number,
        "message" => $message
    );

    $payload = json_encode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "token: " . $token
    ));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}
