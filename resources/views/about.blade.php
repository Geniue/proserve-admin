<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>عن التطبيق — PUMP</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Noto Sans Arabic', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">
    <header class="bg-white border-b border-gray-200">
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
        <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm p-8 md:p-12">
            <div class="text-center mb-10">
                <h1 class="text-3xl font-extrabold text-gray-900 mb-3">PUMP</h1>
                <p class="text-lg text-gray-500">خدمات صيانة المنازل، غسيل السيارات، والتنظيف</p>
            </div>

            <section class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-3 border-b border-gray-200 pb-2">عن التطبيق</h2>
                <p class="text-gray-600 leading-relaxed mb-3">
                    تطبيق PUMP هو منصة إلكترونية تربط العملاء بمقدمي خدمات الصيانة والتنظيف المؤهلين في المملكة العربية السعودية.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    نوفّر خدمات صيانة المنازل، غسيل السيارات، والتنظيف بجودة عالية وأسعار تنافسية، مع ضمان سهولة الحجز ومتابعة الطلبات عبر التطبيق.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-3 border-b border-gray-200 pb-2">خدماتنا</h2>
                <ul class="space-y-2 text-gray-600 pr-5 list-disc">
                    <li>صيانة المنازل (كهرباء، سباكة، تكييف، دهانات)</li>
                    <li>غسيل السيارات (في الموقع)</li>
                    <li>خدمات التنظيف المنزلي</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-800 mb-3 border-b border-gray-200 pb-2">تواصل معنا</h2>
                <table class="w-full text-sm">
                    <tbody>
                        <tr class="border-b border-gray-100">
                            <td class="py-3 font-semibold text-gray-700 w-1/3">البريد الإلكتروني</td>
                            <td class="py-3"><a href="mailto:support@pumpnow.app" class="text-blue-600 hover:underline">support@pumpnow.app</a></td>
                        </tr>
                        <tr class="border-b border-gray-100">
                            <td class="py-3 font-semibold text-gray-700">بريد الخصوصية</td>
                            <td class="py-3"><a href="mailto:privacy@pumpnow.app" class="text-blue-600 hover:underline">privacy@pumpnow.app</a></td>
                        </tr>
                        <tr>
                            <td class="py-3 font-semibold text-gray-700">الموقع الإلكتروني</td>
                            <td class="py-3"><a href="https://pumpnow.app" class="text-blue-600 hover:underline">pumpnow.app</a></td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
    </main>

    <footer class="border-t border-gray-200 mt-12">
        <div class="container mx-auto px-6 py-6 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} PUMP. جميع الحقوق محفوظة.
        </div>
    </footer>
</body>
</html>
