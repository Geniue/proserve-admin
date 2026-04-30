<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_returns_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_homepage_auto_creates_page_record(): void
    {
        $this->assertDatabaseMissing('pages', ['slug' => 'home']);

        $this->get('/');

        $this->assertDatabaseHas('pages', ['slug' => 'home']);
    }

    public function test_homepage_view_receives_blocks_and_seo(): void
    {
        $response = $this->get('/');

        $response->assertViewHas('blocks');
        $response->assertViewHas('seo');
        $response->assertViewHas('homepage');
    }

    public function test_homepage_blocks_contain_expected_sections(): void
    {
        $response = $this->get('/');

        $blocks = $response->viewData('blocks');

        $this->assertArrayHasKey('hero', $blocks);
        $this->assertArrayHasKey('about', $blocks);
        $this->assertArrayHasKey('services', $blocks);
        $this->assertArrayHasKey('why_choose_us', $blocks);
        $this->assertArrayHasKey('how_it_works', $blocks);
        $this->assertArrayHasKey('cta', $blocks);
        $this->assertArrayHasKey('footer', $blocks);
        $this->assertArrayHasKey('navigation', $blocks);
    }

    public function test_homepage_blocks_have_bilingual_content(): void
    {
        $response = $this->get('/');
        $blocks = $response->viewData('blocks');

        $this->assertArrayHasKey('en', $blocks['hero']['title']);
        $this->assertArrayHasKey('ar', $blocks['hero']['title']);
        $this->assertNotEmpty($blocks['hero']['title']['en']);
        $this->assertNotEmpty($blocks['hero']['title']['ar']);
    }

    public function test_homepage_default_google_play_url_points_to_play_store(): void
    {
        $page = Page::firstOrCreateHomepage();

        $this->assertSame(
            Page::ANDROID_PLAY_STORE_URL,
            $page->content_blocks['hero']['google_play_url']
        );
    }

    public function test_homepage_renders_play_store_fallback_for_placeholder_url(): void
    {
        $blocks = Page::defaultContentBlocks();
        $blocks['hero']['google_play_url'] = '#';

        Page::create([
            'title' => 'Homepage',
            'slug' => 'home',
            'content' => '',
            'is_active' => true,
            'content_blocks' => $blocks,
            'seo_translations' => ['en' => [], 'ar' => []],
        ]);

        $response = $this->get('/');

        $this->assertStringContainsString(
            str_replace('/', '\\/', Page::ANDROID_PLAY_STORE_URL),
            $response->getContent()
        );
    }

    public function test_api_homepage_returns_json(): void
    {
        Page::firstOrCreateHomepage();

        $response = $this->getJson('/api/v1/pages/home');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'slug',
                    'locale',
                    'dir',
                    'alternate_locale',
                    'seo',
                    'title',
                    'sections',
                ],
            ]);
    }

    public function test_api_homepage_respects_locale_parameter(): void
    {
        Page::firstOrCreateHomepage();

        $responseEn = $this->getJson('/api/v1/pages/home?locale=en');
        $responseEn->assertStatus(200)
            ->assertJsonPath('data.locale', 'en')
            ->assertJsonPath('data.dir', 'ltr');

        $responseAr = $this->getJson('/api/v1/pages/home?locale=ar');
        $responseAr->assertStatus(200)
            ->assertJsonPath('data.locale', 'ar')
            ->assertJsonPath('data.dir', 'rtl');
    }

    public function test_api_returns_404_for_inactive_page(): void
    {
        $page = Page::firstOrCreateHomepage();
        $page->update(['is_active' => false]);

        $response = $this->getJson('/api/v1/pages/home');

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_api_returns_404_for_nonexistent_slug(): void
    {
        $response = $this->getJson('/api/v1/pages/nonexistent');

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_page_model_get_translation(): void
    {
        $page = Page::firstOrCreateHomepage();

        $enTitle = $page->getTranslation('title_translations', 'en');
        $arTitle = $page->getTranslation('title_translations', 'ar');

        $this->assertNotEmpty($enTitle);
        $this->assertNotEmpty($arTitle);
        $this->assertNotEquals($enTitle, $arTitle);
    }

    public function test_page_model_direction_for_locale(): void
    {
        $this->assertEquals('ltr', Page::getDirectionForLocale('en'));
        $this->assertEquals('rtl', Page::getDirectionForLocale('ar'));
        $this->assertEquals('ltr', Page::getDirectionForLocale('fr'));
    }

    public function test_page_model_localized_sections(): void
    {
        $page = Page::firstOrCreateHomepage();

        $sectionsEn = $page->getLocalizedSections('en');
        $sectionsAr = $page->getLocalizedSections('ar');

        $this->assertArrayHasKey('hero', $sectionsEn);
        $this->assertArrayHasKey('hero', $sectionsAr);

        // Localized sections should have resolved strings, not {en, ar} arrays
        $this->assertIsString($sectionsEn['hero']['title']);
        $this->assertIsString($sectionsAr['hero']['title']);
        $this->assertNotEquals($sectionsEn['hero']['title'], $sectionsAr['hero']['title']);
    }
}
