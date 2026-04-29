# PUMP Admin Dashboard
> A full-stack admin panel for a home-services mobile app in Saudi Arabia — bridging Firebase Firestore with a Laravel + Filament management interface for real-time content control, booking management, and operational monitoring.

## Meta
- **Client:** PUMP (Home Services App — Saudi Arabia)
- **Category:** Full-Stack Web (Admin Panel + REST API)
- **Timeline:** ~6 months — from initial architecture (Oct 2025) to production with 29 iterative releases (Apr 2026)
- **Slug:** pump-admin-dashboard

## The Problem
PUMP is a Flutter mobile app connecting customers with home-service providers in Saudi Arabia. The mobile app stores all data in Firebase Firestore, but there was no centralized admin interface for the operations team to manage services, users, bookings, promotions, theme configuration, or content — everything required manual Firestore Console edits or code deployments.

### Problem Details
- **No operational visibility:** The team had zero dashboard to monitor bookings, revenue, user growth, or sync status — all data lived only in Firestore with no reporting layer
- **Content changes required developer involvement:** Updating banners, onboarding screens, FAQ, legal pages, or app theme colors required a developer to manually edit Firestore documents
- **No promotional tooling:** There was no system to create, target, schedule, or track offers and promo codes for customers and providers
- **No OTP delivery infrastructure:** The Flutter app needed WhatsApp-based OTP delivery for phone verification, but there was no server-side proxy to securely hold API credentials away from the client

## The Solution
Built a Laravel 12 + Filament v4 admin panel backed by PostgreSQL that acts as a cache layer over Firebase Firestore. The panel provides full CRUD on all Firestore collections, background sync jobs to keep the local cache current, a comprehensive REST API for the Flutter app, and real-time monitoring pages powered by Firebase JS SDK. All admin changes propagate immediately to Firestore so the mobile app picks them up in real time.

### Solution Steps
1. **Architected a dual-database sync strategy** — PostgreSQL as a fast-query cache with `firebase_id`/`firebase_uid` columns on every table, background import jobs (`ImportAllFirestoreData`, `SyncFirestoreChanges`) pulling from Firestore via a custom REST client (`FirestoreRestClient`) using Google Service Account credentials and the Firestore REST API
2. **Built 17 Filament resource panels** — full CRUD for Users, Services, Service Categories, Service Providers, Bookings, Banners, Pages, FAQs, Onboarding Screens, App Config, Theme Configs, Theme Settings, Feature Flags, App Versions, Offers, Promo Codes, and Firestore Sync Logs — each with search, filtering, sorting, and Firestore sync status indicators
3. **Implemented a complete REST API (v1)** — 20+ endpoints for auth (register/login/logout), services, categories, banners, pages, FAQs, navigation, onboarding, app config, theme, version check, media uploads, and file management — secured with Laravel Sanctum and API key middleware
4. **Created a bilingual promotions engine** — Offers and Promo Codes with EN/AR content, audience targeting (customer/provider/specific users/cities), scheduling with date ranges, usage limits, discount types (percentage/fixed), first-order-only logic, and redemption tracking
5. **Built a real-time support chat system** — Filament page using Firebase JS SDK with `onSnapshot` listeners for live message streaming, agent assignment, and session management — styled with scoped CSS to avoid Filament's pre-compiled Tailwind conflicts
6. **Implemented WhatsApp OTP proxy API** — Server-side controller proxying OTP delivery via Meta WhatsApp Business API (Graph API v21.0), with template-based and plain-text message modes, API key authentication to keep credentials off the mobile client
7. **Built a real-time OTP monitoring dashboard** — Filament page with Firebase JS SDK showing live OTP request statuses, stats cards (pending/sent/expired), filter tabs, auto-expire logic, and sound notifications
8. **Implemented PDF receipt generation** — Booking receipts with mPDF supporting Arabic RTL text (`autoArabic`, `autoLangToFont`), downloadable PDFs, and email delivery via `BookingReceiptMail`
9. **Set up SEO and public-facing pages** — Sitemap XML generation, robots.txt with admin path blocking, `NoIndexAdminPages` middleware for X-Robots-Tag headers, noindex meta tags via Filament render hooks, and public pages for terms, privacy, about, and delete-account (bilingual EN/AR for Google Play compliance)
10. **Deployed on Hostinger VPS with Cloudflare CDN** — Queue workers for background sync, scheduled jobs for continuous Firestore imports, and production hardening (mPDF temp directory, JSON error responses for API, image upload size guidelines)

## Architecture Decisions

### PostgreSQL as a Cache Layer Over Firestore
- **Reasoning:** Firestore is the source of truth for the mobile app, but Firestore's query capabilities are limited for admin reporting (no JOINs, no aggregations across collections). PostgreSQL enables complex queries, full-text search, and fast table rendering in Filament. Evaluated using Firestore directly from Filament but rejected due to query latency and lack of relational join support.
- **Outcome:** Admin panel loads data instantly from PostgreSQL while every write propagates to Firestore via the `SyncToFirestore` trait, keeping mobile app data consistent

### Custom Firestore REST Client Instead of Kreait SDK
- **Reasoning:** The Kreait Firebase PHP SDK requires the gRPC PHP extension, which is notoriously difficult to install on shared/VPS hosting (Hostinger). Built a lightweight `FirestoreRestClient` using `google/auth` for service account credentials and Guzzle HTTP for Firestore REST API v1 calls — zero native extension dependencies.
- **Outcome:** Firestore read/write operations work on any PHP hosting without gRPC, reducing deployment friction to zero

