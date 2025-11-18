<?php

namespace App\Services;

class TwilioMagicNumbers
{
    /**
     * Magic numbers cho From
     */
    private const FROM_MAGIC_NUMBERS = [
        '+15005550001' => [
            'error_code' => 21212,
            'message' => "The 'From' phone number provided is not a valid, SMS-capable inbound phone number or short code for your account.",
            'more_info' => 'https://www.twilio.com/docs/errors/21212',
        ],
        '+15005550007' => [
            'error_code' => 21606,
            'message' => "The 'From' phone number provided is not a valid, SMS-capable inbound phone number or short code for your account.",
            'more_info' => 'https://www.twilio.com/docs/errors/21606',
        ],
        '+15005550008' => [
            'error_code' => 21611,
            'message' => "The 'From' phone number provided is not a valid, SMS-capable inbound phone number or short code for your account.",
            'more_info' => 'https://www.twilio.com/docs/errors/21611',
        ],
        '+15005550006' => [
            'error_code' => null,
            'message' => null,
            'more_info' => null,
        ],
    ];

    /**
     * Magic numbers cho To
     * Một số trả về error ngay, một số vẫn tạo message nhưng có error_code
     */
    private const TO_MAGIC_NUMBERS = [
        '+15005550001' => [
            'error_code' => 21211,
            'message' => "Invalid 'To' Phone Number",
            'more_info' => 'https://www.twilio.com/docs/errors/21211',
            'return_error' => true, // Trả về error ngay
        ],
        '+15005550002' => [
            'error_code' => 21612,
            'message' => "Unable to create record. We're sorry, an error has occurred. Please try again later.",
            'more_info' => 'https://www.twilio.com/docs/errors/21612',
            'return_error' => true,
        ],
        '+15005550003' => [
            'error_code' => 21408,
            'message' => "Permission to send an SMS has not been enabled for the region indicated by the 'To' number.",
            'more_info' => 'https://www.twilio.com/docs/errors/21408',
            'return_error' => true,
        ],
        '+15005550004' => [
            'error_code' => 21610,
            'message' => "The 'To' number +15005550004 is not a valid mobile number",
            'more_info' => 'https://www.twilio.com/docs/errors/21610',
            'return_error' => true,
        ],
        '+15005550009' => [
            'error_code' => 21614,
            'message' => "The 'To' number +15005550009 is not a valid mobile number",
            'more_info' => 'https://www.twilio.com/docs/errors/21614',
            'return_error' => false, // Vẫn tạo message nhưng có error_code
        ],
    ];

    /**
     * Kiểm tra và trả về error nếu là magic number From
     */
    public static function checkFromNumber(string $from): ?array
    {
        // Kiểm tra magic numbers
        if (isset(self::FROM_MAGIC_NUMBERS[$from])) {
            $magic = self::FROM_MAGIC_NUMBERS[$from];
            
            // Nếu có error code, trả về error
            if ($magic['error_code'] !== null) {
                return [
                    'code' => $magic['error_code'],
                    'message' => $magic['message'],
                    'more_info' => $magic['more_info'],
                    'status' => 400,
                ];
            }
            
            // +15005550006 là valid, return null để tiếp tục
            return null;
        }

        // Nếu không phải magic number và không phải số Mỹ hợp lệ, trả về error
        if (!preg_match('/^\+1[0-9]{10}$/', $from)) {
            return [
                'code' => 21606,
                'message' => "The 'From' phone number provided is not a valid, SMS-capable inbound phone number or short code for your account.",
                'more_info' => 'https://www.twilio.com/docs/errors/21606',
                'status' => 400,
            ];
        }

        return null;
    }

    /**
     * Kiểm tra và trả về error nếu là magic number To (chỉ trả về error nếu return_error = true)
     */
    public static function checkToNumber(string $to): ?array
    {
        // Kiểm tra magic numbers
        if (isset(self::TO_MAGIC_NUMBERS[$to])) {
            $magic = self::TO_MAGIC_NUMBERS[$to];
            
            // Chỉ trả về error nếu return_error = true
            if ($magic['return_error']) {
                return [
                    'code' => $magic['error_code'],
                    'message' => $magic['message'],
                    'more_info' => $magic['more_info'],
                    'status' => 400,
                ];
            }
        }

        // Nếu không phải magic number hoặc không return_error, validate bình thường
        // Cho phép bất kỳ số nào (không chỉ số Mỹ) để giống Twilio test
        return null;
    }

    /**
     * Kiểm tra xem có phải magic number không (để set status/error_code trong message)
     */
    public static function getMessageErrorForTo(string $to): ?array
    {
        if (isset(self::TO_MAGIC_NUMBERS[$to])) {
            $magic = self::TO_MAGIC_NUMBERS[$to];
            return [
                'error_code' => $magic['error_code'],
                'error_message' => $magic['message'],
                'status' => 'failed',
            ];
        }

        return null;
    }
}

