# دليل الإعداد الكامل لـ Railway

## ✅ الخطوات المطلوبة

### 1. إعداد Environment Variables في Railway

1. اذهب إلى Railway Dashboard → مشروعك → Variables
2. انسخ القيم من ملف `RAILWAY_ENV_VARIABLES.txt`
3. **مهم**: استبدل `APP_URL` بـ URL المشروع الفعلي من Railway

**مثال:**
```
APP_URL=https://complaints-api-production.up.railway.app
```

### 2. إعداد Mail (Gmail)

#### الخطوة 1: إنشاء App Password من Gmail

1. اذهب إلى: https://myaccount.google.com/
2. Security → 2-Step Verification (يجب تفعيله أولاً)
3. App passwords → Select app: Mail → Select device: Other
4. انسخ الـ App Password (16 حرف)

#### الخطوة 2: إضافة في Railway Variables

```
MAIL_PASSWORD=your-16-character-app-password
```

**ملاحظة:** الـ App Password الحالي `pvlplhnymbfxwunr` قد يكون غير صحيح. أنشئ واحد جديد.

### 3. إعداد رفع الملفات

#### الخيار A: استخدام Local Storage (للاختبار)

**لا تحتاج أي إعدادات إضافية** - الكود يعمل تلقائياً.

**مشكلة:** الملفات قد تُحذف عند إعادة النشر على Railway.

#### الخيار B: استخدام S3 (للإنتاج - موصى به)

1. إنشاء حساب AWS S3
2. إنشاء Bucket جديد
3. إنشاء IAM User مع صلاحيات S3
4. في Railway Variables:

```
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
AWS_URL=https://your-bucket.s3.amazonaws.com
```

5. تثبيت حزمة S3:
```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

### 4. إعداد Queue Worker

#### في Railway Settings → Start Command:

```bash
php artisan migrate --force && php artisan storage:link && php artisan queue:work --tries=3 --timeout=90
```

**أو** استخدام Supervisor (الأفضل):

1. إنشاء ملف `Procfile` في جذر المشروع:
```
web: php artisan serve --host=0.0.0.0 --port=$PORT
worker: php artisan queue:work --tries=3 --timeout=90
```

2. في Railway → Settings → Add Service → Worker

### 5. إعداد Build Command

في Railway Settings → Build Command:

```bash
composer install --no-dev --optimize-autoloader && npm install --production && php artisan optimize && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan storage:link
```

### 6. التحقق من Storage Link

تأكد من أن `storage:link` يعمل. أضفه في Build Command أو Start Command.

---

## 🔍 التحقق من الإعدادات

### 1. تحقق من Mail

بعد النشر، اختبر إرسال إيميل:
- سجل حساب جديد
- تحقق من Logs في Railway Dashboard
- إذا كان `MAIL_MAILER=log`، ستجد الإيميلات في Logs

### 2. تحقق من رفع الملفات

- أضف شكوى مع ملف
- تحقق من الاستجابة
- إذا فشل، راجع Logs

### 3. تحقق من Queue

- تأكد من تشغيل Queue Worker
- راجع Logs للتأكد من معالجة Jobs

---

## 🐛 حل المشاكل الشائعة

### المشكلة: Mail لا يعمل (401 error)

**الحل:**
1. تحقق من App Password من Gmail
2. تأكد من تفعيل 2-Step Verification
3. جرب `MAIL_MAILER=log` للاختبار

### المشكلة: الملفات لا تُرفع

**الحل:**
1. تحقق من `storage:link` في Build/Start Command
2. تحقق من صلاحيات مجلد storage
3. راجع Logs للخطأ المحدد

### المشكلة: Queue لا يعمل

**الحل:**
1. تأكد من `QUEUE_CONNECTION=database`
2. تأكد من تشغيل `php artisan queue:work`
3. تحقق من جدول `jobs` في قاعدة البيانات

---

## 📝 قائمة التحقق النهائية

- [ ] جميع Environment Variables موجودة في Railway
- [ ] APP_URL صحيح (من Railway)
- [ ] MAIL_PASSWORD هو App Password صحيح
- [ ] Build Command يحتوي على `storage:link`
- [ ] Start Command يحتوي على `queue:work`
- [ ] قاعدة البيانات متصلة
- [ ] Migrations تم تشغيلها
- [ ] تم النشر بنجاح

---

## 🚀 بعد النشر

1. اختبر تسجيل حساب جديد
2. اختبر إضافة شكوى مع ملف
3. راجع Logs للتأكد من عدم وجود أخطاء
4. اختبر إرسال إشعارات

---

## 📞 إذا استمرت المشاكل

1. راجع Logs في Railway Dashboard
2. تحقق من جميع Environment Variables
3. تأكد من أن جميع الأوامر تعمل محلياً أولاً

