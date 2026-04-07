<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppOtpController extends Controller
{
    /**
     * POST /api/otp/whatsapp
     *
     * Expects JSON: {"phone": "+966501234567", "otp": "123456", "lang": "ar"}
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|regex:/^\+[1-9]\d{6,14}$/',
            'otp'   => 'required|string|digits:6',
            'lang'  => 'nullable|string|in:en,ar',
        ]);

        $phone = $request->input('phone');
        $otp   = $request->input('otp');
        $lang  = $request->input('lang', 'en');

        $accessToken   = config('services.whatsapp.access_token');
        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $templateName  = config('services.whatsapp.template_name');
        $useOtp        = config('services.whatsapp.use_otp_template');

        if (empty($accessToken) || empty($phoneNumberId)) {
            Log::error('WhatsApp OTP: Missing credentials in config.');
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp service not configured.',
            ], 500);
        }

        // Strip leading "+" for Meta API (expects digits only)
        $recipient = ltrim($phone, '+');

        // Build the message payload
        if ($useOtp) {
            // Approved Authentication template with OTP button
            $body = [
                'messaging_product' => 'whatsapp',
                'to'                => $recipient,
                'type'              => 'template',
                'template'          => [
                    'name'     => $templateName,
                    'language' => ['code' => $lang === 'ar' ? 'ar' : 'en'],
                    'components' => [
                        [
                            'type'       => 'body',
                            'parameters' => [
                                ['type' => 'text', 'text' => $otp],
                            ],
                        ],
                        [
                            'type'       => 'button',
                            'sub_type'   => 'url',
                            'index'      => 0,
                            'parameters' => [
                                ['type' => 'text', 'text' => $otp],
                            ],
                        ],
                    ],
                ],
            ];
        } else {
            // Plain text mode: sends OTP as a regular message (no template needed)
            $message = $lang === 'ar'
                ? "رمز التحقق الخاص بك في PUMP هو: *{$otp}*\nصالح لمدة 5 دقائق. لا تشارك هذا الرمز مع أي شخص."
                : "Your PUMP verification code is: *{$otp}*\nValid for 5 minutes. Do not share this code.";

            $body = [
                'messaging_product' => 'whatsapp',
                'to'                => $recipient,
                'type'              => 'text',
                'text'              => ['body' => $message],
            ];
        }

        try {
            $response = Http::withToken($accessToken)
                ->timeout(15)
                ->post(
                    "https://graph.facebook.com/v21.0/{$phoneNumberId}/messages",
                    $body
                );

            if ($response->successful()) {
                Log::info("WhatsApp OTP sent to {$phone}");
                return response()->json([
                    'success' => true,
                    'message' => 'OTP sent via WhatsApp.',
                ]);
            }

            Log::warning("WhatsApp API error for {$phone}: " . $response->body());
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp API error.',
                'error'   => $response->json('error.message', 'Unknown error'),
            ], 502);
        } catch (\Exception $e) {
            Log::error("WhatsApp OTP exception: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to contact WhatsApp API.',
            ], 502);
        }
    }
}
