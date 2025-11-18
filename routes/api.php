<?php

use App\Http\Controllers\Api\TwilioMessagesController;
use App\Http\Controllers\Api\TwilioKeysController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Twilio Mock API Routes
|--------------------------------------------------------------------------
|
| Các routes này giả lập Twilio API để test hệ thống
|
*/

Route::middleware(['twilio.auth'])->group(function () {
    // Messages API
    Route::post('/2010-04-01/Accounts/{accountSid}/Messages.json', [TwilioMessagesController::class, 'create']);
    Route::get('/2010-04-01/Accounts/{accountSid}/Messages.json', [TwilioMessagesController::class, 'index']);
    Route::get('/2010-04-01/Accounts/{accountSid}/Messages/{messageSid}.json', [TwilioMessagesController::class, 'show']);
    Route::delete('/2010-04-01/Accounts/{accountSid}/Messages/{messageSid}.json', [TwilioMessagesController::class, 'destroy']);

    // Keys API v2010
    Route::post('/2010-04-01/Accounts/{accountSid}/Keys.json', [TwilioKeysController::class, 'create']);
    Route::get('/2010-04-01/Accounts/{accountSid}/Keys.json', [TwilioKeysController::class, 'index']);
    Route::get('/2010-04-01/Accounts/{accountSid}/Keys/{keySid}.json', [TwilioKeysController::class, 'show']);
    Route::post('/2010-04-01/Accounts/{accountSid}/Keys/{keySid}.json', [TwilioKeysController::class, 'update']);
    Route::delete('/2010-04-01/Accounts/{accountSid}/Keys/{keySid}.json', [TwilioKeysController::class, 'destroy']);
});
