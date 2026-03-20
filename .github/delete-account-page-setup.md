# ProServe — Delete Account Page for Google Play Compliance

> **Required by**: Google Play Data Safety section
> **URL**: `https://pumpnow.app/delete-account`
> **Purpose**: Public page where users can learn how to delete their account and what data is removed.

---

## Step-by-Step Implementation

### 1. Create the Route

**File: `routes/web.php`**

```php
Route::get('/delete-account', function () {
    return view('delete-account');
});
```

### 2. Create the Blade View

**File: `resources/views/delete-account.blade.php`**

```html
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account — ProServe (Pump Now)</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            color: #1a1a2e;
            line-height: 1.7;
        }
        .container {
            max-width: 680px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 40px 32px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        h1 {
            font-size: 24px;
            color: #0C1F41;
            margin-bottom: 8px;
        }
        .app-name {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 28px;
        }
        h2 {
            font-size: 18px;
            color: #0C1F41;
            margin-top: 28px;
            margin-bottom: 12px;
        }
        p, li {
            font-size: 15px;
            color: #374151;
        }
        ol, ul {
            padding-left: 24px;
            margin-bottom: 16px;
        }
        li { margin-bottom: 6px; }
        .highlight {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            padding: 14px 18px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .warning {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 14px 18px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .email-link {
            color: #0891b2;
            text-decoration: none;
            font-weight: 600;
        }
        .email-link:hover { text-decoration: underline; }
        .divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 28px 0;
        }
        .footer {
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
            color: #9ca3af;
        }

        /* Arabic version */
        [dir="rtl"] { text-align: right; }
        [dir="rtl"] ol, [dir="rtl"] ul { padding-left: 0; padding-right: 24px; }
        .lang-toggle {
            text-align: right;
            margin-bottom: 16px;
        }
        .lang-toggle a {
            font-size: 14px;
            color: #0891b2;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <!-- Language toggle -->
            <div class="lang-toggle">
                <a href="#arabic" onclick="toggleLang()">العربية / English</a>
            </div>

            <!-- ═══ English Version ═══ -->
            <div id="en-content">
                <h1>Delete Your Account</h1>
                <p class="app-name">ProServe (Pump Now) — Home Services App</p>

                <h2>How to Delete Your Account</h2>

                <p><strong>Option 1 — From the app:</strong></p>
                <ol>
                    <li>Open the ProServe app</li>
                    <li>Go to <strong>Settings</strong> (bottom navigation bar)</li>
                    <li>Tap <strong>Delete Account</strong></li>
                    <li>Confirm the deletion when prompted</li>
                </ol>

                <p><strong>Option 2 — By email:</strong></p>
                <p>Send an email to <a class="email-link" href="mailto:support@pumpnow.app">support@pumpnow.app</a> with the subject line <strong>"Delete My Account"</strong> and include the phone number associated with your account. We will process your request within 7 business days.</p>

                <hr class="divider">

                <h2>What Data Is Deleted</h2>
                <ul>
                    <li>Your profile information (name, email, phone number)</li>
                    <li>Your saved addresses</li>
                    <li>Your order history</li>
                    <li>Your chat messages</li>
                    <li>Your ratings and reviews</li>
                    <li>Your notification history</li>
                    <li>Your shopping cart</li>
                </ul>

                <h2>What Data May Be Retained</h2>
                <div class="highlight">
                    <p>Anonymized transaction records may be retained for up to <strong>90 days</strong> after deletion for legal, tax, and accounting purposes, as required by Saudi Arabian commercial regulations.</p>
                </div>

                <div class="warning">
                    <p><strong>Technicians:</strong> If you have pending or in-progress orders, they must be completed or cancelled before your account can be deleted.</p>
                </div>

                <h2>Processing Time</h2>
                <p>Account deletion is processed within <strong>30 days</strong> of your request. You will not be able to recover your account or data after deletion is complete.</p>
            </div>

            <!-- ═══ Arabic Version ═══ -->
            <div id="ar-content" dir="rtl" style="display: none;">
                <h1>حذف حسابك</h1>
                <p class="app-name">بروسيرف (بامب ناو) — تطبيق الخدمات المنزلية</p>

                <h2>كيفية حذف حسابك</h2>

                <p><strong>الطريقة الأولى — من التطبيق:</strong></p>
                <ol>
                    <li>افتح تطبيق بروسيرف</li>
                    <li>اذهب إلى <strong>الإعدادات</strong> (شريط التنقل السفلي)</li>
                    <li>انقر على <strong>حذف الحساب</strong></li>
                    <li>قم بتأكيد الحذف عند ظهور الرسالة</li>
                </ol>

                <p><strong>الطريقة الثانية — عبر البريد الإلكتروني:</strong></p>
                <p>أرسل بريدًا إلكترونيًا إلى <a class="email-link" href="mailto:support@pumpnow.app">support@pumpnow.app</a> مع عنوان الرسالة <strong>"حذف حسابي"</strong> وأرفق رقم الهاتف المرتبط بحسابك. سنقوم بمعالجة طلبك خلال ٧ أيام عمل.</p>

                <hr class="divider">

                <h2>البيانات التي سيتم حذفها</h2>
                <ul>
                    <li>معلومات ملفك الشخصي (الاسم، البريد الإلكتروني، رقم الهاتف)</li>
                    <li>عناوينك المحفوظة</li>
                    <li>سجل طلباتك</li>
                    <li>رسائل المحادثة</li>
                    <li>تقييماتك ومراجعاتك</li>
                    <li>سجل الإشعارات</li>
                    <li>سلة التسوق</li>
                </ul>

                <h2>البيانات التي قد يتم الاحتفاظ بها</h2>
                <div class="highlight">
                    <p>قد يتم الاحتفاظ بسجلات المعاملات المجهولة لمدة تصل إلى <strong>٩٠ يومًا</strong> بعد الحذف لأغراض قانونية وضريبية ومحاسبية، وفقًا للأنظمة التجارية في المملكة العربية السعودية.</p>
                </div>

                <div class="warning">
                    <p><strong>للفنيين:</strong> إذا كان لديك طلبات معلقة أو قيد التنفيذ، يجب إكمالها أو إلغاؤها قبل حذف حسابك.</p>
                </div>

                <h2>مدة المعالجة</h2>
                <p>يتم معالجة حذف الحساب خلال <strong>٣٠ يومًا</strong> من تاريخ طلبك. لن تتمكن من استعادة حسابك أو بياناتك بعد اكتمال الحذف.</p>
            </div>
        </div>

        <div class="footer">
            &copy; 2026 ProServe (Pump Now). All rights reserved.
        </div>
    </div>

    <script>
        function toggleLang() {
            const en = document.getElementById('en-content');
            const ar = document.getElementById('ar-content');
            if (en.style.display === 'none') {
                en.style.display = 'block';
                ar.style.display = 'none';
            } else {
                en.style.display = 'none';
                ar.style.display = 'block';
            }
        }
    </script>
</body>
</html>
```

### 3. Deploy

Upload the file to your Hostinger server and verify:

```bash
# SSH into server
cd ~/domains/pumpnow.app/public_html
php artisan route:clear
```

Then visit `https://pumpnow.app/delete-account` to confirm it loads.

### 4. Google Play Console

Enter this URL in the Data Safety form:

```
https://pumpnow.app/delete-account
```

---

## Checklist

- [x] Page refers to app name shown on store listing (ProServe / Pump Now)
- [x] Steps for users to request account deletion are prominently featured
- [x] Specifies which data types are deleted
- [x] Specifies which data types are retained and the retention period
- [x] Bilingual (English + Arabic) for Saudi Arabia users
- [x] Accessible without login
- [x] Mobile-responsive

---

**Last Updated**: March 20, 2026
