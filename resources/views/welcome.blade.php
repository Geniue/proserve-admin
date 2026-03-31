<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ 
          darkMode: localStorage.getItem('darkMode') === 'true', 
          lang: localStorage.getItem('lang') || 'en',
          blocks: {{ Js::from($blocks) }},
          seo: {{ Js::from($seo) }},
          t(obj) { 
              if (!obj) return ''; 
              if (typeof obj === 'string') return obj; 
              return obj[this.lang] || obj['en'] || ''; 
          },
          get footerLogoSrc() {
              return (this.blocks.footer?.logo_image_url) || (this.blocks.logo?.white_image_url) || (this.blocks.logo?.dark_image_url) || '';
          }
      }" 
      x-init="
          $watch('darkMode', val => localStorage.setItem('darkMode', val)); 
          $watch('lang', val => {
              localStorage.setItem('lang', val);
              document.documentElement.dir = val === 'ar' ? 'rtl' : 'ltr';
              document.documentElement.lang = val;
          });
          document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
          document.documentElement.lang = lang;
      " 
      :class="{ 'dark': darkMode }" 
      :dir="lang === 'ar' ? 'rtl' : 'ltr'">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title x-text="seo[lang]?.title || 'PUMP - Fast & Reliable Maintenance Services'"></title>
    <meta name="description" :content="seo[lang]?.meta_description || ''">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Arabic:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    
    <!-- Iconify (for dynamic icon rendering) -->
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@2/dist/iconify-icon.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        body {
            font-family: 'Inter', 'Noto Sans Arabic', sans-serif;
        }
        
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
    </style>
    <script src="https://analytics.ahrefs.com/analytics.js" data-key="qiPuIAZrOjGu4euMtfYb+w" async></script>
