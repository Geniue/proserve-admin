<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\NavigationItem;
use Illuminate\Http\JsonResponse;

class NavigationController extends Controller
{
    /**
     * Get navigation menu tree
     */
    public function tree(): JsonResponse
    {
        $navigation = NavigationItem::visible()
            ->rootItems()
            ->with(['children' => function ($query) {
                $query->visible()->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $navigation,
            'message' => 'Navigation tree retrieved successfully'
        ]);
    }
}
