<?php

require_once __DIR__ . '/../config/jwt.php';

function base64UrlEncode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode($data)
{
    $pad = strlen($data) % 4;
    if ($pad)
        $data .= str_repeat('=', 4 - $pad);
    return base64_decode(strtr($data, '-_', '+/'));
}

function generarJWT(array $payload)
{
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];

    $headerEncoded = base64UrlEncode(json_encode($header));
    $payloadEncoded = base64UrlEncode(json_encode($payload));

    $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", JWT_SECRET, true);
    $signatureEncoded = base64UrlEncode($signature);

    return "$headerEncoded.$payloadEncoded.$signatureEncoded";
}

function validarJWT(string $jwt): ?array
{
    $parts = explode('.', $jwt);

    if (count($parts) !== 3)
        return null;

    [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

    $signatureCheck = base64UrlEncode(hash_hmac('sha256', "$headerEncoded.$payloadEncoded", JWT_SECRET, true));

    if (!hash_equals($signatureCheck, $signatureEncoded))
        return null;

    $payload = json_decode(base64UrlDecode($payloadEncoded), true);

    return $payload;
}
