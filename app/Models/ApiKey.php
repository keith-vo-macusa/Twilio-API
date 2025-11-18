<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ApiKey extends Model
{
    protected $fillable = [
        'account_sid',
        'sid',
        'friendly_name',
        'secret',
        'date_created',
        'date_updated',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($apiKey) {
            if (empty($apiKey->sid)) {
                $apiKey->sid = 'SK' . Str::random(32);
            }
            if (empty($apiKey->secret)) {
                $apiKey->secret = Str::random(32);
            }
            if (empty($apiKey->date_created)) {
                $apiKey->date_created = Carbon::now()->utc()->format('D, d M Y H:i:s \+\0\0\0\0');
            }
            if (empty($apiKey->date_updated)) {
                $apiKey->date_updated = Carbon::now()->utc()->format('D, d M Y H:i:s \+\0\0\0\0');
            }
        });

        static::updating(function ($apiKey) {
            $apiKey->date_updated = Carbon::now()->utc()->format('D, d M Y H:i:s \+\0\0\0\0');
        });
    }
}
