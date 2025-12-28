# 🚀 Deploy RenzBilliard ke Render.com

Panduan step-by-step deployment dengan MySQL external (filess.io).

---

## ✅ Prerequisites (Yang Sudah Ada)

- [x] MySQL hosting dari filess.io
- [x] GitHub account
- [ ] Render.com account (gratis, tanpa CC)

---

## Step 1: Push Code ke GitHub

```bash
cd /home/ferdiodwi/Documents/RenzBilliard

# Add semua file deployment
git add .

# Commit
git commit -m "Add Render deployment configuration"

# Push ke GitHub
git push origin main
```

**Pastikan semua file ini ter-commit:**
- ✅ `render.yaml`
- ✅ `Dockerfile`
- ✅ `build.sh`
- ✅ `.env.render` (template)

---

## Step 2: Buat Akun Render

1. Buka: https://render.com
2. Click **"Get Started"**
3. Pilih **"Sign up with GitHub"**
4. Authorize Render untuk akses GitHub repos
5. ✅ **Tidak perlu credit card!**

---

## Step 3: Create Web Service

1. **Di Render Dashboard:**
   - Click **"New +"** → **"Web Service"**

2. **Connect Repository:**
   - Pilih repository: **`renz-billiard`**
   - Click **"Connect"**

3. **Configure Service:**
   ```
   Name: renz-billiard
   Region: Singapore (atau terdekat)
   Branch: main
   Runtime: Docker
   ```

4. **Instance Type:**
   - Pilih: **Free** (0$/month)

5. **Jangan deploy dulu!** Click **"Advanced"**

---

## Step 4: Set Environment Variables

Di halaman **Environment**, tambahkan variables ini:

### Copy-paste semua ini:

```env
APP_NAME=RenzBilliard
APP_ENV=production
APP_DEBUG=false
APP_URL=https://renz-billiard.onrender.com

DB_CONNECTION=mysql
DB_HOST=0yud01.h.filess.io
DB_PORT=3307
DB_DATABASE=renzb1_recentfast
DB_USERNAME=renzb1_recentfast
DB_PASSWORD=3818d40a72fbff40facc5eea2a36b3ccad1

CACHE_DRIVER=database
SESSION_DRIVER=database
SESSION_LIFETIME=120
QUEUE_CONNECTION=sync

LOG_CHANNEL=stack
LOG_LEVEL=debug
```

**Cara input:**
- Click **"Add Environment Variable"**
- Key: `APP_NAME`, Value: `RenzBilliard`
- Ulangi untuk setiap variable di atas

**PENTING:**
- ⚠️ `APP_KEY` akan auto-generate oleh Render, **skip yang ini!**
- ✅ Copy-paste credentials MySQL dari filess.io dengan benar

---

## Step 5: Deploy!

1. Click **"Create Web Service"**
2. Render akan mulai build (5-10 menit pertama kali)
3. Monitor di tab **"Logs"**

**Build process:**
```
📦 Installing Composer dependencies...
📦 Installing npm dependencies...
🔨 Building Vue.js frontend...
🗄️ Running database migrations...
✅ Build completed!
🚀 Starting server...
```

---

## Step 6: Import Database Schema

Karena MySQL-nya external, perlu import schema manual:

### **Option A: Via phpMyAdmin filess.io**

1. Login ke phpMyAdmin filess.io
2. Select database: `renzb1_recentfast`
3. Tab **"Import"**
4. Upload file SQL (atau copy-paste)

### **Option B: Export dari local, import ke filess**

**Export dari local:**
```bash
cd /home/ferdiodwi/Documents/RenzBilliard

# Export database structure + data
mysqldump -u root -p renz_billiard > database_export.sql
```

**Import ke filess.io:**
- Login phpMyAdmin filess.io
- Import `database_export.sql`

### **Option C: Run migration dari Render Shell**

Setelah deploy selesai:
1. Render Dashboard → **"Shell"** tab
2. Run:
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

---

## Step 7: Update APP_URL

Setelah deployment selesai:

1. Render akan kasih URL: `https://renz-billiard.onrender.com`
2. Copy URL tersebut
3. **Environment** tab → Edit `APP_URL`
4. Paste URL yang benar
5. Click **"Save Changes"**
6. Render akan auto-redeploy (2-3 menit)

---

## Step 8: Test Application

Visit: **https://renz-billiard.onrender.com**

**Login dengan:**
```
Email: admin@renzbilliard.com
Password: password
```

**Test checklist:**
- [ ] Homepage loads
- [ ] Login works
- [ ] Dashboard shows
- [ ] Tables page works
- [ ] Can start session
- [ ] POS system works
- [ ] Mobile responsive

---

## 🔧 Troubleshooting

### Build Failed

**Check logs di "Logs" tab**

Common issues:
- `composer install failed` → Check composer.json
- `npm build failed` → Check package.json
- Database connection → Check MySQL credentials

### Database Connection Error

```
SQLSTATE[HY000] [2002] Connection refused
```

**Solutions:**
1. ✅ Verify MySQL credentials benar
2. ✅ Check firewall rules di filess.io
3. ✅ Pastikan port `3307` (bukan 3306!)
4. ✅ Test connection dari local dulu:
   ```bash
   mysql -h 0yud01.h.filess.io -P 3307 -u renzb1_recentfast -p
   ```

### Assets Not Loading

**CSS/JS files not found:**

```bash
# Di Render Shell
php artisan storage:link
npm run build
php artisan config:clear
```

### Cold Start (30 detik)

**Normal di Render free tier!**
- Server sleep setelah 15 menit idle
- First request setelah sleep: ~30 detik
- Subsequent requests: Cepat

**Solusi:**
- Keep-alive ping service (optional)
- Atau upgrade ke paid tier ($7/mo)

---

## 🎯 Next Steps

### Enable Auto-Deploy

**Settings → Build & Deploy:**
- Toggle **"Auto-Deploy"** → **YES**

Sekarang setiap `git push` akan auto-deploy! 🚀

```bash
# Make changes
git add .
git commit -m "Update feature"
git push

# Render auto-builds dan deploys!
```

### Custom Domain (Optional)

**Settings → Custom Domains:**
1. Add custom domain
2. Update DNS records
3. SSL auto-renew

---

## 💡 Tips

**Optimize Build Time:**
```bash
# Di build.sh, comment out seeding jika tidak perlu:
# php artisan db:seed --force
```

**Clear Cache:**
```bash
# Di Render Shell
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

**View Logs:**
- Real-time: Render Dashboard → "Logs" tab
- Laravel logs: Render Shell → `tail -f storage/logs/laravel.log`

---

## 🎉 Done!

Aplikasi RenzBilliard Anda sekarang live di:
**https://renz-billiard.onrender.com**

**100% GRATIS:**
- ✅ Render Web Service: Free
- ✅ MySQL filess.io: Free
- ⚠️ Server sleep after 15 min idle (free tier limitation)

---

## 📞 Need Help?

**Render Issues:**
- Docs: https://render.com/docs
- Community: https://community.render.com

**filess.io MySQL:**
- Dashboard: https://filess.io

**Laravel Errors:**
- Check logs in Render dashboard
- Use Render Shell for debugging
