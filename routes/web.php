<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\ReceiptController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);
Route::get('/terms', [LegalController::class, 'terms']);
Route::get('/privacy', [LegalController::class, 'privacy']);
Route::get('/about', [LegalController::class, 'about']);
Route::get('/delete-account', fn () => view('delete-account'));

Route::get('/sitemap.xml', function () {
    $urls = [
        ['loc' => url('/'), 'changefreq' => 'weekly', 'priority' => '1.0'],
        ['loc' => url('/terms'), 'changefreq' => 'monthly', 'priority' => '0.5'],
        ['loc' => url('/privacy'), 'changefreq' => 'monthly', 'priority' => '0.5'],
        ['loc' => url('/about'), 'changefreq' => 'monthly', 'priority' => '0.5'],
        ['loc' => url('/delete-account'), 'changefreq' => 'monthly', 'priority' => '0.3'],
    ];

    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $url) {
        $xml .= '<url>';
        $xml .= '<loc>' . htmlspecialchars($url['loc'], ENT_XML1) . '</loc>';
        $xml .= '<lastmod>' . now()->toDateString() . '</lastmod>';
        $xml .= '<changefreq>' . $url['changefreq'] . '</changefreq>';
        $xml .= '<priority>' . $url['priority'] . '</priority>';
        $xml .= '</url>';
    }
    $xml .= '</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml']);
});

Route::middleware('auth')->prefix('receipts')->name('receipts.')->group(function () {
    Route::get('/{booking}', [ReceiptController::class, 'view'])->name('view');
    Route::get('/{booking}/pdf', [ReceiptController::class, 'downloadPdf'])->name('pdf');
    Route::post('/{booking}/email', [ReceiptController::class, 'emailReceipt'])->name('email');
});
