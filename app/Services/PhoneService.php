<?php

namespace App\Services;

class PhoneService
{
    public static function normalize($phone)
    {
        // check for whitespace and dashes and remove them
        $phone = preg_replace('/[\s\-]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '+44' . substr($phone, 1);
        }

        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }
}