# دليل إعداد Railway - حل المشاكل

## 🔧 المشكلة 1: الإشعارات (Mail) لا تعمل

### الخطأ:
```
HttpTransportException: Unable to send an email: Forbidden (code 401)
```

### الحل السريع (للاختبار):

في Railway Dashboard → Variables → أضف:

```
MAIL_MAILER=log
```

هذا سيحفظ الإشعارات في ملفات Log بدلاً من إرسالها.

### الحل للإنتاج:

#### الخيار 1: استخدام SMTP (Gmail)
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Complaints System"
```

**ملاحظة:** للحصول على App Password من Gmail:
1. تفعيل 2-Step Verification
2. إنشاء App Password من: https://myaccount.google.com/apppasswords

#### الخيار 2: إصلاح Mailgun
```
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.mailgun.org
MAILGUN_SECRET=your-mailgun-secret-key
MAILGUN_ENDPOINT=api.mailgun.net
```

---

## 📁 المشكلة 2: رفع الملفات لا يعمل

### السبب:
- على Railway، التخزين المحلي غير دائم
- الملفات تُحذف عند إعادة النشر

### الحل:

#### الخيار 1: استخدام S3 (الأفضل)

1. إنشاء حساب AWS S3
2. إنشاء Bucket جديد
3. في Railway → Variables → أضف:

```
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
AWS_URL=https://your-bucket.s3.amazonaws.com
```

4. تثبيت حزمة S3:
```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

#### الخيار 2: استخدام Railway Volume

1. في Railway Dashboard → إضافة Volume
2. ربط Volume مع المشروع
3. تعديل `config/filesystems.php`:

```php
'local' => [
    'driver' => 'local',
    'root' => env('STORAGE_PATH', storage_path('app')),
],
```

4. في Railway Variables:
```
STORAGE_PATH=/data/storage
```

#### الخيار 3: استخدام Cloudinary (للصور فقط)

1. إنشاء حساب Cloudinary
2. في Railway Variables:
```
FILESYSTEM_DISK=cloudinary
CLOUDINARY_URL=cloudinary://api_key:api_secret@cloud_name
```

---

## ⚙️ إعدادات Railway المطلوبة

### 1. Environment Variables الأساسية:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.railway.app

# Database (من Railway Database)
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=your-password

# Queue
QUEUE_CONNECTION=database

# Cache
CACHE_DRIVER=redis
REDIS_HOST=your-redis-host
REDIS_PASSWORD=your-redis-password
REDIS_PORT=6379
```

### 2. Build Command:

في Railway → Settings → Build Command:
```bash
composer install --no-dev --optimize-autoloader && php artisan config:cache && php artisan route:cache && php artisan view:cache
```

### 3. Start Command:

```bash
php artisan migrate --force && php artisan storage:link && php artisan queue:work --tries=3
```

**أو** استخدام Supervisor (الأفضل):

إنشاء ملف `supervisor.conf`:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /app/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/app/storage/logs/worker.log
stopwaitsecs=3600
```

---

## 🔗 إنشاء Storage Link على Railway

أضف في Start Command:
```bash
php artisan storage:link
```

أو أضف في Build Command:
```bash
php artisan storage:link || true
```

---

## ✅ قائمة التحقق

- [ ] إعدادات Mail صحيحة
- [ ] إعدادات Filesystem (S3 أو Volume)
- [ ] Queue Worker يعمل
- [ ] Storage Link منشأ
- [ ] قاعدة البيانات متصلة
- [ ] Migrations تم تشغيلها
- [ ] Environment Variables كلها موجودة

---

## 🐛 حل المشاكل الشائعة

### المشكلة: "Storage link not found"
**الحل:**
```bash
php artisan storage:link
```

### المشكلة: "Queue jobs not processing"
**الحل:**
- تأكد من تشغيل `php artisan queue:work`
- تحقق من `QUEUE_CONNECTION=database`

### المشكلة: "Permission denied" عند رفع الملفات
**الحل:**
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### المشكلة: "Mail 401 error"
**الحل:**
- تحقق من Mailgun credentials
- أو استخدم `MAIL_MAILER=log` للاختبار

---

## 📝 ملاحظات مهمة

1. **Queue Worker**: يجب تشغيله دائماً على Railway
2. **Storage**: استخدم S3 أو Volume، لا تعتمد على local storage
3. **Logs**: راجع logs في Railway Dashboard
4. **Environment**: تأكد من أن جميع Variables موجودة

---

## 🚀 بعد التطبيق

1. أعد نشر المشروع على Railway
2. تحقق من Logs
3. اختبر رفع ملف
4. اختبر إرسال إشعار

