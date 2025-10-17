# Installing PHP gRPC Extension on Windows

## Your System Information
- **PHP Version**: 8.2.12 (ZTS - Zend Thread Safe)
- **Architecture**: x64 (64-bit)
- **Compiler**: Visual C++ 2019
- **PHP.ini Location**: `C:\xampp\php\php.ini`
- **Extensions Directory**: `C:\xampp\php\ext\`

## Installation Steps

### Step 1: Download gRPC Extension

You need to download the **Thread Safe (TS)** version for **PHP 8.2**, **x64**, **VS16 (Visual C++ 2019)**.

**Direct Link to PECL gRPC Downloads:**
👉 https://pecl.php.net/package/gRPC/1.68.0/windows

**What to download:**
- File: `php_grpc-1.68.0-8.2-ts-vs16-x64.zip`
- This matches your PHP configuration exactly

### Step 2: Extract and Install

1. **Download the ZIP file** from the link above
2. **Extract the ZIP** - you'll find `php_grpc.dll` inside
3. **Copy `php_grpc.dll`** to: `C:\xampp\php\ext\`

### Step 3: Enable Extension in php.ini

1. **Open php.ini**: `C:\xampp\php\php.ini` (use Notepad or any text editor)
2. **Find the extensions section** (search for `;extension=`)
3. **Add this line**:
   ```ini
   extension=grpc
   ```
   (Note: In PHP 8.2+, you don't need the `.dll` extension)

4. **Save the file**

### Step 4: Restart Services

Restart Apache/PHP-FPM to load the new extension:
```powershell
# If using XAMPP Control Panel:
# - Stop Apache
# - Start Apache

# Or from command line:
net stop Apache2.4
net start Apache2.4
```

### Step 5: Verify Installation

Run this command:
```powershell
php -m | Select-String grpc
```

If successful, you should see:
```
grpc
```

## Additional: Install Protobuf Extension (Optional but Recommended)

For better performance, also install the protobuf extension:

1. **Download**: https://pecl.php.net/package/protobuf/4.29.2/windows
   - File: `php_protobuf-4.29.2-8.2-ts-vs16-x64.zip`

2. **Extract and copy** `php_protobuf.dll` to `C:\xampp\php\ext\`

3. **Add to php.ini**:
   ```ini
   extension=protobuf
   ```

4. **Restart Apache** again

## Step 6: Enable Firebase Sync

Once gRPC is installed:

1. **Update .env**:
   ```env
   FIREBASE_SYNC_ENABLED=true
   ```

2. **Clear config cache**:
   ```powershell
   php artisan config:clear
   php artisan cache:clear
   ```

3. **Test Firebase connection**:
   ```powershell
   php artisan firebase:test-sync
   ```

## Troubleshooting

### Error: "The specified module could not be found"
- Make sure you downloaded the **TS (Thread Safe)** version, not NTS
- Verify the file is in `C:\xampp\php\ext\`
- Check that the DLL matches your PHP version (8.2)

### Error: "Unable to load dynamic library"
- Install [Visual C++ Redistributable](https://aka.ms/vs/17/release/vc_redist.x64.exe) (VS16 = Visual Studio 2019)
- Restart your computer after installing

### PHP doesn't recognize the extension
- Make sure you edited the correct php.ini file (check with `php --ini`)
- Verify the line is `extension=grpc` (no semicolon at the start)
- Restart Apache completely

## Quick Commands Reference

```powershell
# Check PHP version
php -v

# Check loaded extensions
php -m

# Find php.ini location
php --ini

# Test specific extension
php -m | Select-String grpc

# Clear Laravel caches
php artisan optimize:clear
```

## What Happens After Installation

Once gRPC is installed and enabled:

✅ Laravel will be able to boot without errors
✅ Firebase SDK will connect to Firestore
✅ Background sync jobs will work
✅ You can run: `php artisan firebase:test-sync`
✅ You can dispatch: `ImportAllFirestoreData::dispatch()`
✅ Admin panel will sync with Firebase in real-time

## Next Step After gRPC Installation

Download Firebase service account credentials:
1. Visit: https://console.firebase.google.com/project/proserve-95f34/settings/serviceaccounts/adminsdk
2. Click "Generate new private key"
3. Save as: `F:\work\copilotTut\proserve-admin\storage\firebase-credentials.json`
