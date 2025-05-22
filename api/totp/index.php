<?php
header('Content-Type: application/json');

// Chave secreta em Base32 (a mesma usada no app autenticador)
$secret = 'J6GLGQA2M5CAFTEN';

function base32_decode($b32) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $b32 = strtoupper($b32);
    $l = strlen($b32);
    $n = 0;
    $j = 0;
    $binary = '';

    for ($i = 0; $i < $l; $i++) {
        $n = $n << 5;
        $n = $n + strpos($alphabet, $b32[$i]);
        $j += 5;

        if ($j >= 8) {
            $j -= 8;
            $binary .= chr(($n & (0xFF << $j)) >> $j);
        }
    }

    return $binary;
}

function get_totp($secret, $interval = 30, $digits = 6) {
    $key = base32_decode($secret);
    $time = floor(time() / $interval);
    $bin_time = pack('N*', 0) . pack('N*', $time); // 64-bit int

    $hash = hash_hmac('sha1', $bin_time, $key, true);
    $offset = ord($hash[19]) & 0x0F;
    $part = substr($hash, $offset, 4);

    $value = unpack('N', $part)[1] & 0x7FFFFFFF;
    $modulo = pow(10, $digits);

    return str_pad($value % $modulo, $digits, '0', STR_PAD_LEFT);
}

// Gera o código TOTP
$totp = get_totp($secret);

echo json_encode([
    'totp' => $totp
]);
