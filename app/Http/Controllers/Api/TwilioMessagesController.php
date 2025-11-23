<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Services\TwilioResponseFormatter;
use App\Services\TwilioMagicNumbers;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TwilioMessagesController extends Controller
{
    /**
     * POST /2010-04-01/Accounts/{AccountSid}/Messages.json
     * Gửi SMS mới
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
            'To' => 'required|string',
            'From' => 'required_without:MessagingServiceSid|string',
            'MessagingServiceSid' => 'required_without:From|string',
            'Body' => 'required_without:MediaUrl|string|max:1600',
            'MediaUrl' => 'array',
            'MediaUrl.*' => 'url',
            'StatusCallback' => 'nullable|url',
        ], [
            'To.required' => 'The To parameter is required.',
            'From.required_without' => 'Either From or MessagingServiceSid must be provided.',
            'MessagingServiceSid.required_without' => 'Either From or MessagingServiceSid must be provided.',
            'Body.required_without' => 'Either Body or MediaUrl must be provided.',
        ]);

        // Kiểm tra magic numbers và validate số điện thoại
        $to = $validated['To'];
        $from = $validated['From'] ?? null;

        // Kiểm tra From number (magic numbers hoặc validation)
        if ($from) {
            $fromError = TwilioMagicNumbers::checkFromNumber($from);
            if ($fromError) {
                return response()->json($fromError, $fromError['status']);
            }
        }

        // Kiểm tra To number (magic numbers)
        $toError = TwilioMagicNumbers::checkToNumber($to);
        if ($toError) {
            return response()->json($toError, $toError['status']);
        }

        // Kiểm tra error từ magic numbers (một số magic numbers vẫn tạo message nhưng có error)
        $toErrorInfo = TwilioMagicNumbers::getMessageErrorForTo($to);

        // Tạo message
        $message = new Message();
        $message->account_sid = $accountSid;
        $message->api_version = '2010-04-01';
        $message->body = $validated['Body'] ?? null;
        $message->from = $from ?? 'MOCK_TWILIO';
        $message->to = $to;
        $message->messaging_service_sid = $validated['MessagingServiceSid'] ?? null;
        $message->direction = 'outbound-api';
        $message->num_media = isset($validated['MediaUrl']) ? count($validated['MediaUrl']) : 0;
        $message->num_segments = $message->body ? TwilioResponseFormatter::calculateSegments($message->body) : 1;
        $message->price = null; // Test credentials có price = null
        $message->price_unit = null; // Test credentials có price_unit = null
        $message->status = $toErrorInfo ? $toErrorInfo['status'] : 'queued';
        $message->error_code = $toErrorInfo ? $toErrorInfo['error_code'] : null;
        $message->error_message = $toErrorInfo ? $toErrorInfo['error_message'] : null;
        $message->date_created = Carbon::now()->utc()->format('D, d M Y H:i:s \+\0\0\0\0');
        $message->date_updated = Carbon::now()->utc()->format('D, d M Y H:i:s \+\0\0\0\0');
        $message->sid = 'SM' . Str::random(32);
        $message->uri = '/2010-04-01/Accounts/' . $accountSid . '/Messages/' . $message->sid . '.json';

        $message->save();

        // Cập nhật status thành sent nếu không có error (giả lập)
        if (!$toErrorInfo) {
            $message->status = 'sent';
            $message->date_sent = Carbon::now()->utc()->format('D, d M Y H:i:s \+\0\0\0\0');
            $message->date_updated = $message->date_sent;
            $message->save();
        } else {
            // Một số magic numbers vẫn có date_sent
            $message->date_sent = Carbon::now()->utc()->format('D, d M Y H:i:s \+\0\0\0\0');
            $message->date_updated = $message->date_sent;
            $message->save();
        }

        // Error 1: Authenticate error (existing)
        if (rand(0, 100) < 10) {
            return response()->json([
                'code' => 20003,
                'message' => 'Authenticate',
                'more_info' => 'https://www.twilio.com/docs/errors/20003',
                'status' => 401
            ], 401);
        }
        // Error 2: Invalid "To" Phone Number (existing)
        if (rand(0, 100) < 20) {
            return response()->json([
                'code' => 21211,
                'message' => 'Invalid "To" Phone Number',
                'more_info' => 'https://www.twilio.com/docs/errors/21211',
                'status' => 400
            ], 400);
        }

        // Error 3: Unable to create record (existing)
        if (rand(0,100) < 30) {
            return response()->json([
                'code' => 21612,
                'message' => 'Unable to create record. We\'re sorry, an error has occurred. Please try again later.',
                'more_info' => 'https://www.twilio.com/docs/errors/21612',
                'status' => 400
            ], 400);
        }

        // Thêm error mới (ví dụ: 30005 - Message Delivery Failed)
        if (rand(0,100) < 15) {
            return response()->json([
                'code' => 30005,
                'message' => 'Message Delivery Failed',
                'more_info' => 'https://www.twilio.com/docs/errors/30005',
                'status' => 400
            ], 400);
        }

        return response()->json(TwilioResponseFormatter::formatMessage($message), 201);
    }

    /**
     * GET /2010-04-01/Accounts/{AccountSid}/Messages.json
     * Lấy danh sách messages
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

        $query = Message::where('account_sid', $accountSid);

        // Filter by To
        if ($request->has('To')) {
            $query->where('to', $request->get('To'));
        }

        // Filter by From
        if ($request->has('From')) {
            $query->where('from', $request->get('From'));
        }

        // Filter by DateSent
        if ($request->has('DateSent')) {
            $dateSent = Carbon::parse($request->get('DateSent'));
            $query->whereDate('date_sent', $dateSent->format('Y-m-d'));
        }

        // Pagination
        $pageSize = min((int) ($request->get('PageSize', 50)), 1000);
        $page = (int) ($request->get('Page', 0));

        $total = $query->count();
        $messages = $query->orderBy('date_created', 'desc')
            ->skip($page * $pageSize)
            ->take($pageSize)
            ->get();

        return response()->json(
            TwilioResponseFormatter::formatMessageList($messages, $accountSid, $page, $pageSize, $total)
        );
    }

    /**
     * GET /2010-04-01/Accounts/{AccountSid}/Messages/{MessageSid}.json
     * Lấy thông tin một message cụ thể
     */
    public function show(Request $request, string $accountSid, string $messageSid)
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

        $message = Message::where('account_sid', $accountSid)
            ->where('sid', $messageSid)
            ->first();

        if (!$message) {
            return response()->json([
                'code' => 20404,
                'message' => 'The requested resource /2010-04-01/Accounts/' . $accountSid . '/Messages/' . $messageSid . '.json was not found',
                'more_info' => 'https://www.twilio.com/docs/errors/20404',
                'status' => 404
            ], 404);
        }

        return response()->json(TwilioResponseFormatter::formatMessage($message));
    }

    /**
     * DELETE /2010-04-01/Accounts/{AccountSid}/Messages/{MessageSid}.json
     * Xóa một message
     */
    public function destroy(Request $request, string $accountSid, string $messageSid)
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

        $message = Message::where('account_sid', $accountSid)
            ->where('sid', $messageSid)
            ->first();

        if (!$message) {
            return response()->json([
                'code' => 20404,
                'message' => 'The requested resource /2010-04-01/Accounts/' . $accountSid . '/Messages/' . $messageSid . '.json was not found',
                'more_info' => 'https://www.twilio.com/docs/errors/20404',
                'status' => 404
            ], 404);
        }

        $message->delete();

        return response()->json(null, 204);
    }
}
