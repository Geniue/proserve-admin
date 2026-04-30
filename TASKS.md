# TASKS.md

## Architecture
- [ ] Treat Firebase Firestore as the source of truth for all mobile app data.
- [ ] Use PostgreSQL as the admin cache, reporting store, sync log store, and admin-only data store.
- [ ] Keep admin users, roles, and permissions out of Firebase.
- [ ] Ensure synced tables keep stable `firebase_id` or `firebase_uid` references.

## Firebase Setup
- [ ] Store Firebase service account credentials outside git.
- [ ] Configure Firebase environment variables before running sync commands.
- [ ] Verify credentials with `php artisan firebase:test-credentials`.
- [ ] Verify Firestore access with `php artisan firebase:test`.

## Sync Jobs
- [ ] Maintain full Firestore import through `ImportAllFirestoreData`.
- [ ] Maintain incremental Firestore sync through `SyncFirestoreChanges`.
- [ ] Schedule full import daily and incremental sync every 5-10 minutes.
- [ ] Log sync attempts, successes, and failures in Firestore sync log tables.
- [ ] Add collection-specific mapping when introducing a new Firestore-backed model.

## Admin CRUD
- [ ] Build Filament resources from PostgreSQL cache models.
- [ ] Sync admin create, update, and delete operations back to Firestore immediately.
- [ ] Add manual "force sync" actions for synced resources where useful.
- [ ] Show sync status and last sync time on admin screens that manage Firestore data.
- [ ] Preserve nullable fields and metadata for variable Firestore documents.

## App Data
- [ ] Manage app users as Firestore-backed records cached in PostgreSQL.
- [ ] Do not create Firebase Auth users from the admin panel unless explicitly required.
- [ ] Keep services, categories, bookings, banners, config, navigation, theme, and content synced.
- [ ] Store app configuration changes in Firestore so the Flutter app receives them.

## Monitoring
- [ ] Keep dashboard widgets for recent syncs, failed syncs, and last sync time.
- [ ] Make failed syncs easy to inspect and retry.
- [ ] Track performance, analytics, and admin activity in PostgreSQL.

## Local Commands
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

```bash
composer run dev
php artisan queue:work
php artisan schedule:work
php artisan firebase:import
```

## Validation Commands
```bash
composer test
php artisan test
./vendor/bin/pint --test
npm run build
php artisan firebase:test-sync
php artisan firebase:test-credentials
php artisan firebase:test
```

## Done When
- [ ] Requested behavior is implemented without unrelated application code changes.
- [ ] Relevant tests pass.
- [ ] Frontend assets build when views, CSS, or JS change.
- [ ] Firestore and PostgreSQL remain consistent after admin changes.
- [ ] Sync failures are logged and visible.
- [ ] No secrets, generated artifacts, or local environment files are added.
