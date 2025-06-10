<?php

namespace Core;

use Exception;

class JWT {
    // private static $secret = $_ENV['JWT_SECRET'] ?? 'default_secret_key';

    public static function encode($payload, $exp = 3600) {
        $secret = $_ENV['APP_SECRET'];

        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload['exp'] = time() + $exp;

        $base64UrlHeader = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $base64UrlPayload = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $secret, true);
        $base64UrlSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }

    public static function decode($jwt) {
        $secret = $_ENV['APP_SECRET'];
        [$header, $payload, $signature] = explode('.', $jwt);
        $validSignature = base64_encode(hash_hmac('sha256', "$header.$payload", $secret, true));

        $payloadDecoded = json_decode(base64_decode($payload), true);

        if ($payloadDecoded['exp'] < time()) {
            throw new Exception('Token expired');
        }

        if ($validSignature !== $signature) {
            throw new Exception('Invalid token signature');
        }

        return $payloadDecoded;
    }
}
