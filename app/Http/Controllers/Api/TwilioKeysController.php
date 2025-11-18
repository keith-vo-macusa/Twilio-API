<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Services\TwilioResponseFormatter;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TwilioKeysController extends Controller
{
    /**
     * POST /2010-04-01/Accounts/{AccountSid}/Keys.json
     * Tạo API Key mới
     */
    public function create(Request $request, string $accountSid)
    {
        // Kiểm tra Account SID phải khớp với authenticated account
        $authenticatedAccountSid = $request->get('authenticated_account_sid');
        if ($accountSid !== $authenticatedAccountSid) {
            return response()->json([
                'code' => 20003,
                'message' => 'Authenticate',
                'more_info' => 'https://www.twilio.com/docs/errors/20003',
                'status' => 401
            ], 401);
        }

        // Validate request
        $validated = $request->validate([
            'FriendlyName' => 'nullable|string|max:64',
        ]);

        // Tạo API Key
        $apiKey = new ApiKey();
        $apiKey->account_sid = $accountSid;
        $apiKey->friendly_name = $validated['FriendlyName'] ?? null;
        $apiKey->save();

        return response()->json(TwilioResponseFormatter::formatApiKey($apiKey, true), 201);
    }

    /**
     * GET /2010-04-01/Accounts/{AccountSid}/Keys.json
     * Lấy danh sách API Keys
     */
    public function index(Request $request, string $accountSid)
    {
        // Kiểm tra Account SID phải khớp với authenticated account
        $authenticatedAccountSid = $request->get('authenticated_account_sid');
        if ($accountSid !== $authenticatedAccountSid) {
            return response()->json([
                'code' => 20003,
                'message' => 'Authenticate',
                'more_info' => 'https://www.twilio.com/docs/errors/20003',
                'status' => 401
            ], 401);
        }

        $query = ApiKey::where('account_sid', $accountSid);

        // Pagination
        $pageSize = min((int) ($request->get('PageSize', 50)), 1000);
        $page = (int) ($request->get('Page', 0));

        $total = $query->count();
        $keys = $query->orderBy('date_created', 'desc')
            ->skip($page * $pageSize)
            ->take($pageSize)
            ->get();

        return response()->json(
            TwilioResponseFormatter::formatApiKeyList($keys, $accountSid, $page, $pageSize, $total)
        );
    }

    /**
     * GET /2010-04-01/Accounts/{AccountSid}/Keys/{KeySid}.json
     * Lấy thông tin một API Key cụ thể
     */
    public function show(Request $request, string $accountSid, string $keySid)
    {
        // Kiểm tra Account SID phải khớp với authenticated account
        $authenticatedAccountSid = $request->get('authenticated_account_sid');
        if ($accountSid !== $authenticatedAccountSid) {
            return response()->json([
                'code' => 20003,
                'message' => 'Authenticate',
                'more_info' => 'https://www.twilio.com/docs/errors/20003',
                'status' => 401
            ], 401);
        }

        $apiKey = ApiKey::where('account_sid', $accountSid)
            ->where('sid', $keySid)
            ->first();

        if (!$apiKey) {
            return response()->json([
                'code' => 20404,
                'message' => 'The requested resource /2010-04-01/Accounts/' . $accountSid . '/Keys/' . $keySid . '.json was not found',
                'more_info' => 'https://www.twilio.com/docs/errors/20404',
                'status' => 404
            ], 404);
        }

        return response()->json(TwilioResponseFormatter::formatApiKey($apiKey, false));
    }

    /**
     * POST /2010-04-01/Accounts/{AccountSid}/Keys/{KeySid}.json
     * Cập nhật API Key
     */
    public function update(Request $request, string $accountSid, string $keySid)
    {
        // Kiểm tra Account SID phải khớp với authenticated account
        $authenticatedAccountSid = $request->get('authenticated_account_sid');
        if ($accountSid !== $authenticatedAccountSid) {
            return response()->json([
                'code' => 20003,
                'message' => 'Authenticate',
                'more_info' => 'https://www.twilio.com/docs/errors/20003',
                'status' => 401
            ], 401);
        }

        $apiKey = ApiKey::where('account_sid', $accountSid)
            ->where('sid', $keySid)
            ->first();

        if (!$apiKey) {
            return response()->json([
                'code' => 20404,
                'message' => 'The requested resource /2010-04-01/Accounts/' . $accountSid . '/Keys/' . $keySid . '.json was not found',
                'more_info' => 'https://www.twilio.com/docs/errors/20404',
                'status' => 404
            ], 404);
        }

        // Validate request
        $validated = $request->validate([
            'FriendlyName' => 'nullable|string|max:64',
        ]);

        if (isset($validated['FriendlyName'])) {
            $apiKey->friendly_name = $validated['FriendlyName'];
            $apiKey->save();
        }

        return response()->json(TwilioResponseFormatter::formatApiKey($apiKey, false));
    }

    /**
     * DELETE /2010-04-01/Accounts/{AccountSid}/Keys/{KeySid}.json
     * Xóa API Key
     */
    public function destroy(Request $request, string $accountSid, string $keySid)
    {
        // Kiểm tra Account SID phải khớp với authenticated account
        $authenticatedAccountSid = $request->get('authenticated_account_sid');
        if ($accountSid !== $authenticatedAccountSid) {
            return response()->json([
                'code' => 20003,
                'message' => 'Authenticate',
                'more_info' => 'https://www.twilio.com/docs/errors/20003',
                'status' => 401
            ], 401);
        }

        $apiKey = ApiKey::where('account_sid', $accountSid)
            ->where('sid', $keySid)
            ->first();

        if (!$apiKey) {
            return response()->json([
                'code' => 20404,
                'message' => 'The requested resource /2010-04-01/Accounts/' . $accountSid . '/Keys/' . $keySid . '.json was not found',
                'more_info' => 'https://www.twilio.com/docs/errors/20404',
                'status' => 404
            ], 404);
        }

        $apiKey->delete();

        return response()->json(null, 204);
    }
}
