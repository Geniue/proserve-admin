<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    /**
     * Get a specific page by slug
     */
    public function show(string $slug): JsonResponse
    {
        $page = Page::where('slug', $slug)->first();
        
        if (!$page) {
            return response()->json([
                'success' => false,
                'error' => 'Page not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $page,
            'message' => 'Page retrieved successfully'
        ]);
    }

    /**
     * Get all FAQs
     */
    public function faqs(): JsonResponse
    {
        $faqs = Faq::where('is_active', true)
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');
        
        return response()->json([
            'success' => true,
            'data' => $faqs,
            'message' => 'FAQs retrieved successfully'
        ]);
    }
}
