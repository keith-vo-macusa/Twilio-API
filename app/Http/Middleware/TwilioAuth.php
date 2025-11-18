<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwilioAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $username = $request->getUser();
        $password = $request->getPassword();

        if (empty($username) || empty($password)) {
            return response()->json([
                'code' => 20003,
                'message' => 'Authenticate',
                'more_info' => 'https://www.twilio.com/docs/errors/20003',
                'status' => 401
            ], 401, [
                'WWW-Authenticate' => 'Basic realm="Twilio API"'
            ]);
        }

        // Kiểm tra API Key hoặc Account SID/Auth Token
        $accountSid = config('services.twilio.account_sid');
        $authToken = config('services.twilio.auth_token');
        $apiKey = config('services.twilio.api_key');
        $apiSecret = config('services.twilio.api_secret');

        $isValid = false;
        $authenticatedAccountSid = null;

        // Kiểm tra API Key từ database trước
        // $dbApiKey = \App\Models\ApiKey::where('sid', $username)
        //     ->where('secret', $password)
        //     ->first();

        // if ($dbApiKey) {
        //     $isValid = true;
        //     $authenticatedAccountSid = $dbApiKey->account_sid;
        // }
        // Kiểm tra API Key từ config
        if ($apiKey && $apiSecret && $username === $apiKey && $password === $apiSecret) {
            $isValid = true;
            $authenticatedAccountSid = $accountSid;
        }
        // Kiểm tra Account SID/Auth Token
        elseif ($accountSid && $authToken && $username === $accountSid && $password === $authToken) {
            $isValid = true;
            $authenticatedAccountSid = $accountSid;
        }

        if (!$isValid) {
            return response()->json([
                'code' => 20003,
                'message' => 'Authenticate',
                'more_info' => 'https://www.twilio.com/docs/errors/20003',
                'status' => 401
            ], 401, [
                'WWW-Authenticate' => 'Basic realm="Twilio API"'
            ]);
        }

        // Lưu account_sid vào request để sử dụng trong controller
        $request->merge(['authenticated_account_sid' => $authenticatedAccountSid]);

        return $next($request);
    }
}
