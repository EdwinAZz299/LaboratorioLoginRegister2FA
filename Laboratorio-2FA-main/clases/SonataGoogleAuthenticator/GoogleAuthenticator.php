<?php

namespace Sonata\GoogleAuthenticator;

class GoogleAuthenticator
{
    protected $codeLength = 6;

    public function generateSecret()
    {
        $secret = '';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        for ($i = 0; $i < 16; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $secret;
    }

    public function getCode($secret, $timeSlice = null)
    {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }

        $secretkey = $this->base32Decode($secret);

        // Pack 'N' into binary string
        $time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $timeSlice);
        $hmac = hash_hmac('sha1', $time, $secretkey, true);
        $offset = ord(substr($hmac, -1)) & 0x0F;
        $hashpart = substr($hmac, $offset, 4);

        $value = unpack('N', $hashpart);
        $value = $value[1] & 0x7FFFFFFF;

        $modulo = pow(10, $this->codeLength);

        return str_pad($value % $modulo, $this->codeLength, '0', STR_PAD_LEFT);
    }

    public function checkCode($secret, $code, $discrepancy = 1)
    {
        $timeSlice = floor(time() / 30);

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            if ($this->getCode($secret, $timeSlice + $i) == $code) {
                return true;
            }
        }

        return false;
    }

    protected function base32Decode($secret)
    {
        if (empty($secret)) {
            return '';
        }

        $base32chars = $this->getBase32LookupTable();
        $base32charsFlipped = array_flip($base32chars);

        $paddingCharCount = substr_count($secret, '=');
        $allowedValues = array(6, 4, 3, 1, 0);
        if (!in_array($paddingCharCount, $allowedValues)) {
            return false;
        }
        for ($i = 0; $i < 4; $i++) {
            if ($paddingCharCount == $allowedValues[$i] &&
                substr($secret, -(($allowedValues[$i]) + 1)) != str_repeat('=', ($allowedValues[$i]) + 1)) {
                return false;
            }
        }
        $secret = str_replace('=', '', $secret);
        $secret = str_split($secret);
        $binaryString = '';
        for ($i = 0; $i < count($secret); $i = $i + 8) {
            $x = '';
            if (!in_array($secret[$i], $base32chars)) {
                return false;
            }
            for ($j = 0; $j < 8; $j++) {
                if (isset($secret[$i + $j])) {
                    $x .= str_pad(base_convert($base32charsFlipped[$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
                }
            }
            $eightBits = str_split($x, 8);
            for ($z = 0; $z < count($eightBits); $z++) {
                $binaryString .= (($v = bindec($eightBits[$z])) === false) ? chr(256) : chr($v);
            }
        }
        return $binaryString;
    }

    protected function getBase32LookupTable()
    {
        return array(
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  0-7
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', //  8-15
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 16-23
            'Y', 'Z', '2', '3', '4', '5', '6', '7', // 24-31
        );
    }
}
