<?php

namespace Sonata\GoogleAuthenticator;

class GoogleQrUrl
{
    public static function generate($user, $secret, $title = null, $params = array())
    {
        $url = "otpauth://totp/" . urlencode($user) . "?secret=" . $secret;

        if ($title) {
            $url .= "&issuer=" . urlencode($title);
        }

        foreach ($params as $key => $value) {
            $url .= "&" . urlencode($key) . "=" . urlencode($value);
        }

        return $url;
    }
}
