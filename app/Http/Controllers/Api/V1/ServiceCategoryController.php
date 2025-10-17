<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\JsonResponse;

class ServiceCategoryController extends Controller
{
    /**
     * Get all active service categories
     */
    public function index(): JsonResponse
    {
        $categories = ServiceCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->withCount('services')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $categories,
            'message' => 'Service categories retrieved successfully'
        ]);
    }

    /**
     * Get a specific category with its services
     */
    public function show(int $id): JsonResponse
    {
        $category = ServiceCategory::with(['services' => function ($query) {
            $query->where('is_active', true)->orderBy('sort_order');
        }])->find($id);
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'error' => 'Category not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $category,
            'message' => 'Category retrieved successfully'
        ]);
    }
}
