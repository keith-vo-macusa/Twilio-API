<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Message extends Model
{
    protected $fillable = [
        'account_sid',
        'api_version',
        'body',
        'date_created',
        'date_sent',
        'date_updated',
        'direction',
        'error_code',
        'error_message',
        'from',
        'messaging_service_sid',
        'num_media',
        'num_segments',
        'price',
        'price_unit',
        'sid',
        'status',
        'to',
        'uri',
    ];

    protected $casts = [
        'num_media' => 'integer',
        'num_segments' => 'integer',
        'error_code' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($message) {
            if (empty($message->sid)) {
                $message->sid = 'SM' . Str::random(32);
            }
            if (empty($message->date_created)) {
                $message->date_created = now()->utc()->format('D, d M Y H:i:s \+\0\0\0\0');
            }
            if (empty($message->date_updated)) {
                $message->date_updated = now()->utc()->format('D, d M Y H:i:s \+\0\0\0\0');
            }
            if (empty($message->uri)) {
                $message->uri = '/2010-04-01/Accounts/' . $message->account_sid . '/Messages/' . $message->sid . '.json';
            }
        });
    }
}
