<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MediaController extends Controller
{
    /**
     * Upload user avatar.
     */
    public function uploadAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        
        // Delete old avatar if exists
        $user->clearMediaCollection('avatar');
        
        // Add new avatar
        $media = $user->addMediaFromRequest('avatar')
            ->toMediaCollection('avatar');

        return response()->json([
            'success' => true,
            'data' => [
                'url' => $media->getUrl(),
                'thumb' => $media->getUrl('thumb'),
            ],
            'message' => 'Avatar uploaded successfully',
        ]);
    }

    /**
     * Delete user avatar.
     */
    public function deleteAvatar(Request $request)
    {
        $user = $request->user();
        $user->clearMediaCollection('avatar');

        return response()->json([
            'success' => true,
            'message' => 'Avatar deleted successfully',
        ]);
    }

    /**
     * Get user media.
     */
    public function getUserMedia(Request $request)
    {
        $user = $request->user();
        $avatar = $user->getFirstMedia('avatar');

        return response()->json([
            'success' => true,
            'data' => [
                'avatar' => $avatar ? [
                    'url' => $avatar->getUrl(),
                    'thumb' => $avatar->getUrl('thumb'),
                ] : null,
            ],
        ]);
    }
}
