<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_description',
        'is_active',
        'title_translations',
        'excerpt_translations',
        'content_translations',
        'seo_translations',
        'content_blocks',
        'schema_markup',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'title_translations' => 'array',
        'excerpt_translations' => 'array',
        'content_translations' => 'array',
        'seo_translations' => 'array',
        'content_blocks' => 'array',
        'schema_markup' => 'array',
    ];

    public function scopeHomepage($query)
    {
        return $query->where('slug', 'home');
    }

    public static function firstOrCreateHomepage(): self
    {
        return static::firstOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Homepage',
                'content' => '',
                'is_active' => true,
                'title_translations' => ['en' => 'Homepage', 'ar' => 'الصفحة الرئيسية'],
                'excerpt_translations' => ['en' => '', 'ar' => ''],
                'content_translations' => ['en' => '', 'ar' => ''],
                'seo_translations' => [
                    'en' => ['title' => 'PUMP - Fast & Reliable Maintenance Services', 'meta_description' => 'Easy booking with trusted technicians.', 'og_title' => '', 'og_description' => ''],
                    'ar' => ['title' => 'PUMP - خدمات صيانة سريعة وموثوقة', 'meta_description' => 'حجز سهل مع فنيين موثوقين.', 'og_title' => '', 'og_description' => ''],
                ],
                'content_blocks' => self::defaultContentBlocks(),
                'schema_markup' => ['en' => [], 'ar' => []],
            ]
        );
    }

    public function getTranslation(string $field, string $locale, mixed $fallback = null): mixed
    {
        $translations = $this->{$field};

        if (!is_array($translations)) {
            return $fallback;
        }

        return $translations[$locale] ?? $translations['en'] ?? $fallback;
    }

    public function getLocalizedSections(string $locale): array
    {
        $blocks = $this->content_blocks ?? [];
        return self::resolveLocaleInBlocks($blocks, $locale);
    }

    public static function getDirectionForLocale(string $locale): string
    {
        return $locale === 'ar' ? 'rtl' : 'ltr';
    }

    public static function defaultContentBlocks(): array
    {
        return [
            'logo' => [
                'text' => 'PUMP',
                'image_url' => '',
            ],
            'navigation' => [
                'items' => [
                    ['href' => '#about', 'label' => ['en' => 'About', 'ar' => 'عن PUMP']],
                    ['href' => '#services', 'label' => ['en' => 'Services', 'ar' => 'خدماتنا']],
                    ['href' => '#how-it-works', 'label' => ['en' => 'How It Works', 'ar' => 'كيف تعمل']],
                    ['href' => '#contact', 'label' => ['en' => 'Contact', 'ar' => 'تواصل معنا']],
                ],
            ],
            'hero' => [
                'title' => ['en' => 'Fast & Reliable Maintenance Services', 'ar' => 'خدمات صيانة سريعة وموثوقة'],
                'description' => ['en' => 'Easy booking with trusted, verified technicians. Professional service delivered right to your doorstep.', 'ar' => 'حجز سهل مع فنيين موثوقين ومعتمدين. خدمة احترافية تصل إلى باب منزلك.'],
                'image_url' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=600&h=600&fit=crop',
                'google_play_url' => '#',
                'app_store_url' => '',
                'app_store_badge_mode' => 'coming_soon',
                'app_store_badge_label' => ['en' => 'Coming Soon', 'ar' => 'قريباً'],
            ],
            'about' => [
                'title' => ['en' => 'About PUMP', 'ar' => 'عن PUMP'],
                'body' => ['en' => 'PUMP helps customers get maintenance services easily by connecting them with professional service providers, ensuring quality, speed, and customer satisfaction.', 'ar' => 'تساعد PUMP العملاء في الحصول على خدمات الصيانة بسهولة من خلال ربطهم بمقدمي الخدمات المحترفين، مما يضمن الجودة والسرعة ورضا العملاء.'],
                'image_url' => 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=600&h=400&fit=crop',
            ],
            'services' => [
                'title' => ['en' => 'Our Services', 'ar' => 'خدماتنا'],
                'description' => ['en' => 'Comprehensive maintenance solutions for all your needs', 'ar' => 'حلول صيانة شاملة لجميع احتياجاتك'],
                'items' => [
                    ['icon' => 'sparkles', 'title' => ['en' => 'General Cleaning', 'ar' => 'التنظيف العام'], 'description' => ['en' => 'Professional cleaning for homes and offices', 'ar' => 'تنظيف احترافي للمنازل والمكاتب']],
                    ['icon' => 'wrench-screwdriver', 'title' => ['en' => 'Plumbing', 'ar' => 'السباكة'], 'description' => ['en' => 'Expert plumbing repairs and installations', 'ar' => 'إصلاحات وتركيبات السباكة الخبيرة']],
                    ['icon' => 'bolt', 'title' => ['en' => 'Electrical', 'ar' => 'الكهرباء'], 'description' => ['en' => 'Safe and certified electrical services', 'ar' => 'خدمات كهربائية آمنة ومعتمدة']],
                    ['icon' => 'sun', 'title' => ['en' => 'Air Conditioning', 'ar' => 'التكييف والتبريد'], 'description' => ['en' => 'AC maintenance, repair, and installation', 'ar' => 'صيانة وإصلاح وتركيب المكيفات']],
                    ['icon' => 'cpu-chip', 'title' => ['en' => 'Home Appliances', 'ar' => 'صيانة الأجهزة المنزلية'], 'description' => ['en' => 'Repair and maintenance for all appliances', 'ar' => 'إصلاح وصيانة جميع الأجهزة']],
                    ['icon' => 'paint-brush', 'title' => ['en' => 'Painting & Finishing', 'ar' => 'الدهان والتشطيب'], 'description' => ['en' => 'Interior and exterior painting services', 'ar' => 'خدمات الدهان الداخلي والخارجي']],
                    ['icon' => 'wrench', 'title' => ['en' => 'Carpentry', 'ar' => 'النجارة'], 'description' => ['en' => 'Custom woodwork and furniture repair', 'ar' => 'أعمال خشبية مخصصة وإصلاح الأثاث']],
                    ['icon' => 'bug-ant', 'title' => ['en' => 'Pest Control', 'ar' => 'مكافحة الحشرات'], 'description' => ['en' => 'Safe and effective pest elimination', 'ar' => 'القضاء الآمن والفعال على الآفات']],
                    ['icon' => 'truck', 'title' => ['en' => 'Moving Services', 'ar' => 'خدمات النقل'], 'description' => ['en' => 'Professional packing and moving assistance', 'ar' => 'مساعدة احترافية في التعبئة والنقل']],
                    ['icon' => 'archive-box', 'title' => ['en' => 'Car Wash', 'ar' => 'غسيل السيارات'], 'description' => ['en' => 'Premium car cleaning and detailing services', 'ar' => 'خدمات غسيل وتنظيف السيارات المتميزة']],
                ],
            ],
            'why_choose_us' => [
                'title' => ['en' => 'Why Choose PUMP', 'ar' => 'لماذا تختار PUMP'],
                'description' => ['en' => 'Quality service you can trust', 'ar' => 'خدمة عالية الجودة يمكنك الوثوق بها'],
                'items' => [
                    ['icon' => 'shield-check', 'title' => ['en' => '30-Day Service Guarantee', 'ar' => 'ضمان الخدمة لمدة 30 يومًا'], 'description' => ['en' => 'We stand behind our work with a comprehensive guarantee', 'ar' => 'نقف وراء عملنا بضمان شامل']],
                    ['icon' => 'check-badge', 'title' => ['en' => 'Verified Technicians', 'ar' => 'فنيون معتمدون'], 'description' => ['en' => 'All service providers are thoroughly vetted and certified', 'ar' => 'جميع مقدمي الخدمات تم فحصهم واعتمادهم بدقة']],
                    ['icon' => 'currency-dollar', 'title' => ['en' => 'Competitive & Transparent Pricing', 'ar' => 'أسعار تنافسية وشفافة'], 'description' => ['en' => 'Fair rates with no hidden fees - know exactly what you\'ll pay', 'ar' => 'أسعار عادلة بدون رسوم خفية - اعرف بالضبط ما ستدفعه']],
                    ['icon' => 'rocket-launch', 'title' => ['en' => 'Easy & Fast Booking', 'ar' => 'حجز سهل وسريع'], 'description' => ['en' => 'Schedule services quickly through our simple platform', 'ar' => 'جدولة الخدمات بسرعة من خلال منصتنا البسيطة']],
                    ['icon' => 'squares-2x2', 'title' => ['en' => 'Wide Range of Services', 'ar' => 'مجموعة واسعة من الخدمات'], 'description' => ['en' => 'Comprehensive solutions for all your maintenance needs', 'ar' => 'حلول شاملة لجميع احتياجات الصيانة الخاصة بك']],
                    ['icon' => 'phone', 'title' => ['en' => 'Continuous Customer Support', 'ar' => 'دعم العملاء المستمر'], 'description' => ['en' => '24/7 support team ready to assist you anytime', 'ar' => 'فريق دعم على مدار الساعة جاهز لمساعدتك في أي وقت']],
                ],
            ],
            'how_it_works' => [
                'title' => ['en' => 'How It Works', 'ar' => 'كيف تعمل'],
                'description' => ['en' => 'Getting quality service is simple and straightforward', 'ar' => 'الحصول على خدمة عالية الجودة بسيط ومباشر'],
                'steps' => [
                    ['number' => '01', 'title' => ['en' => 'Choose the Service', 'ar' => 'اختر الخدمة'], 'description' => ['en' => 'Select the service you need from our wide range of options', 'ar' => 'اختر الخدمة التي تحتاجها من مجموعتنا الواسعة من الخيارات']],
                    ['number' => '02', 'title' => ['en' => 'Select Time & Location', 'ar' => 'اختر الوقت والموقع'], 'description' => ['en' => 'Pick your preferred date, time, and service location', 'ar' => 'اختر التاريخ والوقت وموقع الخدمة المفضل لديك']],
                    ['number' => '03', 'title' => ['en' => 'Confirm the Request', 'ar' => 'أكد الطلب'], 'description' => ['en' => 'Review and confirm your service booking details', 'ar' => 'راجع وأكد تفاصيل حجز الخدمة الخاصة بك']],
                    ['number' => '04', 'title' => ['en' => 'Technician Arrives on Time', 'ar' => 'وصول الفني في الوقت المحدد'], 'description' => ['en' => 'Our verified professional arrives punctually and completes the job', 'ar' => 'يصل المحترف المعتمد لدينا في الوقت المحدد ويكمل العمل']],
                ],
            ],
            'cta' => [
                'title' => ['en' => 'Download PUMP App Now', 'ar' => 'حمّل تطبيق PUMP الآن'],
                'description' => ['en' => 'Get instant access to professional maintenance services. Available on Android, coming soon to iOS.', 'ar' => 'احصل على وصول فوري لخدمات الصيانة الاحترافية. متاح على أندرويد، قريباً على iOS.'],
            ],
            'footer' => [
                'brand_blurb' => ['en' => 'Your trusted platform for professional home and commercial maintenance services. Quality you can count on.', 'ar' => 'منصتك الموثوقة للخدمات المهنية لصيانة المنازل والأعمال التجارية. جودة يمكنك الاعتماد عليها.'],
                'contact_title' => ['en' => 'Contact Us', 'ar' => 'تواصل معنا'],
                'quick_links_title' => ['en' => 'Quick Links', 'ar' => 'روابط سريعة'],
                'social_title' => ['en' => 'Follow Us', 'ar' => 'تابعنا'],
                'copyright' => ['en' => 'PUMP. All rights reserved.', 'ar' => 'PUMP. جميع الحقوق محفوظة.'],
                'contact' => [
                    'phone' => '+1 (800) PUMP-123',
                    'email' => 'hello@pump.services',
                    'address' => ['en' => '123 Service Ave, City, State', 'ar' => '123 شارع الخدمة، المدينة، الولاية'],
                ],
            ],
        ];
    }

    private static function resolveLocaleInBlocks(array $data, string $locale): array
    {
        $resolved = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (isset($value['en']) && isset($value['ar']) && count($value) === 2 && is_string($value['en'])) {
                    $resolved[$key] = $value[$locale] ?? $value['en'] ?? '';
                } else {
                    $resolved[$key] = self::resolveLocaleInBlocks($value, $locale);
                }
            } else {
                $resolved[$key] = $value;
            }
        }
        return $resolved;
    }
}
