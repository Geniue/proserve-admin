<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class TransparentImageTrimmer
{
    public static function publicDiskPathFromUrl(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: $url;
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return str_starts_with($path, 'logo/') ? $path : null;
    }

    public static function trimPublicDiskImage(string $path, int $padding = 8): bool
    {
        $path = self::publicDiskPathFromUrl($path) ?? ltrim($path, '/');

        if (! str_starts_with($path, 'logo/') || ! Storage::disk('public')->exists($path)) {
            return false;
        }

        return self::trimFile(Storage::disk('public')->path($path), $padding);
    }

    public static function trimFile(string $path, int $padding = 8): bool
    {
        if (! is_file($path) || ! function_exists('imagecreatetruecolor')) {
            return false;
        }

        $info = @getimagesize($path);
        $mime = $info['mime'] ?? null;

        $image = match ($mime) {
            'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($path) : false,
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if (! $image) {
            return false;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $minX = $width;
        $minY = $height;
        $maxX = -1;
        $maxY = -1;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $alpha = (imagecolorat($image, $x, $y) & 0x7F000000) >> 24;

                if ($alpha >= 120) {
                    continue;
                }

                $minX = min($minX, $x);
                $minY = min($minY, $y);
                $maxX = max($maxX, $x);
                $maxY = max($maxY, $y);
            }
        }

        if ($maxX < $minX || $maxY < $minY) {
            imagedestroy($image);

            return false;
        }

        $minX = max(0, $minX - $padding);
        $minY = max(0, $minY - $padding);
        $maxX = min($width - 1, $maxX + $padding);
        $maxY = min($height - 1, $maxY + $padding);

        if ($minX === 0 && $minY === 0 && $maxX === ($width - 1) && $maxY === ($height - 1)) {
            imagedestroy($image);

            return false;
        }

        $trimmedWidth = $maxX - $minX + 1;
        $trimmedHeight = $maxY - $minY + 1;
        $trimmed = imagecreatetruecolor($trimmedWidth, $trimmedHeight);

        imagealphablending($trimmed, false);
        imagesavealpha($trimmed, true);

        $transparent = imagecolorallocatealpha($trimmed, 0, 0, 0, 127);
        imagefill($trimmed, 0, 0, $transparent);
        imagecopy($trimmed, $image, 0, 0, $minX, $minY, $trimmedWidth, $trimmedHeight);

        $saved = match ($mime) {
            'image/png' => function_exists('imagepng') && imagepng($trimmed, $path),
            'image/webp' => function_exists('imagewebp') && imagewebp($trimmed, $path, 90),
            default => false,
        };

        imagedestroy($image);
        imagedestroy($trimmed);

        return $saved;
    }
}
