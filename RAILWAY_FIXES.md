# حلول مشاكل Railway

## المشكلة 1: الإشعارات (Mail) لا تعمل

### السبب:
- خطأ 401 من Mailgun يعني أن الـ credentials غير صحيحة أو Mailgun غير مفعل

### الحل:

#### الخيار 1: استخدام Log Driver (للاختبار)
في Railway Environment Variables:
```
MAIL_MAILER=log
```

#### الخيار 2: إصلاح Mailgun
في Railway Environment Variables:
```
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.mailgun.org
MAILGUN_SECRET=your-mailgun-secret-key
MAILGUN_ENDPOINT=api.mailgun.net
```

#### الخيار 3: استخدام SMTP عادي (Gmail/Outlook)
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Your App Name"
```

---

## المشكلة 2: رفع الملفات لا يعمل

### السبب:
- على Railway، التخزين المحلي (`local` disk) غير دائم
- الملفات تُحذف عند إعادة النشر

### الحل:

#### الخيار 1: استخدام S3 (الأفضل للإنتاج)
1. إنشاء حساب AWS S3
2. إضافة في Railway Environment Variables:
```
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
AWS_URL=https://your-bucket.s3.amazonaws.com
```

#### الخيار 2: استخدام Railway Volume (تخزين دائم)
1. في Railway Dashboard → إضافة Volume
2. ربط Volume مع المشروع
3. تعديل الكود لاستخدام Volume path

#### الخيار 3: استخدام Cloud Storage آخر
- Cloudinary (للصور)
- DigitalOcean Spaces
- Backblaze B2

---

## خطوات التطبيق السريعة:

### للإشعارات (اختر واحد):
```bash
# في Railway → Variables → أضف:
MAIL_MAILER=log  # للاختبار فقط
```

### لرفع الملفات (اختر واحد):
```bash
# في Railway → Variables → أضف:
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket
```

---

## ملاحظات مهمة:

1. **Queue Worker**: تأكد من تشغيل `php artisan queue:work` على Railway
2. **Storage Link**: على Railway قد تحتاج لإنشاء symbolic link
3. **Permissions**: تأكد من صلاحيات الكتابة على مجلد storage

