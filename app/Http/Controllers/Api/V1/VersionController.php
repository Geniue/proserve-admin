<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VersionController extends Controller
{
    /**
     * Check app version and update requirements
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'version' => 'required|string',
            'build_number' => 'required|integer',
            'platform' => 'required|in:android,ios',
        ]);
        
        $currentVersion = $request->input('version');
        $currentBuild = $request->input('build_number');
        $platform = $request->input('platform');
        
        // Get the latest version for the platform
        $latestVersion = AppVersion::where('platform', $platform)
            ->orderBy('build_number', 'desc')
            ->first();
        
        if (!$latestVersion) {
            return response()->json([
                'success' => true,
                'data' => [
                    'update_required' => false,
                    'force_update' => false,
                ],
                'message' => 'No version information available'
            ]);
        }
        
        $updateRequired = $currentBuild < $latestVersion->build_number;
        $forceUpdate = $updateRequired && $latestVersion->force_update;
        
        return response()->json([
            'success' => true,
            'data' => [
                'current_version' => $currentVersion,
                'latest_version' => $latestVersion->version,
                'update_required' => $updateRequired,
                'force_update' => $forceUpdate,
                'update_message' => $latestVersion->update_message,
                'download_url' => $latestVersion->download_url,
            ],
            'message' => 'Version check completed'
        ]);
    }
}
