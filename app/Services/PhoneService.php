<?php

namespace App\Services;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;

class PhoneService
{
    protected static function phoneUtil()
    {
        return PhoneNumberUtil::getInstance();
    }

    public static function normalize(string $phone): array
    {
        $phoneUtil = self::phoneUtil();

        try {
            $phone = trim($phone);
            $phone = str_replace([' ', '-', '(', ')'], '', $phone);

            if (str_starts_with($phone, '0')) {
            $phone = '+44' . substr($phone, 1);
            }

            if (!str_starts_with($phone, '+')) {
                $phone = '+' . $phone;
            }

            // parse number
            $number = $phoneUtil->parse($phone, null);

            // validate number
            if (!$phoneUtil->isValidNumber($number)) {
                return self::invalid("Invalid phone number.");
            }

            // get country code (GB, US, SG, etc.)
            $region = $phoneUtil->getRegionCodeForNumber($number);

            // format to E.164
            $formatted = $phoneUtil->format(
                $number,
                PhoneNumberFormat::E164
            );

            return [
                'valid'     => true,
                'country'   => $region,
                'formatted' => $formatted,
                'message'   => self::countryMessage($region),
            ];

        } catch (NumberParseException $e) {
            return self::invalid("Invalid phone number format.");
        }
    }

    // check if the phone number is valid and return the message if invalid
    protected static function invalid(string $message): array
    {
        return [
            'valid'   => false,
            'country' => null,
            'formatted' => null,
            'message' => $message,
        ];
    }

    // add more validation messages based on country code if needed
    protected static function countryMessage(?string $region): string
    {
        return match ($region) {
            'GB' => 'UK phone number',
            'US' => 'US phone number',
            'SG' => 'Singapore phone number',
            'IN' => 'India phone number',
            'AU' => 'Australia phone number',
            default => 'International phone number',
        };
    }
}