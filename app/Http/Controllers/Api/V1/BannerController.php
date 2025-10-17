<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    /**
     * Get all active banners
     */
    public function index(): JsonResponse
    {
        $banners = Banner::active()
            ->orderBy('sort_order')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $banners,
            'message' => 'Banners retrieved successfully'
        ]);
    }

    /**
     * Get a specific banner
     */
    public function show(int $id): JsonResponse
    {
        $banner = Banner::find($id);
        
        if (!$banner) {
            return response()->json([
                'success' => false,
                'error' => 'Banner not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $banner,
            'message' => 'Banner retrieved successfully'
        ]);
    }
}
