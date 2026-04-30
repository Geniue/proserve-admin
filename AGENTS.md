# AGENTS.md

## Project Structure
- `app/`: Laravel application code, including models, controllers, middleware, Filament resources/pages/widgets, jobs, services, and traits.
- `app/Services/FirestoreRestClient.php`: Firestore REST integration. Prefer this over adding new Firebase transports.
- `app/Traits/SyncToFirestore.php`: model hook for pushing admin changes back to Firestore.
- `app/Jobs/ImportAllFirestoreData.php` and `app/Jobs/SyncFirestoreChanges.php`: Firestore-to-PostgreSQL sync jobs.
- `routes/web.php`, `routes/api.php`, `routes/console.php`: public routes, API routes, and scheduled sync tasks.
- `resources/views`, `resources/css`, `resources/js`: Blade views and frontend assets.
- `database/migrations`, `database/factories`, `database/seeders`: schema and test data.
- `tests/Unit`, `tests/Feature`: PHPUnit test suites.
- `config/firebase.php` and `.env*`: Firebase and environment configuration.

## Coding Rules
- Firestore is the source of truth for mobile app data; PostgreSQL is a cache plus admin-only storage.
- Admin users, roles, and permissions stay PostgreSQL-only and must not sync to Firestore.
- Synced records need stable `firebase_id` or `firebase_uid` references.
- Models that sync to Firestore should follow the existing `SyncToFirestore` contract: `getFirestoreCollection()`, `getFirestoreDocumentId()`, and `toFirestoreArray()`.
- Admin CRUD that changes mobile app data must update Firestore immediately and record sync failures.
- Keep migrations tolerant of Firestore data variability: nullable fields and JSON metadata are often intentional.
- Follow existing Laravel 12, Filament v4, Livewire, Tailwind, and Vite patterns.
- Keep changes scoped. Do not refactor unrelated resources, schemas, or views.
- Never commit secrets. Firebase credentials belong in `storage/firebase-credentials.json` or environment variables.

## Setup Commands
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

Use the bundled setup script when appropriate:

```bash
composer run setup
```

For local development:

```bash
composer run dev
```

For queues and scheduled sync jobs:

```bash
php artisan queue:work
php artisan schedule:work
php artisan firebase:import
```

## Testing Commands
```bash
composer test
php artisan test
./vendor/bin/pint --test
npm run build
```

When changing Firebase or sync behavior, also run the relevant checks:

```bash
php artisan firebase:test-sync
php artisan firebase:test-credentials
php artisan firebase:test
```

## Done When
- The requested behavior is implemented with no unrelated application changes.
- Tests relevant to the change pass.
- `npm run build` passes when frontend assets or Blade/CSS/JS are changed.
- Firestore sync changes preserve source-of-truth rules and log failures.
- No credentials, generated artifacts, or local environment files are added.
- New or changed commands, routes, jobs, or resources are documented when needed.
