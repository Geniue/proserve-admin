# ProServe — File Upload/Download API for Laravel (Hostinger)

> **Context**: The ProServe Flutter app needs file upload/download capabilities to a Hostinger server running Laravel + Filament. Files include profile images (JPG, PNG, HEIC/HEIF, WebP), PDFs, and text files. Images must be compressed server-side before saving. The Flutter app already compresses client-side, but the server acts as a second pass.

---

## What Already Exists

- **Laravel + Filament admin panel** deployed on Hostinger
- **Flutter app** with `FileUploadService` that sends multipart POST requests
- **Client-side image compression** via `flutter_image_compress` (HEIC→JPEG, resize to 1200px, quality 70)

## What Needs to Be Added to the Laravel App

Three API endpoints behind API key authentication:

| Method | Endpoint | Purpose |
|--------|----------|---------|
| `POST` | `/api/files/upload` | Upload a file (multipart form) |
| `GET` | `/api/files/{path}` | Download/serve a file |
| `POST` | `/api/files/delete` | Delete a file |

---

## Step-by-Step Implementation

### 1. Add Environment Variables

Add to your `.env` file:

```env
# ProServe Mobile API Key (generate a strong random string)
PROSERVE_API_KEY=your-64-char-random-string-here

# Max upload size in KB (10 MB)
PROSERVE_MAX_UPLOAD_KB=10240

# Image compression settings
PROSERVE_IMAGE_MAX_WIDTH=1200
PROSERVE_IMAGE_MAX_HEIGHT=1200
PROSERVE_IMAGE_QUALITY=80
```

### 2. Create the API Key Middleware

```bash
php artisan make:middleware VerifyApiKey
```

**File: `app/Http/Middleware/VerifyApiKey.php`**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key') ?? $request->query('key');
        $expectedKey = config('services.proserve.api_key');

        if (empty($expectedKey) || !hash_equals($expectedKey, $apiKey ?? '')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        return $next($request);
    }
}
```

### 3. Register the Middleware

**File: `bootstrap/app.php`** (Laravel 11+)

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'api.key' => \App\Http\Middleware\VerifyApiKey::class,
    ]);
})
```

Or if using the older `app/Http/Kernel.php` approach:

```php
protected $middlewareAliases = [
    // ... existing aliases
    'api.key' => \App\Http\Middleware\VerifyApiKey::class,
];
```

### 4. Add Config Entry

**File: `config/services.php`** — add inside the return array:

```php
'proserve' => [
    'api_key' => env('PROSERVE_API_KEY'),
    'max_upload_kb' => env('PROSERVE_MAX_UPLOAD_KB', 10240),
    'image_max_width' => env('PROSERVE_IMAGE_MAX_WIDTH', 1200),
    'image_max_height' => env('PROSERVE_IMAGE_MAX_HEIGHT', 1200),
    'image_quality' => env('PROSERVE_IMAGE_QUALITY', 80),
],
```

### 5. Create the Controller

```bash
php artisan make:controller Api/FileController
```

