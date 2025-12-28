# 🚀 Deploy RenzBilliard ke InfinityFree

Panduan lengkap deployment ke InfinityFree (100% gratis, no credit card).

---

## ✅ Keunggulan InfinityFree

- ✅ **100% FREE** selamanya
- ✅ **NO credit card** required
- ✅ PHP 8.1 support
- ✅ MySQL unlimited databases
- ✅ cPanel included
- ✅ SSL gratis (Let's Encrypt)

**Trade-offs:**
- ⚠️ Upload manual (no auto-deploy dari Git)
- ⚠️ Performance lebih lambat dari VPS
- ⚠️ Ada ads di domain gratis (bisa hilang dengan custom domain)

---

## Step 1: Sign Up InfinityFree

1. **Buka:** https://www.infinityfree.net/
2. Click **"Sign Up"**
3. Isi form:
   ```
   Email: your-email@gmail.com
   Password: [your password]
   ```
4. **Verify email** (instant!)
5. Login ke Client Area

---

## Step 2: Create Hosting Account

1. **Client Area** → Click **"Create Account"**
2. **Pilih domain options:**
   - **Opsi A:** Subdomain gratis (e.g., `renzbildb.gq`)
   - **Opsi B:** Custom domain (kalau punya)
3. **Server:** Auto-selected
4. Click **"Create Account"**
5. Wait 2-5 menit untuk activation
6. Login ke **Control Panel (cPanel)**

---

## Step 3: Setup MySQL Database

1. **cPanel** → **MySQL Databases**
2. **Create New Database:**
   ```
   Database Name: renzb (auto-prefix jadi: epiz_xxxxx_renzb)
   ```
3. **Create MySQL User:**
   ```
   Username: renzadmin
   Password: [strong password]
   ```
4. **Add User to Database:**
   - Select database: `epiz_xxxxx_renzb`
   - Select user: `epiz_xxxxx_renzadmin`
   - Grant **ALL PRIVILEGES**

5. **Save credentials:**
   ```
   DB_HOST: sql123.epizy.com (check di cPanel)
   DB_PORT: 3306
   DB_DATABASE: epiz_xxxxx_renzb
   DB_USERNAME: epiz_xxxxx_renzadmin
   DB_PASSWORD: [your password]
   ```

---

## Step 4: Prepare Files Locally

### 1. Build Production Assets

```bash
cd /home/ferdiodwi/Documents/RenzBilliard

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Clear caches
php artisan config:clear
php artisan cache:clear
```

### 2. Create Production .env

```bash
cp .env .env.production
nano .env.production
```

**Edit .env.production:**
```env
APP_NAME=RenzBilliard
APP_ENV=production
APP_KEY=  # Will generate later
APP_DEBUG=false
APP_URL=https://your-subdomain.epizy.com

LOG_CHANNEL=stack
LOG_LEVEL=error

# InfinityFree MySQL (ganti dengan credentials Anda)
DB_CONNECTION=mysql
DB_HOST=sql123.epizy.com
DB_PORT=3306
DB_DATABASE=epiz_xxxxx_renzb
DB_USERNAME=epiz_xxxxx_renzadmin
DB_PASSWORD=your_db_password

# Use database for session/cache (file driver tidak reliable)
BROADCAST_DRIVER=log
CACHE_DRIVER=database
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

### 3. Generate APP_KEY

```bash
# Generate key
php artisan key:generate

# Copy key dari .env.production dan simpan
```

### 4. Create ZIP Archive

```bash
# Exclude files yang tidak perlu
zip -r renzbilliard.zip . \
  -x "node_modules/*" \
  -x ".git/*" \
  -x "tests/*" \
  -x ".env" \
  -x "*.log" \
  -x "storage/logs/*"
```

---

## Step 5: Upload Files

### Option A: File Manager (Recommended)

1. **cPanel** → **File Manager**
2. Navigate to **`htdocs/`** (atau `public_html/`)
3. **Delete** default files (index.html, dll)
4. **Upload** `renzbilliard.zip`
5. **Extract** ZIP file
6. **Move** semua file dari folder extracted ke root `htdocs/`

**Struktur akhir di htdocs/:**
```
htdocs/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env.production
├── artisan
└── composer.json
```

### Option B: FTP (Alternative)

1. **Get FTP credentials** dari cPanel
2. Use FileZilla:
   ```
   Host: ftpupload.net (atau yang tertera)
   Username: epiz_xxxxx
   Password: [your password]
   Port: 21
   ```
3. Upload semua files ke `htdocs/`

---

## Step 6: Configure Web Root

**PENTING!** Laravel harus serve dari folder `public/`

1. **cPanel** → **Domains** → **Subdomains** (atau **Addon Domains**)
2. Find your domain
3. **Document Root:** Ubah ke:
   ```
   /htdocs/public
   ```
4. Save

---

## Step 7: Setup .env di Server

1. **File Manager** → Navigate to `/htdocs/`
2. **Rename** `.env.production` menjadi `.env`
3. **Edit** `.env` dan verify semua setting benar
4. **Permissions:** Set `.env` to **644** (read/write owner)

---

## Step 8: Set Directory Permissions

**Fix storage permissions:**

1. **File Manager** → Navigate to:
   ```
   /htdocs/storage/
   /htdocs/bootstrap/cache/
   ```

2. **Right-click** → **Change Permissions**
3. Set to **755** (rwxr-xr-x) atau **775**
4. Check **"Recurs into subdirectories"**

---

## Step 9: Setup Database Schema

### Option A: Using phpMyAdmin

1. **cPanel** → **phpMyAdmin**
2. Select database: `epiz_xxxxx_renzb`
3. **Import** tab
4. Upload `database.sql` (export dari local)

### Option B: Manual Tables

Kalau file SQL terlalu besar:

1. Export dari local per table
2. Import satu-satu ke phpMyAdmin

---

## Step 10: Run Artisan Commands

**Gunakan SSH (jika available) atau PHP:**

### Via PHP File (Workaround)

Create file `run-migrations.php` di `/htdocs/`:

```php
<?php
// Temporary file to run migrations
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Run migration
$status = $kernel->call('migrate', ['--force' => true]);
echo "Migrations: " . ($status === 0 ? "Success" : "Failed") . "\n";

// Create storage link
$status = $kernel->call('storage:link');
echo "Storage link: " . ($status === 0 ? "Success" : "Failed") . "\n";

// Cache config
$kernel->call('config:cache');
$kernel->call('route:cache');
echo "Cache created!\n";
```

**Run:**
1. Visit: `https://your-site.epizy.com/run-migrations.php`
2. Check output
3. **DELETE** file setelah selesai!

---

## Step 11: Test Application

Visit: **https://your-subdomain.epizy.com**

**Test:**
- [ ] Homepage loads
- [ ] Login works (`admin@renzbilliard.com` / `password`)
- [ ] Dashboard shows
- [ ] Tables page works
- [ ] Assets (CSS/JS) load correctly

---

## 🔧 Troubleshooting

### Error 500 - Internal Server Error

**Check:**
1. File permissions (storage/bootstrap/cache = 755)
2. `.env` exists and configured
3. APP_KEY generated
4. Debug mode:
   ```env
   APP_DEBUG=true
   ```

### Database Connection Error

**Verify:**
1. DB credentials di `.env` benar
2. Database exists di phpMyAdmin
3. User has privileges

### Assets Not Loading (404)

**Fix:**
1. Check `public/build/` folder exists
2. Run `npm run build` locally lagi
3. Re-upload `public/build/` folder
4. Clear browser cache

### Composer Dependencies Missing

**If need to run composer on server:**

InfinityFree tidak bisa run `composer install` direct. Harus:
1. Run `composer install` locally
2. Upload `vendor/` folder (besar!)
3. Atau use paid hosting yang support SSH

---

## 🎨 Custom Domain (Optional)

### Add Custom Domain

1. **cPanel** → **Addon Domains**
2. **New Domain Name:** `yourdomain.com`
3. **Document Root:** `/htdocs/public`
4. Create

### Update DNS

Di domain registrar Anda:
```
A Record: @ → 185.xxx.xxx.xxx (IP dari InfinityFree)
CNAME: www → your-subdomain.epizy.com
```

### Update .env

```env
APP_URL=https://yourdomain.com
```

Re-cache config via `run-migrations.php` atau manual.

---

## 🔄 Update Deployment

**Ketika ada perubahan code:**

1. **Build locally:**
   ```bash
   npm run build
   composer install --no-dev
   ```

2. **Upload changed files:**
   - Upload via File Manager/FTP
   - Hanya file yang berubah (partial update)

3. **Clear cache:**
   - Visit `run-migrations.php` (tambah cache clear)
   - Atau manual di cPanel

---

## ⚡ Performance Tips

### 1. Enable OPcache

InfinityFree sudah enable OPcache default. Check di:
```
cPanel → Select PHP Version → Check "opcache"
```

### 2. Optimize Images

Upload optimized images (compress dulu)

### 3. Use CDN (Optional)

- Cloudflare (gratis)
- Setup via cPanel → Cloudflare

### 4. Database Optimization

```sql
-- Run di phpMyAdmin
OPTIMIZE TABLE billing_sessions;
OPTIMIZE TABLE transactions;
```

---

## 💾 Backup Strategy

### Manual Backup

1. **cPanel** → **Backup** → **Download Full Backup**
2. Schedule: Weekly atau Monthly

### Database Backup

**phpMyAdmin** → **Export** → Save `.sql` file

**Automatic (via cron):**
- InfinityFree free tier tidak support cron
- Backup manual atau upgrade

---

## 📊 Monitoring

### Check Uptime

Free tools:
- UptimeRobot (https://uptimerobot.com)
- Ping every 5 minutes

### View Logs

**cPanel** → **Errors** → **Error Log**

Laravel logs:
- Download `storage/logs/laravel.log` via File Manager

---

## 💰 Cost

**TOTAL: $0/month** ✅

Forever free!

---

## 🎉 Done!

Aplikasi RenzBilliard Anda sudah live di InfinityFree!

**Next Steps:**
1. [ ] Test thoroughly
2. [ ] Setup custom domain (optional)
3. [ ] Configure backups
4. [ ] Monitor uptime
5. [ ] Share dengan team!

---

## 📞 Support

**InfinityFree:**
- Forum: https://forum.infinityfree.net
- Docs: https://forum.infinityfree.net/docs

**Laravel Issues:**
- Check storage/logs/laravel.log
- Enable APP_DEBUG=true untuk debugging
