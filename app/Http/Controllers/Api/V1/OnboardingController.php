<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OnboardingScreen;
use Illuminate\Http\JsonResponse;

class OnboardingController extends Controller
{
    /**
     * Get all onboarding screens
     */
    public function screens(): JsonResponse
    {
        $screens = OnboardingScreen::orderBy('sort_order')->get();
        
        return response()->json([
            'success' => true,
            'data' => $screens,
            'message' => 'Onboarding screens retrieved successfully'
        ]);
    }
}
