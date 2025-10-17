<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Http\Resources\ServiceResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    /**
     * Get all active services with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $categoryId = $request->input('category_id');
        $featured = $request->input('featured');
        $search = $request->input('search');
        
        $query = Service::with('category')
            ->where('is_active', true);
        
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        if ($featured) {
            $query->where('is_featured', true);
        }
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }
        
        $services = $query->orderBy('sort_order')
            ->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => ServiceResource::collection($services->items()),
            'meta' => [
                'current_page' => $services->currentPage(),
                'total' => $services->total(),
                'per_page' => $services->perPage(),
                'last_page' => $services->lastPage(),
            ],
            'message' => 'Services retrieved successfully'
        ]);
    }

    /**
     * Get a specific service with details
     */
    public function show(int $id): JsonResponse
    {
        $service = Service::with(['category', 'providers'])
            ->find($id);
        
        if (!$service || !$service->is_active) {
            return response()->json([
                'success' => false,
                'error' => 'Service not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => new ServiceResource($service),
            'message' => 'Service retrieved successfully'
        ]);
    }

    /**
     * Get featured services
     */
    public function featured(): JsonResponse
    {
        $services = Service::with('category')
            ->where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->limit(10)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => ServiceResource::collection($services),
            'message' => 'Featured services retrieved successfully'
        ]);
    }
}
