<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;

class LegalController extends Controller
{
    public function terms()
    {
        $markdown = file_get_contents(base_path('.github/terms-and-conditions-ar.md'));

        return view('legal', [
            'title' => 'الشروط والأحكام — PUMP',
            'content' => Str::markdown($markdown),
        ]);
    }

    public function privacy()
    {
        $markdown = file_get_contents(base_path('.github/privacy-policy-ar.md'));

        return view('legal', [
            'title' => 'سياسة الخصوصية — PUMP',
            'content' => Str::markdown($markdown),
        ]);
    }

    public function about()
    {
        return view('about');
    }
}
