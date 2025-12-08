# دليل إعداد وتشغيل Queue Worker

## ✅ ما تم إنجازه

1. ✅ إنشاء Jobs Classes:
   - `SendNotificationJob` - لإرسال الإشعارات
   - `SendFcmNotificationJob` - لإرسال إشعارات FCM

2. ✅ تعديل Controllers:
   - `ComplaintController` - استخدام Queue بدلاً من التنفيذ المباشر

3. ✅ إعداد قاعدة البيانات:
   - جدول `jobs` - لحفظ المهام في الانتظار
   - جدول `failed_jobs` - لحفظ المهام الفاشلة

4. ✅ تحديث `.env`:
   - `QUEUE_CONNECTION=database`

---

## 🚀 كيفية تشغيل Queue Worker

### للتطوير (Development)

```bash
php artisan queue:work
```

هذا الأمر سيعمل حتى تقوم بإيقافه يدوياً (Ctrl+C).

### للإنتاج (Production)

يجب تشغيل Queue Worker كخدمة خلفية باستخدام Supervisor أو Systemd.

#### خيار 1: استخدام Supervisor (موصى به)

1. تثبيت Supervisor:
```bash
sudo apt-get install supervisor  # Ubuntu/Debian
```

2. إنشاء ملف إعداد Supervisor:
```bash
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

3. إضافة التكوين التالي:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
stopwaitsecs=3600
```

4. تحديث Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

#### خيار 2: استخدام Systemd (Linux)

1. إنشاء ملف service:
```bash
sudo nano /etc/systemd/system/laravel-worker.service
```

2. إضافة التكوين التالي:
```ini
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /path/to/your/project/artisan queue:work database --sleep=3 --tries=3

[Install]
WantedBy=multi-user.target
```

3. تفعيل وتشغيل الخدمة:
```bash
sudo systemctl daemon-reload
sudo systemctl enable laravel-worker
sudo systemctl start laravel-worker
```

#### خيار 3: Windows (للتطوير)

استخدم Task Scheduler أو قم بتشغيل الأمر في نافذة PowerShell منفصلة:

```powershell
php artisan queue:work
```

---

## 📊 مراقبة Queue

### عرض المهام في الانتظار

```bash
php artisan queue:monitor database:default
```

### عرض المهام الفاشلة

```bash
php artisan queue:failed
```

### إعادة محاولة المهام الفاشلة

```bash
# إعادة محاولة جميع المهام الفاشلة
php artisan queue:retry all

# إعادة محاولة مهمة محددة
php artisan queue:retry {id}
```

### حذف المهام الفاشلة

```bash
# حذف مهمة محددة
php artisan queue:forget {id}

# حذف جميع المهام الفاشلة
php artisan queue:flush
```

---

## ⚙️ خيارات Queue:work

```bash
php artisan queue:work [connection] [options]
```

### الخيارات المهمة:

- `--queue=default` - تحديد اسم الـ queue
- `--tries=3` - عدد المحاولات قبل الفشل
- `--timeout=60` - الوقت الأقصى لتنفيذ Job (بالثواني)
- `--sleep=3` - وقت الانتظار بين المهام (بالثواني)
- `--max-jobs=1000` - عدد المهام قبل إعادة تشغيل Worker
- `--max-time=3600` - الوقت الأقصى للعمل قبل إعادة التشغيل (بالثواني)
- `--once` - تنفيذ مهمة واحدة فقط ثم الخروج

### مثال:

```bash
php artisan queue:work database --queue=default,notifications --tries=3 --timeout=60 --sleep=3
```

---

## 🔍 استكشاف الأخطاء

### المهام لا تعمل

1. تأكد من تشغيل Queue Worker:
```bash
php artisan queue:work
```

2. تحقق من إعدادات `.env`:
```env
QUEUE_CONNECTION=database
```

3. تحقق من الجداول في قاعدة البيانات:
```sql
SELECT * FROM jobs;
SELECT * FROM failed_jobs;
```

### المهام تفشل

1. عرض المهام الفاشلة:
```bash
php artisan queue:failed
```

2. عرض تفاصيل الخطأ:
```bash
php artisan queue:failed {id}
```

3. تحقق من ملفات السجلات:
```bash
tail -f storage/logs/laravel.log
```

---

## 📝 ملاحظات مهمة

1. **يجب تشغيل Queue Worker دائماً في الإنتاج** - بدون Worker، المهام لن تُنفذ!

2. **استخدم Supervisor أو Systemd** - لضمان إعادة تشغيل Worker تلقائياً في حالة التوقف

3. **راقب المهام الفاشلة** - راجع `failed_jobs` بانتظام

4. **في التطوير** - يمكنك استخدام `php artisan queue:listen` بدلاً من `queue:work` (أبطأ لكن يعيد تحميل الكود تلقائياً)

5. **لإعادة تحميل الكود** - في الإنتاج، أعد تشغيل Worker بعد تحديث الكود:
```bash
sudo supervisorctl restart laravel-worker:*
# أو
sudo systemctl restart laravel-worker
```

---

## 🎯 الخطوات التالية

1. ✅ تشغيل Queue Worker
2. ✅ اختبار إرسال إشعار
3. ✅ مراقبة المهام في جدول `jobs`
4. ✅ التحقق من نجاح الإرسال

---

## 📚 مراجع

- [Laravel Queue Documentation](https://laravel.com/docs/9.x/queues)
- [Supervisor Documentation](http://supervisord.org/)
- [Systemd Documentation](https://www.freedesktop.org/software/systemd/man/systemd.service.html)

