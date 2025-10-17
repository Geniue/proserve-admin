# 🔥 Final Step: Download Firebase Service Account Credentials

## ✅ Completed
- ✅ PHP gRPC extension installed and working
- ✅ Firebase sync enabled in .env
- ✅ All sync components tested and ready

## ⏳ What You Need to Do Now

### Step 1: Download Firebase Credentials

1. **Open Firebase Console**
   - 🔗 Click here: https://console.firebase.google.com/project/proserve-95f34/settings/serviceaccounts/adminsdk

2. **Generate Private Key**
   - Click the **"Generate new private key"** button
   - Click **"Generate key"** in the confirmation dialog
   - A JSON file will download (e.g., `proserve-95f34-firebase-adminsdk-xxxxx.json`)

3. **Save to Project**
   - Rename the file to: `firebase-credentials.json`
   - Move it to: `F:\work\copilotTut\proserve-admin\storage\firebase-credentials.json`

   PowerShell command (after downloading to Downloads folder):
   ```powershell
   Move-Item "$env:USERPROFILE\Downloads\proserve-95f34-firebase-adminsdk-*.json" "F:\work\copilotTut\proserve-admin\storage\firebase-credentials.json"
   ```

### Step 2: Verify Credentials File

The file should look like this (with your actual values):
```json
{
  "type": "service_account",
  "project_id": "proserve-95f34",
  "private_key_id": "...",
  "private_key": "-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n",
  "client_email": "firebase-adminsdk-xxxxx@proserve-95f34.iam.gserviceaccount.com",
  "client_id": "...",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "..."
}
```

### Step 3: Test Firebase Connection

```powershell
php artisan tinker
```

Then in Tinker:
```php
$firestore = app(\Kreait\Firebase\Contract\Firestore::class);
$db = $firestore->database();
echo "✅ Connected to Firebase Firestore!\n";

// Try listing collections
$collections = $db->listCollections();
foreach ($collections as $collection) {
    echo "Collection: " . $collection->id() . "\n";
}
```

Type `exit` to quit Tinker.

### Step 4: Import Existing Data from Firestore

```powershell
php artisan tinker
```

```php
// Dispatch the import job
\App\Jobs\ImportAllFirestoreData::dispatch();

// Or run it synchronously (for testing)
$job = new \App\Jobs\ImportAllFirestoreData();
$job->handle();
```

### Step 5: Start Queue Workers (for ongoing sync)

```powershell
# Terminal 1: Queue worker
php artisan queue:work --queue=default

# Terminal 2: Scheduler (in another terminal)
php artisan schedule:work
```

## 🎯 What Happens After Setup

Once credentials are in place:

1. **Admin Panel Changes → Firestore**
   - Any create/update/delete in Filament admin
   - Automatically syncs to Firestore
   - Logged in `firestore_sync_logs` table

2. **Firestore Changes → Admin Panel**
   - Background jobs run every 5-10 minutes
   - Pull latest changes from Firestore
   - Update PostgreSQL cache
   - Visible in admin panel

3. **Dashboard Widget**
   - Shows sync statistics
   - Recent syncs (last hour)
   - Failed syncs (last 24 hours)
   - Last sync timestamp

## 🧪 Test the Full Sync Flow

### Test 1: Create a Service in Admin Panel
1. Go to: http://127.0.0.1:8000/admin/services/create
2. Create a new service
3. Check Firestore console to see if it appears

### Test 2: Create Data in Firestore (simulate mobile app)
1. Add a document directly in Firebase Console
2. Wait 5 minutes or run sync job manually:
   ```powershell
   php artisan tinker
   \App\Jobs\SyncFirestoreChanges::dispatch('services');
   ```
3. Check admin panel to see if it appears

### Test 3: Check Sync Logs
```powershell
php artisan tinker
```

```php
// See recent syncs
\App\Models\FirestoreSyncLog::latest('attempted_at')->limit(10)->get(['collection', 'action', 'status', 'attempted_at']);

// See failed syncs
\App\Models\FirestoreSyncLog::where('status', 'failed')->get();

// See sync status per collection
\App\Models\FirestoreSyncStatus::all(['collection', 'last_sync_at', 'total_documents', 'status']);
```

## 🚨 Troubleshooting

### Error: "Unable to read file firebase-credentials.json"
- Check file exists: `Test-Path "F:\work\copilotTut\proserve-admin\storage\firebase-credentials.json"`
- Check file is valid JSON
- Check file permissions

### Error: "Permission denied" or "Insufficient permissions"
- Verify service account has Firestore access in Firebase Console
- Go to: IAM & Admin → Service Accounts
- Ensure account has "Firebase Admin SDK Administrator Service Agent" role

### No data syncing
- Check `FIREBASE_SYNC_ENABLED=true` in .env
- Run: `php artisan config:clear`
- Start queue worker: `php artisan queue:work`
- Check logs: `Get-Content storage/logs/laravel.log -Tail 50`

## 📊 Monitoring

View sync statistics in admin panel:
- Dashboard: http://127.0.0.1:8000/admin
- Widget shows: Recent syncs, failed syncs, last sync time

Check database directly:
```sql
-- Recent sync operations
SELECT * FROM firestore_sync_logs ORDER BY attempted_at DESC LIMIT 20;

-- Failed syncs
SELECT * FROM firestore_sync_logs WHERE status = 'failed';

-- Sync status per collection
SELECT * FROM firestore_sync_status;
```

## 🎊 You're Done!

Once you complete these steps:
- ✅ Full bidirectional sync is active
- ✅ Admin panel ↔ Firestore in real-time
- ✅ Mobile app data visible in admin
- ✅ Admin changes push to mobile app
- ✅ Automatic background sync every 5-10 minutes

**Next step**: Download the credentials file and test the connection! 🚀
