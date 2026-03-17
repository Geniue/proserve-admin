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
                dirname("uploads/{$relativePath}"),
                basename($relativePath),
                'public'
            );
        }

        $publicUrl = asset("storage/uploads/{$relativePath}");
        $fileSize = Storage::disk('public')->size("uploads/{$relativePath}");

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

        $fullPath = "uploads/{$path}";

        if (!Storage::disk('public')->exists($fullPath)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        $mimeType = Storage::disk('public')->mimeType($fullPath);

        return response()->streamDownload(function () use ($fullPath) {
            echo Storage::disk('public')->get($fullPath);
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

        $fullPath = "uploads/{$relativePath}";

        if (!Storage::disk('public')->exists($fullPath)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        Storage::disk('public')->delete($fullPath);

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

            $storagePath = "uploads/{$relativePath}";

            // Ensure directory exists
            $dir = dirname($storagePath);
            if (!Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->makeDirectory($dir);
            }

            Storage::disk('public')->put($storagePath, (string) $encoded);

            return $storagePath;
        } catch (\Exception $e) {
            \Log::error('Image compression failed: ' . $e->getMessage());
            return null;
        }
    }
}
