# ProServe — WhatsApp OTP Proxy API for Laravel (Hostinger)

> **Context**: The ProServe Flutter app sends OTPs via a server-side proxy at `POST /api/otp/whatsapp`. This keeps the Meta WhatsApp Cloud API credentials on the server — the mobile app never sees them. The Flutter `WhatsAppOtpService` sends `{phone, otp, lang}` and the server forwards the message to Meta's API.

---

## What Already Exists

- **Laravel + Filament admin panel** deployed on Hostinger
- **API key middleware** (`VerifyApiKey`) registered as `api.key` (see `laravel-file-api-setup.md`)
- **Flutter `WhatsAppOtpService`** in `lib/services/whatsapp_otp_service.dart` — calls `POST /api/otp/whatsapp`
- **Flutter `ServerConfig`** in `lib/services/server_config.dart` — defines base URL (`https://pumpnow.app`) and endpoint

## What Needs to Be Added to the Laravel App

One API endpoint behind the existing API key middleware:

| Method | Endpoint | Purpose |
|--------|----------|---------|
| `POST` | `/api/otp/whatsapp` | Receive `{phone, otp, lang}` from the app and forward an OTP message via Meta WhatsApp Cloud API |

---

## Step-by-Step Implementation

### 1. Add Environment Variables

Add to your `.env` file:

```env
# ── WhatsApp Cloud API (Meta) ──────────────────────────────
# Permanent System User token (see whatsapp-otp-setup.md §4)
WHATSAPP_ACCESS_TOKEN=EAABs...your_permanent_token...ZD

# Phone Number ID from WhatsApp > API Setup in Meta dashboard
WHATSAPP_PHONE_NUMBER_ID=123456789012345

# Template name (use "hello_world" for sandbox, "proserve_otp" for production)
WHATSAPP_TEMPLATE_NAME=hello_world

# Set to true once you have an approved AUTH template with a {{1}} variable
WHATSAPP_USE_OTP_TEMPLATE=false
```

### 2. Add Config Entry

**File: `config/services.php`** — add inside the return array (alongside the existing `proserve` key):

```php
'whatsapp' => [
    'access_token'    => env('WHATSAPP_ACCESS_TOKEN'),
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    'template_name'   => env('WHATSAPP_TEMPLATE_NAME', 'hello_world'),
    'use_otp_template' => env('WHATSAPP_USE_OTP_TEMPLATE', false),
],
```

### 3. Create the Controller

```bash
php artisan make:controller Api/WhatsAppOtpController
```

**File: `app/Http/Controllers/Api/WhatsAppOtpController.php`**

```php
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
     *
     * Forwards the OTP to Meta WhatsApp Cloud API using server-side credentials.
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

        // Strip leading "+" for Meta API (it expects digits only)
        $recipient = ltrim($phone, '+');

        // Build the template payload
        if ($useOtp) {
            // Production: approved AUTH template with {{1}} parameter
            $templatePayload = [
                'name'     => $templateName,
                'language' => ['code' => $lang === 'ar' ? 'ar' : 'en'],
                'components' => [
                    [
                        'type'       => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => $otp],
                        ],
                    ],
                ],
            ];
        } else {
            // Sandbox: hello_world (no parameters)
            $templatePayload = [
                'name'     => $templateName,
                'language' => ['code' => 'en_US'],
            ];
        }

        $body = [
            'messaging_product' => 'whatsapp',
            'to'                => $recipient,
            'type'              => 'template',
            'template'          => $templatePayload,
        ];

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
```

### 4. Add the Route

**File: `routes/api.php`** — add alongside the existing `files` routes:

```php
use App\Http\Controllers\Api\WhatsAppOtpController;

// Existing file routes
Route::middleware('api.key')->prefix('files')->group(function () {
    Route::post('/upload', [FileController::class, 'upload']);
    Route::get('/{path}', [FileController::class, 'show'])->where('path', '.*');
    Route::post('/delete', [FileController::class, 'destroy']);
});

// WhatsApp OTP proxy
Route::middleware('api.key')->prefix('otp')->group(function () {
    Route::post('/whatsapp', [WhatsAppOtpController::class, 'send']);
});
```

### 5. Clear Route Cache (Hostinger)

After adding the route, SSH into the server and run:

```bash
cd ~/domains/pumpnow.app/public_html
php artisan route:clear
php artisan route:cache
```

Or if you don't have SSH, hit any URL to force Laravel to reload routes (route caching is only an issue if you previously ran `route:cache`).

---

