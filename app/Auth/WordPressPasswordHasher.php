<?php

namespace App\Auth;

use Illuminate\Support\Facades\Hash;

class WordPressPasswordHasher
{
    public static function check(string $password, string $hash): bool
    {
        if ($hash === '') {
            return false;
        }

        if (str_starts_with($hash, '$wp')) {
            $stripped = substr($hash, 3);
            $prehashed = base64_encode(hash_hmac('sha384', $password, 'wp-sha384', true));

            return password_verify($prehashed, $stripped);
        }

        if (str_starts_with($hash, '$P$') || str_starts_with($hash, '$H$')) {
            return static::checkPhpass($password, $hash);
        }

        return Hash::check($password, $hash);
    }

    protected static function checkPhpass(string $password, string $hash): bool
    {
        if (strlen($hash) !== 34) {
            return false;
        }

        $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $countLog2 = strpos($itoa64, $hash[3]);

        if ($countLog2 === false || $countLog2 < 7 || $countLog2 > 30) {
            return false;
        }

        $count = 1 << $countLog2;
        $salt = substr($hash, 4, 8);

        if (strlen($salt) !== 8) {
            return false;
        }

        $hashed = md5($salt.$password, true);

        for ($i = 0; $i < $count; $i++) {
            $hashed = md5($hashed.$password, true);
        }

        $output = substr($hash, 0, 12).static::encode64($hashed, 16);

        return hash_equals($hash, $output);
    }

    protected static function encode64(string $input, int $count): string
    {
        $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $output = '';
        $i = 0;

        do {
            $value = ord($input[$i++]);
            $output .= $itoa64[$value & 0x3F];

            if ($i < $count) {
                $value |= ord($input[$i]) << 8;
            }

            $output .= $itoa64[($value >> 6) & 0x3F];

            if ($i++ >= $count) {
                break;
            }

            if ($i < $count) {
                $value |= ord($input[$i]) << 16;
            }

            $output .= $itoa64[($value >> 12) & 0x3F];

            if ($i++ >= $count) {
                break;
            }

            $output .= $itoa64[($value >> 18) & 0x3F];
        } while ($i < $count);

        return $output;
    }
}