**File: `app/Http/Controllers/Api/FileController.php`**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    /**
     * Allowed MIME types and their extensions.
     */
    private const ALLOWED_TYPES = [
        // Images
        'image/jpeg'    => 'jpg',
        'image/png'     => 'png',
        'image/webp'    => 'webp',
        'image/heic'    => 'heic',
        'image/heif'    => 'heif',
        // Documents
        'application/pdf' => 'pdf',
        'text/plain'      => 'txt',
    ];

    /**
     * POST /api/files/upload
     *
     * Expects multipart/form-data:
     *   - file: the uploaded file
     *   - folder: string (e.g. "profiles", "documents")
     *   - user_id: string (Firebase UID)
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file'    => 'required|file|max:' . config('services.proserve.max_upload_kb', 10240),
            'folder'  => 'required|string|alpha_dash|max:50',
            'user_id' => 'required|string|alpha_dash|max:128',
        ]);

        $file = $request->file('file');
        $folder = $request->input('folder');
        $userId = $request->input('user_id');

        // Validate MIME type using server-side detection
        $detectedMime = $file->getMimeType();
        if (!isset(self::ALLOWED_TYPES[$detectedMime])) {
            return response()->json([
                'success' => false,
                'message' => "File type not allowed: {$detectedMime}",
            ], 415);
        }

        $isImage = str_starts_with($detectedMime, 'image/');
        $extension = self::ALLOWED_TYPES[$detectedMime];

        // Generate unique filename
        $filename = time() . '_' . Str::random(16) . '.' . $extension;
        $relativePath = "{$folder}/{$userId}/{$filename}";

        if ($isImage) {
            // Compress and resize image
            $storedPath = $this->compressAndStoreImage($file, $relativePath);
            if ($storedPath === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process image',
                ], 500);
            }
        } else {
            // Store non-image files as-is
            $file->storeAs(
                dirname($relativePath),
                basename($relativePath),
                'public'
            );
        }

        $publicUrl = asset("storage/{$relativePath}");
        $fileSize = Storage::disk('public')->size($relativePath);

        return response()->json([
            'success'   => true,
            'message'   => 'File uploaded successfully',
            'url'       => $publicUrl,
            'path'      => $relativePath,
            'filename'  => $filename,
            'size'      => $fileSize,
            'mime_type' => $detectedMime,
            'is_image'  => $isImage,
        ]);
    }

    /**
     * GET /api/files/{path}
     *
     * Serves a file from storage. The {path} is the relative path
     * returned by upload (e.g. "profiles/uid123/1234_abc.jpg").
     */
    public function show(string $path): StreamedResponse|JsonResponse
    {
        // Sanitize: prevent traversal
        if (str_contains($path, '..')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid path',
            ], 400);
        }

        if (!Storage::disk('public')->exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        $mimeType = Storage::disk('public')->mimeType($path);

        return response()->streamDownload(function () use ($path) {
            echo Storage::disk('public')->get($path);
        }, basename($path), [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    /**
     * POST /api/files/delete
     *
     * Expects JSON: {"path": "profiles/uid123/1234_abc.jpg"}
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string|max:500',
        ]);

        $relativePath = $request->input('path');

        // Sanitize
        if (str_contains($relativePath, '..') || str_starts_with($relativePath, '/')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid path',
            ], 400);
        }

        if (!Storage::disk('public')->exists($relativePath)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        Storage::disk('public')->delete($relativePath);

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully',
        ]);
    }

    /**
     * Compress, resize, and store an image.
     */
    private function compressAndStoreImage($uploadedFile, string $relativePath): ?string
    {
        try {
            $maxW = config('services.proserve.image_max_width', 1200);
            $maxH = config('services.proserve.image_max_height', 1200);
            $quality = config('services.proserve.image_quality', 80);

            $image = Image::read($uploadedFile->getRealPath());

            // Resize if exceeds max dimensions (maintain aspect ratio)
            $image->scaleDown(width: $maxW, height: $maxH);

            // Encode to JPEG for compression (converts HEIC/PNG/WebP → JPEG)
            $encoded = $image->toJpeg($quality);

            // Ensure directory exists
            $dir = dirname($relativePath);
            if (!Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->makeDirectory($dir);
            }

            Storage::disk('public')->put($relativePath, (string) $encoded);

            return $relativePath;
        } catch (\Exception $e) {
            \Log::error('Image compression failed: ' . $e->getMessage());
            return null;
        }
    }
}
```

### 6. Add Routes

**File: `routes/api.php`**

```php
use App\Http\Controllers\Api\FileController;

Route::middleware('api.key')->prefix('files')->group(function () {
    Route::post('/upload', [FileController::class, 'upload']);
    Route::get('/{path}', [FileController::class, 'show'])->where('path', '.*');
    Route::post('/delete', [FileController::class, 'destroy']);
});
```

### 7. Install Intervention Image (Image Processing Library)

```bash
composer require intervention/image-laravel
```

This installs `intervention/image` v3 with the Laravel service provider. It uses GD by default (available on Hostinger).

**Verify** it auto-registered in `config/app.php` — if not, publish the config:

```bash
php artisan vendor:publish --provider="Intervention\Image\Laravel\ServiceProvider"
```

### 8. Create Storage Symlink

Laravel serves public files via a symlink from `public/storage` → `storage/app/public`.

```bash
php artisan storage:link
```

> **Hostinger note**: If `storage:link` fails via SSH, you can create it manually:
> ```bash
> cd public_html/public
> ln -s ../../storage/app/public storage
> ```
> Or via Hostinger File Manager, create a symlink.

### 9. Configure PHP Upload Limits (Hostinger)

Create or update `public/.user.ini` (or `.htaccess`):

```ini
upload_max_filesize = 12M
post_max_size = 14M
max_execution_time = 120
memory_limit = 256M
```

For `.htaccess` approach:
```apache
php_value upload_max_filesize 12M
php_value post_max_size 14M
php_value max_execution_time 120
```

### 10. Verify GD Extension

GD is required for image compression. Verify it's enabled:

```bash
php -m | grep gd
```

Or check in Hostinger Dashboard → **Advanced** → **PHP Configuration** → ensure `gd` is enabled.

---

## API Contract (What the Flutter App Expects)

### Upload — `POST /api/files/upload`

**Request:**
```
Headers:
  X-API-Key: {api_key}
  Content-Type: multipart/form-data

Body (form-data):
  file: (binary)
  folder: "profiles"          ← string, alphanumeric + dash/underscore
  user_id: "abc123XYZ"        ← string, Firebase UID
```

**Response (200):**
```json
{
  "success": true,
  "message": "File uploaded successfully",
  "url": "https://pumpnow.app/storage/profiles/abc123XYZ/1711234567_aB3x9kPmQw2nZy.jpg",
  "path": "profiles/abc123XYZ/1711234567_aB3x9kPmQw2nZy.jpg",
  "filename": "1711234567_aB3x9kPmQw2nZy.jpg",
  "size": 45230,
  "mime_type": "image/jpeg",
  "is_image": true
}
```

**Error Responses:**
- `401` — Missing / wrong API key
- `413` — File too large
- `415` — File type not allowed
- `422` — Validation error (missing fields)
- `500` — Server error

### Download — `GET /api/files/{path}`

**Request:**
```
Headers:
  X-API-Key: {api_key}

Example: GET /api/files/profiles/abc123XYZ/1711234567_aB3x9kPmQw2nZy.jpg
```

**Response:** Binary file data with correct `Content-Type` header.

> **Note:** Profile images also accessible directly via the `url` returned by upload (public storage URL), no API key needed. The `/api/files/{path}` endpoint is for protected file access.

### Delete — `POST /api/files/delete`

**Request:**
```
Headers:
  X-API-Key: {api_key}
  Content-Type: application/json

Body:
{
  "path": "profiles/abc123XYZ/1711234567_aB3x9kPmQw2nZy.jpg"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "File deleted successfully"
}
```

---

## Folder Structure After Setup

```
your-laravel-app/
├── app/
│   └── Http/
│       ├── Controllers/
│       │   └── Api/
│       │       └── FileController.php      ← NEW
│       └── Middleware/
│           └── VerifyApiKey.php             ← NEW
├── config/
│   └── services.php                        ← MODIFIED (add proserve key)
├── routes/
│   └── api.php                             ← MODIFIED (add file routes)
├── storage/
│   └── app/
│       └── public/
│           ├── profiles/               ← Profile images
│           ├── documents/              ← PDFs, text files
│           └── ...
├── public/
│   ├── storage -> ../../storage/app/public ← SYMLINK
│   └── .user.ini                           ← PHP upload limits
└── .env                                     ← MODIFIED (add API key)
```

---

## Security Checklist

- [x] API key authentication via `X-API-Key` header (timing-safe `hash_equals`)
- [x] Server-side MIME type validation (not trusting client `Content-Type`)
- [x] Path traversal prevention (`..` blocked, no absolute paths)
- [x] `alpha_dash` validation on `folder` and `user_id` (no special chars)
- [x] File size limits enforced server-side
- [x] Unique filenames with timestamp + random string (no user-controlled names)
- [x] Images re-encoded server-side (strips metadata/EXIF, prevents polyglot files)
- [x] Laravel's storage system handles file permissions

---

## Testing

After deployment, test with cURL:

```bash
# Upload
curl -X POST https://pumpnow.app/api/files/upload \
  -H "X-API-Key: your-api-key" \
  -F "file=@test-image.jpg" \
  -F "folder=profiles" \
  -F "user_id=testuser123"

# Download
curl -H "X-API-Key: your-api-key" \
  https://pumpnow.app/api/files/profiles/testuser123/filename.jpg \
  --output downloaded.jpg

# Delete
curl -X POST https://pumpnow.app/api/files/delete \
  -H "X-API-Key: your-api-key" \
  -H "Content-Type: application/json" \
  -d '{"path": "profiles/testuser123/filename.jpg"}'
```

---

## Summary of Commands

```bash
# 1. Install image processing library
composer require intervention/image-laravel

# 2. Publish config (if needed)
php artisan vendor:publish --provider="Intervention\Image\Laravel\ServiceProvider"

# 3. Create storage symlink
php artisan storage:link

# 4. Create middleware
php artisan make:middleware VerifyApiKey

# 5. Create controller
php artisan make:controller Api/FileController

# 6. Clear caches after changes
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```