## API Contract (What the Flutter App Sends)

### Send OTP — `POST /api/otp/whatsapp`

**Request:**
```
Headers:
  X-API-Key: {api_key}
  Content-Type: application/json

Body (JSON):
{
  "phone": "+201017910660",
  "otp": "482917",
  "lang": "ar"
}
```

**Response (200 — Success):**
```json
{
  "success": true,
  "message": "OTP sent via WhatsApp."
}
```

**Response (502 — Meta API Error):**
```json
{
  "success": false,
  "message": "WhatsApp API error.",
  "error": "Recipient not in allowed list"
}
```

**Response (401 — Missing/Invalid API Key):**
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

**Response (422 — Validation Error):**
```json
{
  "message": "The phone field format is invalid.",
  "errors": {
    "phone": ["The phone field format is invalid."]
  }
}
```

---

## Verification

### 1. Check the Route Exists

```bash
php artisan route:list --path=api/otp
```

Expected output:
```
POST  api/otp/whatsapp  Api\WhatsAppOtpController@send  api.key
```

### 2. Quick cURL Test

```bash
curl -X POST "https://pumpnow.app/api/otp/whatsapp" \
  -H "X-API-Key: jYDLfPqjxWcaKKKzb8Xz3dM0jtP1SeSgeVJ8QYoPWawm31HcuDptQ1FIVmhFXFYi" \
  -H "Content-Type: application/json" \
  -d '{"phone": "+201017910660", "otp": "123456", "lang": "en"}'
```

**If you get "The route api/otp/whatsapp could not be found"**, check:

1. The route is registered in `routes/api.php` (Step 4 above)
2. Route cache is cleared: `php artisan route:clear`
3. The controller file exists at `app/Http/Controllers/Api/WhatsAppOtpController.php`
4. There are no syntax errors: `php artisan route:list`

### 3. Test from the Flutter App

1. Set `AuthService.devMode = true`
2. Enter an Egyptian number (e.g. `01017910660`)
3. On the OTP screen, the app calls `POST /api/otp/whatsapp`
4. Check Laravel logs at `storage/logs/laravel.log` for success/error messages

---

## Switching to Production

When you have an approved `proserve_otp` AUTH template in Meta dashboard:

1. Update `.env`:
   ```env
   WHATSAPP_TEMPLATE_NAME=proserve_otp
   WHATSAPP_USE_OTP_TEMPLATE=true
   ```

2. Clear config cache:
   ```bash
   php artisan config:clear
   ```

3. The server will now send the actual OTP code inside the template's `{{1}}` parameter, so the user receives:
   > Your ProServe verification code is: **482917**
   > Valid for 5 minutes. Do not share this code.

---

## Troubleshooting

| Symptom | Cause | Fix |
|---------|-------|-----|
| `404 — Route not found` | Route not registered or cache stale | Run `php artisan route:clear` then verify with `php artisan route:list --path=api/otp` |
| `401 — Unauthorized` | API key mismatch | Ensure `X-API-Key` header matches `PROSERVE_API_KEY` in `.env` |
| `500 — WhatsApp service not configured` | Missing env vars | Add `WHATSAPP_ACCESS_TOKEN` and `WHATSAPP_PHONE_NUMBER_ID` to `.env`, then `php artisan config:clear` |
| `502 — Recipient not in allowed list` | Sandbox mode — number not registered as test recipient | Add the number in Meta dashboard (see `whatsapp-otp-setup.md` §5) |
| `502 — Invalid OAuth token` | Access token expired | Generate a new token in Meta dashboard (temporary tokens last 24h; use a System User token for production) |
| `422 — phone format invalid` | Phone not in E.164 format | Must start with `+` followed by country code and number (e.g. `+201017910660`) |
| Works via cURL but not from app | Base URL or API key mismatch in Flutter | Check `ServerConfig.baseUrl` and `ServerConfig.apiKey` in `lib/services/server_config.dart` |

---

## Files Reference

| File | Location | Purpose |
|------|----------|---------|
| `WhatsAppOtpController.php` | `app/Http/Controllers/Api/` | Server-side proxy controller |
| `routes/api.php` | Laravel root | Route registration |
| `config/services.php` | Laravel root | WhatsApp config pulled from `.env` |
| `.env` | Laravel root | Meta API credentials (never commit) |
| `whatsapp_otp_service.dart` | `lib/services/` | Flutter client that calls this endpoint |
| `server_config.dart` | `lib/services/` | Flutter base URL and API key |

---

**Last Updated**: March 20, 2026
