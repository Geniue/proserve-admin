<?php

namespace App\Http\Controllers;

use App\Models\Page;

class HomeController extends Controller
{
    public function index()
    {
        $homepage = Page::firstOrCreateHomepage();

        $data = [
            'homepage' => $homepage,
            'blocks' => $homepage->content_blocks ?? Page::defaultContentBlocks(),
            'seo' => $homepage->seo_translations ?? ['en' => [], 'ar' => []],
        ];

        return view('welcome', $data);
    }
}
