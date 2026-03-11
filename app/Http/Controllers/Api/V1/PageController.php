<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Get a specific page by slug
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        $page = Page::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$page) {
            return response()->json([
                'success' => false,
                'error' => 'Page not found'
            ], 404);
        }

        $locale = in_array($request->query('locale'), ['en', 'ar']) ? $request->query('locale') : 'en';
        $alternateLocale = $locale === 'en' ? 'ar' : 'en';

        return response()->json([
            'success' => true,
            'data' => [
                'slug' => $page->slug,
                'locale' => $locale,
                'dir' => Page::getDirectionForLocale($locale),
                'alternate_locale' => $alternateLocale,
                'seo' => $page->getTranslation('seo_translations', $locale, []),
                'title' => $page->getTranslation('title_translations', $locale, $page->title),
                'sections' => $page->getLocalizedSections($locale),
            ],
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