### Filament v4 for Admin Panel
- **Reasoning:** Evaluated Nova, Backpack, and custom-built admin. Filament v4 provides a modern, Livewire-based UI with auto-generated CRUD from Eloquent models, built-in widgets, and a powerful form/table builder. v4 was chosen over v3 for its improved schema components architecture and better Alpine.js integration.
- **Outcome:** 17 resource panels with full CRUD, search, filters, bulk actions, and custom pages — built rapidly with consistent UX

### Firebase JS SDK for Real-Time Admin Pages
- **Reasoning:** For features requiring real-time updates (support chat, OTP monitoring), server-side polling would add latency and server load. Using Firebase JS SDK (compat v10.12.0) directly in Blade views with Alpine.js provides instant `onSnapshot` updates without any backend overhead.
- **Outcome:** Support chat and OTP dashboard update in real time with zero polling — immediate visibility into live operations

## Tech Stack
List: `PHP 8.4, Laravel 12, Filament v4, PostgreSQL, Firebase Firestore, Firebase JS SDK, Tailwind CSS 4, Vite 7, Alpine.js, Livewire, Sanctum, Spatie Media Library, mPDF, Intervention Image, Guzzle HTTP, Google Auth, Meta WhatsApp Business API, Cloudflare CDN`

- **PHP 8.4 with Laravel 12:** Application framework — API routing, Eloquent ORM, queue workers for background Firestore sync, scheduled jobs, Sanctum token auth, middleware pipeline (API key verification, rate limiting, CORS, noindex headers)
- **Filament v4.0:** Admin panel framework — 17 auto-discovered resource panels, 2 custom Livewire pages (support chat, OTP monitoring), 5 dashboard widgets (stats overview, revenue chart, popular services chart, promotion stats, Firestore sync status), form builder with image uploads and bilingual fields
- **PostgreSQL:** Relational cache database — 24 tables covering users, services, bookings, promotions, content, theming, RBAC, sync logs, and media. Designed with `firebase_id` foreign references on all synced tables, partial unique indexes, JSONB columns for flexible metadata
- **Firebase Firestore (REST API):** Primary data store for mobile app — custom `FirestoreRestClient` service class using Google Service Account auth via `google/auth` library, Guzzle HTTP client, document CRUD, collection listing, and incremental sync via timestamp cursors
- **Firebase JS SDK (v10.12.0 compat):** Browser-side real-time features — `onSnapshot` listeners for live support chat messages and OTP request monitoring, embedded in Blade views with Alpine.js state management
- **Tailwind CSS 4 + Vite 7:** Frontend build pipeline — Tailwind for public pages (terms, privacy, about, delete-account), Vite for asset compilation, scoped CSS for Filament custom pages
- **Spatie Laravel Media Library v11:** Polymorphic file attachments — avatar uploads, banner images, service icons with automatic conversions and responsive image generation
- **Intervention Image v1.5:** Server-side image processing — resize uploaded images to configurable max dimensions (default 1200×1200), quality optimization (default 80%)
- **mPDF v8.3:** PDF generation — booking receipts with Arabic RTL support (`autoArabic`, `autoLangToFont`, `autoScriptToLang`), A4 format, email attachment delivery
- **Meta WhatsApp Business API (Graph API v21.0):** OTP delivery — server-side proxy controller sending template-based or plain-text OTP messages to users' WhatsApp numbers, keeping API credentials secure on the backend
- **Laravel Sanctum v4.2:** API authentication — token-based auth for Flutter app, rate-limited login/register endpoints, protected user profile and media routes
- **Doctrine DBAL v4.3:** Database schema introspection — enabling column modifications and nullable alterations in migrations across PostgreSQL and MySQL compatibility

## Impact & Metrics
The admin panel gave the PUMP operations team full control over the mobile app's content, services, bookings, and promotions without requiring developer intervention — enabling the team to manage the app independently.

| Metric | Value | Description |
|--------|-------|-------------|
| Admin Resource Panels | 17 | Full CRUD panels for all app entities (users, services, bookings, offers, content, config, etc.) |
| REST API Endpoints | 20+ | Complete API powering the Flutter mobile app (auth, services, banners, config, media, OTP) |
| Database Tables | 24 | Comprehensive schema covering services, bookings, promotions, content, theming, RBAC, and sync tracking |
| Background Sync Jobs | 2 | Automated Firestore↔PostgreSQL sync (full import + incremental changes every 5 minutes) |
| Real-Time Pages | 2 | Live support chat and OTP monitoring with Firebase JS SDK `onSnapshot` — zero-latency updates |
| Git Commits to Production | 29 | Iterative delivery over 6 months with continuous feature additions and production fixes |
| Public SEO Pages | 5 | Terms, privacy, about, delete-account (bilingual EN/AR), sitemap.xml — Google Play compliance ready |
| PDF Receipt Generation | Bilingual | Arabic RTL + English booking receipts via mPDF with email delivery |
| Promotion System | Full | Offers + promo codes with bilingual content, audience targeting, scheduling, usage limits, and redemption tracking |
| API Response Time | TODO: get exact number | Average API response time from production logs |