</head>
    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 bg-white/90 dark:bg-gray-900/90 backdrop-blur-md z-50 border-b border-gray-200 dark:border-gray-800">
        <nav class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-2" style="overflow: hidden; height: 48px;">
                    <img x-show="darkMode && blocks.logo?.white_image_url" :src="blocks.logo?.white_image_url" alt="Logo" style="height: 64px; width: auto; max-width: 240px; object-fit: contain; transform: scale(1.4); transform-origin: center;">
                    <img x-show="!darkMode && blocks.logo?.dark_image_url" :src="blocks.logo?.dark_image_url" alt="Logo" style="height: 64px; width: auto; max-width: 240px; object-fit: contain; transform: scale(1.4); transform-origin: center;">
                    <img x-show="darkMode && !blocks.logo?.white_image_url && blocks.logo?.dark_image_url" :src="blocks.logo?.dark_image_url" alt="Logo" style="height: 64px; width: auto; max-width: 240px; object-fit: contain; transform: scale(1.4); transform-origin: center;">
                    <img x-show="!darkMode && !blocks.logo?.dark_image_url && blocks.logo?.white_image_url" :src="blocks.logo?.white_image_url" alt="Logo" style="height: 64px; width: auto; max-width: 240px; object-fit: contain; transform: scale(1.4); transform-origin: center;">
                    <svg x-show="!blocks.logo?.dark_image_url && !blocks.logo?.white_image_url" style="width: 36px; height: 36px; color: #2563eb;" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5zm0 18c-3.87-.96-7-5.54-7-10V8.3l7-3.11 7 3.11V10c0 4.46-3.13 9.04-7 10z"/>
                    </svg>
                    <span x-show="blocks.logo?.text" class="text-2xl font-bold" x-text="blocks.logo?.text"></span>
                </a>
                
                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center gap-8">
                    <template x-for="item in (blocks.navigation?.items || [])" :key="item.href">
                        <a :href="item.href" class="hover:text-blue-600 transition-colors" x-text="t(item.label)"></a>
                    </template>
                </div>
                
                <!-- Theme & Language Toggle -->
                <div class="flex items-center gap-4">
                    <!-- Dark Mode Toggle -->
                    <button @click="darkMode = !darkMode" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </button>
                    
                    <!-- Language Toggle -->
                    <button @click="lang = lang === 'en' ? 'ar' : 'en'" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                        </svg>
                        <span x-text="lang === 'en' ? 'العربية' : 'English'"></span>
                    </button>
                    
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/admin') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <span x-show="lang === 'en'">Dashboard</span>
                                <span x-show="lang === 'ar'">لوحة التحكم</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <span x-show="lang === 'en'">Admin Login</span>
                                <span x-show="lang === 'ar'">دخول المسؤول</span>
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-800 dark:to-gray-900">
        <div class="container mx-auto px-6">
            <div class="flex flex-col lg:flex-row items-center gap-12" :class="lang === 'ar' ? 'lg:flex-row-reverse' : ''">
                <div class="flex-1 text-center" :class="lang === 'ar' ? 'lg:text-right' : 'lg:text-left'">
                    <h1 class="text-5xl lg:text-6xl font-bold mb-6 leading-tight" x-html="t(blocks.hero?.title)?.includes(' ') ? t(blocks.hero?.title).replace(/^(.+?)(\s)/, '$1<br><span class=\'text-blue-600\'>') + '</span>' : t(blocks.hero?.title)"></h1>
                    <p class="text-xl text-gray-600 dark:text-gray-300 mb-8" x-text="t(blocks.hero?.description)"></p>
                    <div class="flex flex-col sm:flex-row gap-4 items-center justify-center lg:justify-start mb-6">
                        <!-- Google Play (shown first in EN, second in AR) -->
                        <a :href="blocks.hero?.google_play_url || '#'" class="inline-flex items-center gap-3 px-6 py-3 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors" :class="lang === 'ar' ? 'order-2' : 'order-1'" dir="ltr">
                            <svg class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.9 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z"/>
                            </svg>
                            <div class="text-left">
                                <div class="text-xs">GET IT ON</div>
                                <div class="text-lg font-semibold -mt-1">Google Play</div>
                            </div>
                        </a>
                        <!-- App Store Badge -->
                        <template x-if="blocks.hero?.app_store_badge_mode !== 'hidden'">
                            <div class="relative inline-flex items-center gap-3 px-6 py-3 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed opacity-60" :class="lang === 'ar' ? 'order-1' : 'order-2'" dir="ltr">
                                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M18.71,19.5C17.88,20.74 17,21.95 15.66,21.97C14.32,22 13.89,21.18 12.37,21.18C10.84,21.18 10.37,21.95 9.1,22C7.79,22.05 6.8,20.68 5.96,19.47C4.25,17 2.94,12.45 4.7,9.39C5.57,7.87 7.13,6.91 8.82,6.88C10.1,6.86 11.32,7.75 12.11,7.75C12.89,7.75 14.37,6.68 15.92,6.84C16.57,6.87 18.39,7.1 19.56,8.82C19.47,8.88 17.39,10.1 17.41,12.63C17.44,15.65 20.06,16.66 20.09,16.67C20.06,16.74 19.67,18.11 18.71,19.5M13,3.5C13.73,2.67 14.94,2.04 15.94,2C16.07,3.17 15.6,4.35 14.9,5.19C14.21,6.04 13.07,6.7 11.95,6.61C11.8,5.46 12.36,4.26 13,3.5Z"/>
                                </svg>
                                <div class="text-left">
                                    <div class="text-xs">Download on the</div>
                                    <div class="text-lg font-semibold -mt-1">App Store</div>
                                </div>
                                <template x-if="blocks.hero?.app_store_badge_mode === 'coming_soon'">
                                    <span class="absolute -top-2 -right-2 bg-yellow-400 text-gray-900 text-xs font-bold px-2 py-1 rounded-full" x-text="t(blocks.hero?.app_store_badge_label) || (lang === 'ar' ? 'قريباً' : 'Coming Soon')"></span>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex-1">
                    <img :src="blocks.hero?.image_url || ''" alt="Maintenance Services" class="rounded-2xl shadow-2xl" loading="lazy">
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 bg-white dark:bg-gray-900">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-4xl font-bold text-center mb-6" x-text="t(blocks.about?.title)"></h2>
                
                <div class="flex flex-col lg:flex-row items-center gap-12" :class="lang === 'ar' ? 'lg:flex-row-reverse' : ''">
                    <div class="flex-1">
                        <img :src="blocks.about?.image_url || ''" alt="About PUMP" class="rounded-lg shadow-lg" loading="lazy">
                    </div>
                    <div class="flex-1">
                        <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed" :class="lang === 'ar' ? 'text-right' : ''" x-text="t(blocks.about?.body)"></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-20 bg-gray-50 dark:bg-gray-800">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center mb-4" x-text="t(blocks.services?.title)"></h2>
            <p class="text-center text-gray-600 dark:text-gray-300 mb-12 max-w-2xl mx-auto" x-text="t(blocks.services?.description)"></p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6"
                 x-data="{
                     svcBg: [
                         'bg-blue-100 dark:bg-blue-900', 'bg-green-100 dark:bg-green-900',
                         'bg-yellow-100 dark:bg-yellow-900', 'bg-cyan-100 dark:bg-cyan-900',
                         'bg-purple-100 dark:bg-purple-900', 'bg-red-100 dark:bg-red-900',
                         'bg-orange-100 dark:bg-orange-900', 'bg-pink-100 dark:bg-pink-900',
                         'bg-indigo-100 dark:bg-indigo-900', 'bg-teal-100 dark:bg-teal-900'
                     ],
                     svcFg: [
                         'color: #2563eb', 'color: #16a34a', 'color: #ca8a04', 'color: #0891b2',
                         'color: #9333ea', 'color: #dc2626', 'color: #ea580c', 'color: #db2777',
                         'color: #4f46e5', 'color: #0d9488'
                     ]
                 }">
                <template x-for="(service, idx) in (blocks.services?.items || [])" :key="idx">
                    <div class="bg-white dark:bg-gray-700 rounded-xl p-6 shadow-md hover:shadow-xl transition-shadow text-center">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
                             :class="svcBg[idx % svcBg.length]">
                            <iconify-icon
                                :icon="'heroicons:' + (service.icon || 'cube')"
                                width="32" height="32"
                                :style="svcFg[idx % svcFg.length]">
                            </iconify-icon>
                        </div>
                        <h3 class="font-semibold text-lg mb-2" x-text="t(service.title)"></h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400" x-text="t(service.description)"></p>
                    </div>
                </template>
            </div>
        </div>
    </section>

    <!-- Why Choose PUMP Section -->
    <section class="py-20 bg-white dark:bg-gray-900">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center mb-4" x-text="t(blocks.why_choose_us?.title)"></h2>
            <p class="text-center text-gray-600 dark:text-gray-300 mb-12 max-w-2xl mx-auto" x-text="t(blocks.why_choose_us?.description)"></p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
                 x-data="{
                     whyGrad: [
                         'from-blue-500 to-blue-600', 'from-green-500 to-green-600',
                         'from-yellow-500 to-yellow-600', 'from-purple-500 to-purple-600',
                         'from-red-500 to-red-600', 'from-cyan-500 to-cyan-600'
                     ]
                 }">
                <template x-for="(reason, idx) in (blocks.why_choose_us?.items || [])" :key="idx">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-gradient-to-br rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg"
                             :class="whyGrad[idx % whyGrad.length]">
                            <iconify-icon
                                :icon="'heroicons:' + (reason.icon || 'check-circle')"
                                width="40" height="40"
                                style="color: white">
                            </iconify-icon>
                        </div>
                        <h3 class="text-xl font-semibold mb-3" x-text="t(reason.title)"></h3>
                        <p class="text-gray-600 dark:text-gray-400" x-text="t(reason.description)"></p>
                    </div>
                </template>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-800 dark:to-gray-900">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center mb-4" x-text="t(blocks.how_it_works?.title)"></h2>
            <p class="text-center text-gray-600 dark:text-gray-300 mb-16 max-w-2xl mx-auto" x-text="t(blocks.how_it_works?.description)"></p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                @php
                    $stepColors = ['blue', 'green', 'yellow', 'purple'];
                @endphp
                <template x-for="(step, idx) in (blocks.how_it_works?.steps || [])" :key="idx">
                    <div class="relative text-center">
                        <div class="relative z-10">
                            <div class="w-24 h-24 bg-blue-600 text-white rounded-full flex items-center justify-center mx-auto mb-6 text-3xl font-bold shadow-lg" x-text="step.number"></div>
                            <h3 class="text-xl font-semibold mb-3" x-text="t(step.title)"></h3>
                            <p class="text-gray-600 dark:text-gray-400" x-text="t(step.description)"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-blue-600 text-white">
        <div class="container mx-auto px-6 text-center">
            <h2 class="text-4xl font-bold mb-6" x-text="t(blocks.cta?.title)"></h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto opacity-90" x-text="t(blocks.cta?.description)"></p>
            <div class="flex flex-col sm:flex-row gap-4 items-center justify-center">
                <!-- Google Play -->
                <a :href="blocks.hero?.google_play_url || '#'" class="inline-flex items-center gap-3 px-6 py-3 bg-white text-gray-900 rounded-lg hover:bg-gray-100 transition-colors shadow-lg hover:shadow-xl" :class="lang === 'ar' ? 'order-2' : 'order-1'" dir="ltr">
                    <svg class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.9 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z"/>
                    </svg>
                    <div class="text-left">
                        <div class="text-xs">GET IT ON</div>
                        <div class="text-lg font-semibold -mt-1">Google Play</div>
                    </div>
                </a>
                <!-- App Store - Coming Soon -->
                <template x-if="blocks.hero?.app_store_badge_mode !== 'hidden'">
                    <div class="relative inline-flex items-center gap-3 px-6 py-3 bg-white/30 text-white rounded-lg cursor-not-allowed opacity-60" :class="lang === 'ar' ? 'order-1' : 'order-2'" dir="ltr">
                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18.71,19.5C17.88,20.74 17,21.95 15.66,21.97C14.32,22 13.89,21.18 12.37,21.18C10.84,21.18 10.37,21.95 9.1,22C7.79,22.05 6.8,20.68 5.96,19.47C4.25,17 2.94,12.45 4.7,9.39C5.57,7.87 7.13,6.91 8.82,6.88C10.1,6.86 11.32,7.75 12.11,7.75C12.89,7.75 14.37,6.68 15.92,6.84C16.57,6.87 18.39,7.1 19.56,8.82C19.47,8.88 17.39,10.1 17.41,12.63C17.44,15.65 20.06,16.66 20.09,16.67C20.06,16.74 19.67,18.11 18.71,19.5M13,3.5C13.73,2.67 14.94,2.04 15.94,2C16.07,3.17 15.6,4.35 14.9,5.19C14.21,6.04 13.07,6.7 11.95,6.61C11.8,5.46 12.36,4.26 13,3.5Z"/>
                        </svg>
                        <div class="text-left">
                            <div class="text-xs">Download on the</div>
                            <div class="text-lg font-semibold -mt-1">App Store</div>
                        </div>
                        <template x-if="blocks.hero?.app_store_badge_mode === 'coming_soon'">
                            <span class="absolute -top-2 -right-2 bg-yellow-400 text-gray-900 text-xs font-bold px-2 py-1 rounded-full" x-text="t(blocks.hero?.app_store_badge_label) || (lang === 'ar' ? 'قريباً' : 'Coming Soon')"></span>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="bg-gray-900 text-white py-16">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
                <!-- Column 1: Brand (becomes column 4 in RTL) -->
                <div :class="lang === 'ar' ? 'lg:order-4 text-right' : 'lg:order-1'">
                    <div class="flex items-center gap-2 mb-4" :class="lang === 'ar' ? 'flex-row-reverse justify-end' : ''" style="overflow: hidden; height: 40px;">
                        <img x-show="footerLogoSrc" :src="footerLogoSrc" alt="Logo" style="height: 64px; width: auto; max-width: 240px; object-fit: contain; transform: scale(1.4); transform-origin: center;">
                        <svg x-show="!footerLogoSrc" style="width: 36px; height: 36px; color: #3b82f6;" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5zm0 18c-3.87-.96-7-5.54-7-10V8.3l7-3.11 7 3.11V10c0 4.46-3.13 9.04-7 10z"/>
                        </svg>
                        <span x-show="blocks.logo?.text" class="text-2xl font-bold" x-text="blocks.logo?.text"></span>
                    </div>
                    <p class="text-gray-400" x-text="t(blocks.footer?.brand_blurb)"></p>
                </div>

                <!-- Column 2: Contact (becomes column 3 in RTL) -->
                <div :class="lang === 'ar' ? 'lg:order-3 text-right' : 'lg:order-2'">
                    <h3 class="text-lg font-semibold mb-4" x-text="t(blocks.footer?.contact_title)"></h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3" :class="lang === 'ar' ? 'flex-row-reverse justify-end' : ''">
                            <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <span class="text-gray-400" x-text="blocks.footer?.contact?.phone"></span>
                        </div>
                        <div class="flex items-center gap-3" :class="lang === 'ar' ? 'flex-row-reverse justify-end' : ''">
                            <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-gray-400" x-text="blocks.footer?.contact?.email"></span>
                        </div>
                        <div class="flex items-center gap-3" :class="lang === 'ar' ? 'flex-row-reverse justify-end' : ''">
                            <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-gray-400" x-text="t(blocks.footer?.contact?.address)"></span>
                        </div>
                    </div>
                </div>

                <!-- Column 3: Quick Links (becomes column 2 in RTL) -->
                <div :class="lang === 'ar' ? 'lg:order-2 text-right' : 'lg:order-3'">
                    <h3 class="text-lg font-semibold mb-4" x-text="t(blocks.footer?.quick_links_title)"></h3>
                    <ul class="space-y-2">
                        <template x-for="item in (blocks.navigation?.items || [])" :key="'footer-' + item.href">
                            <li><a :href="item.href" class="text-gray-400 hover:text-white transition-colors" x-text="t(item.label)"></a></li>
                        </template>
                    </ul>
                </div>

                <!-- Column 4: Social Media (becomes column 1 in RTL) -->
                <div :class="lang === 'ar' ? 'lg:order-1 text-right' : 'lg:order-4'">
                    <h3 class="text-lg font-semibold mb-4" x-text="t(blocks.footer?.social_title)"></h3>
                    <div class="flex gap-4" :class="lang === 'ar' ? 'flex-row-reverse justify-end' : ''">
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-blue-600 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-blue-400 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-pink-600 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-blue-700 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} <span x-text="t(blocks.footer?.copyright)"></span></p>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button -->
    <button 
        x-data="{ showScroll: false }" 
        @scroll.window="showScroll = window.pageYOffset > 300"
        x-show="showScroll"
        x-transition
        @click="window.scrollTo({top: 0, behavior: 'smooth'})"
        class="fixed bottom-8 z-50 w-12 h-12 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 transition-colors flex items-center justify-center"
        :class="lang === 'ar' ? 'left-8' : 'right-8'">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
    </button>
</body>
</html>
