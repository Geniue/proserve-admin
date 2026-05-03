<?php

namespace Tests\Unit;

use App\Support\TransparentImageTrimmer;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TransparentImageTrimmerTest extends TestCase
{
    public function test_it_trims_transparent_padding_from_public_logo_images(): void
    {
        Storage::fake('public');

        $path = Storage::disk('public')->path('logo/padded.png');
        @mkdir(dirname($path), 0777, true);

        $image = imagecreatetruecolor(400, 400);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        $blue = imagecolorallocatealpha($image, 37, 99, 235, 0);

        imagefill($image, 0, 0, $transparent);
        imagefilledrectangle($image, 12, 154, 390, 245, $blue);
        imagepng($image, $path);
        imagedestroy($image);

        $this->assertTrue(TransparentImageTrimmer::trimPublicDiskImage('/storage/logo/padded.png'));

        [$width, $height] = getimagesize($path);

        $this->assertSame(395, $width);
        $this->assertSame(108, $height);
    }
}
