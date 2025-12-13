# 🚀 دليل سريع لإعداد Railway

## ⚡ الخطوات السريعة (5 دقائق)

### 1️⃣ نسخ Environment Variables

افتح ملف `RAILWAY_ENV_VARIABLES.txt` وانسخ جميع القيم إلى:
**Railway Dashboard → مشروعك → Variables**

**⚠️ مهم جداً:**
- استبدل `APP_URL` بـ URL المشروع الفعلي من Railway
- مثال: `APP_URL=https://complaints-api-production.up.railway.app`

### 2️⃣ إصلاح Mail Password

1. اذهب إلى: https://myaccount.google.com/apppasswords
2. أنشئ App Password جديد (16 حرف)
3. في Railway Variables، استبدل:
   ```
   MAIL_PASSWORD=your-new-16-character-password
   ```

### 3️⃣ إعداد Build & Start Commands

في Railway → Settings:

**Build Command:**
```bash
composer install --no-dev --optimize-autoloader && npm install --production && php artisan optimize && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan storage:link
```

**Start Command:**
```bash
php artisan migrate --force && php artisan queue:work --tries=3 --timeout=90
```

### 4️⃣ إضافة Queue Worker Service

1. في Railway Dashboard → Add Service → Worker
2. استخدم نفس Start Command:
   ```bash
   php artisan queue:work --tries=3 --timeout=90
   ```

---

## ✅ التحقق السريع

بعد النشر:

1. ✅ اختبر تسجيل حساب جديد → يجب أن يرسل OTP
2. ✅ اختبر إضافة شكوى مع ملف → يجب أن يُرفع الملف
3. ✅ راجع Logs في Railway → لا يجب أن يكون هناك أخطاء

---

## 🔧 إذا لم يعمل شيء

### Mail لا يعمل؟
```bash
# في Railway Variables:
MAIL_MAILER=log  # للاختبار
```

### الملفات لا تُرفع؟
- تحقق من Logs في Railway
- تأكد من `storage:link` في Build Command

### Queue لا يعمل؟
- تأكد من إضافة Worker Service
- تحقق من `QUEUE_CONNECTION=database`

---

## 📞 للمزيد من التفاصيل

راجع ملف `RAILWAY_SETUP_COMPLETE.md`

