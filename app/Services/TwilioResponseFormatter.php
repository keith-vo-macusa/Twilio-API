<?php

namespace App\Services;

use App\Models\Message;
use App\Models\ApiKey;
use Carbon\Carbon;

class TwilioResponseFormatter
{
    /**
     * Format message response giống Twilio
     */
    public static function formatMessage(Message $message): array
    {
        return [
            'account_sid' => $message->account_sid,
            'api_version' => $message->api_version,
            'body' => $message->body,
            'date_created' => $message->date_created,
            'date_sent' => $message->date_sent,
            'date_updated' => $message->date_updated,
            'direction' => $message->direction,
            'error_code' => $message->error_code,
            'error_message' => $message->error_message,
            'from' => $message->from,
            'messaging_service_sid' => $message->messaging_service_sid,
            'num_media' => (string) $message->num_media,
            'num_segments' => (string) $message->num_segments,
            'price' => $message->price,
            'price_unit' => $message->price_unit,
            'sid' => $message->sid,
            'status' => $message->status,
            'subresource_uris' => [
                'media' => '/2010-04-01/Accounts/' . $message->account_sid . '/Messages/' . $message->sid . '/Media.json',
                'feedback' => '/2010-04-01/Accounts/' . $message->account_sid . '/Messages/' . $message->sid . '/Feedback.json',
            ],
            'to' => $message->to,
            'uri' => $message->uri,
        ];
    }

    /**
     * Format list messages response giống Twilio
     */
    public static function formatMessageList($messages, $accountSid, $page = 0, $pageSize = 50, $total = null): array
    {
        $formattedMessages = $messages->map(function ($message) {
            return self::formatMessage($message);
        })->toArray();

        $baseUri = '/2010-04-01/Accounts/' . $accountSid . '/Messages.json';

        return [
            'first_page_uri' => $baseUri . '?PageSize=' . $pageSize . '&Page=0',
            'end' => min(($page + 1) * $pageSize - 1, ($total ?? $messages->count()) - 1),
            'next_page_uri' => ($page + 1) * $pageSize < ($total ?? $messages->count())
                ? $baseUri . '?PageSize=' . $pageSize . '&Page=' . ($page + 1)
                : null,
            'page' => $page,
            'page_size' => $pageSize,
            'previous_page_uri' => $page > 0
                ? $baseUri . '?PageSize=' . $pageSize . '&Page=' . ($page - 1)
                : null,
            'messages' => $formattedMessages,
            'start' => $page * $pageSize,
            'uri' => $baseUri . '?PageSize=' . $pageSize . '&Page=' . $page,
        ];
    }

    /**
     * Tính số segments dựa trên độ dài message
     */
    public static function calculateSegments(string $body): int
    {
        // GSM-7: 160 ký tự/segment, UCS-2: 70 ký tự/segment
        // Nếu có ký tự đặc biệt (non-GSM), sử dụng UCS-2
        $isUcs2 = preg_match('/[^\x00-\x7F]/', $body);
        $charsPerSegment = $isUcs2 ? 70 : 160;

        return (int) ceil(mb_strlen($body) / $charsPerSegment);
    }

    /**
     * Format API Key response giống Twilio v2010
     */
    public static function formatApiKey(ApiKey $apiKey, bool $includeSecret = false): array
    {
        $response = [
            'sid' => $apiKey->sid,
            'friendly_name' => $apiKey->friendly_name,
            'date_created' => $apiKey->date_created,
            'date_updated' => $apiKey->date_updated,
        ];

        // Secret chỉ hiển thị khi tạo mới
        if ($includeSecret && $apiKey->secret) {
            $response['secret'] = $apiKey->secret;
        }

        return $response;
    }

    /**
     * Format list API Keys response giống Twilio v2010
     */
    public static function formatApiKeyList($keys, $accountSid, $page = 0, $pageSize = 50, $total = null): array
    {
        $formattedKeys = $keys->map(function ($key) {
            return self::formatApiKey($key, false);
        })->toArray();

        $baseUri = '/2010-04-01/Accounts/' . $accountSid . '/Keys.json';

        return [
            'first_page_uri' => $baseUri . '?PageSize=' . $pageSize . '&Page=0',
            'end' => min(($page + 1) * $pageSize - 1, ($total ?? $keys->count()) - 1),
            'next_page_uri' => ($page + 1) * $pageSize < ($total ?? $keys->count())
                ? $baseUri . '?PageSize=' . $pageSize . '&Page=' . ($page + 1)
                : null,
            'page' => $page,
            'page_size' => $pageSize,
            'previous_page_uri' => $page > 0
                ? $baseUri . '?PageSize=' . $pageSize . '&Page=' . ($page - 1)
                : null,
            'keys' => $formattedKeys,
            'start' => $page * $pageSize,
            'uri' => $baseUri . '?PageSize=' . $pageSize . '&Page=' . $page,
        ];
    }
}
