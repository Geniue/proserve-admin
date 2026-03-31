<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Noto Sans Arabic', sans-serif; }

        .prose { max-width: 65ch; }
        .prose h1 { font-size: 1.875rem; font-weight: 800; margin-bottom: 1rem; color: #111827; }
        .prose h2 { font-size: 1.375rem; font-weight: 700; margin-top: 2rem; margin-bottom: 0.75rem; color: #1f2937; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.5rem; }
        .prose h3 { font-size: 1.125rem; font-weight: 600; margin-top: 1.5rem; margin-bottom: 0.5rem; color: #374151; }
        .prose p { margin-bottom: 0.75rem; line-height: 1.8; color: #4b5563; }
        .prose ul, .prose ol { margin-bottom: 1rem; padding-right: 1.5rem; color: #4b5563; }
        .prose li { margin-bottom: 0.375rem; line-height: 1.7; }
        .prose strong { color: #111827; }
        .prose a { color: #2563eb; text-decoration: underline; }
        .prose a:hover { color: #1d4ed8; }
        .prose hr { border-color: #e5e7eb; margin: 1.5rem 0; }
        .prose table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; font-size: 0.875rem; }
        .prose th, .prose td { border: 1px solid #e5e7eb; padding: 0.5rem 0.75rem; text-align: right; }
        .prose th { background: #f9fafb; font-weight: 600; color: #374151; }
        .prose td { color: #4b5563; }
    </style>
    <script src="https://analytics.ahrefs.com/analytics.js" data-key="qiPuIAZrOjGu4euMtfYb+w" async></script>
</head>
        <nav class="container mx-auto px-6 py-4 flex items-center justify-between">
            <a href="/" class="text-2xl font-bold text-blue-600">PUMP</a>
            <div class="flex items-center gap-6 text-sm font-medium text-gray-600">
                <a href="/terms" class="hover:text-blue-600 transition-colors">الشروط والأحكام</a>
                <a href="/privacy" class="hover:text-blue-600 transition-colors">سياسة الخصوصية</a>
                <a href="/about" class="hover:text-blue-600 transition-colors">عن التطبيق</a>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-6 py-12">
        <article class="prose mx-auto bg-white rounded-xl shadow-sm p-8 md:p-12">
            {!! $content !!}
        </article>
    </main>

    <footer class="border-t border-gray-200 mt-12">
        <div class="container mx-auto px-6 py-6 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} PUMP. جميع الحقوق محفوظة.
        </div>
    </footer>
</body>
</html>
