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
