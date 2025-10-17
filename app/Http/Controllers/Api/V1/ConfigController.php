<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use App\Models\ThemeSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ConfigController extends Controller
{
    /**
     * Get all app configuration
     */
    public function index(): JsonResponse
    {
        $configs = AppConfig::all()->pluck('value', 'key');
        
        return response()->json([
            'success' => true,
            'data' => $configs,
            'message' => 'Configuration retrieved successfully'
        ]);
    }

    /**
     * Get theme settings
     */
    public function theme(): JsonResponse
    {
        $theme = ThemeSetting::all()->pluck('value', 'key');
        
        return response()->json([
            'success' => true,
            'data' => $theme,
            'message' => 'Theme settings retrieved successfully'
        ]);
    }

    /**
     * Get specific config by key
     */
    public function show(string $key): JsonResponse
    {
        $config = AppConfig::where('key', $key)->first();
        
        if (!$config) {
            return response()->json([
                'success' => false,
                'error' => 'Configuration key not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $config,
            'message' => 'Configuration retrieved successfully'
        ]);
    }
}
