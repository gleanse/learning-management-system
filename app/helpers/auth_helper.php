<?php

function getUserIP()
{
    $ip = null;

    // priority order of headers to check
    $headers = [
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR',
    ];

    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            // extracting first ip if multiple proxies exist
            $ips = explode(',', $_SERVER[$header]);
            $ip = trim($ips[0]);

            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }

    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

function setRememberMeCookie($user_id, $user_model) {
    $token = bin2hex(random_bytes(32));
    // hash for database storage
    $tokenHash = hash('sha256', $token);
    // set expiry (30 days from now) will decide to adjust later
    $expiresAt = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60));
    // function receives an already instantiated user model object from the controller through dependency injection
    $user_model->saveRememberToken($user_id, $tokenHash, $expiresAt);

    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
}
