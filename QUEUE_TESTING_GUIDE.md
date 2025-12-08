# دليل اختبار Queue - خطوة بخطوة

## 📋 المتطلبات قبل البدء

1. ✅ Queue Worker يجب أن يكون قيد التشغيل
2. ✅ قاعدة البيانات جاهزة (جداول `jobs` و `failed_jobs`)
3. ✅ `.env` يحتوي على `QUEUE_CONNECTION=database`

---

## 🚀 الخطوة 1: تشغيل Queue Worker

افتح **نافذة Terminal/PowerShell جديدة** وقم بتشغيل:

```bash
php artisan queue:work
```

**ملاحظة:** اترك هذه النافذة مفتوحة أثناء الاختبار!

سترى رسالة مثل:
```
[2025-12-07 11:30:00] Processing: App\Jobs\SendNotificationJob
[2025-12-07 11:30:01] Processed:  App\Jobs\SendNotificationJob
```

---

## 🧪 الخطوة 2: اختبار تحديث حالة الشكوى

### 2.1 إعداد Postman

**الطريقة:**
- `PUT`

**URL:**
```
http://localhost:8000/api/employee/complaints/{complaint_id}/status
```

**Headers:**
```
Authorization: Bearer {your_employee_token}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "status": 2
}
```

**قيم Status:**
- `2` = قيد المعالجة
- `3` = منجزة
- `4` = مرفوضة

### 2.2 إرسال الطلب

1. تأكد من أن لديك:
   - Token موظف (role = 2)
   - شكوى موجودة تنتمي لجهة الموظف
   - حالة الشكوى ليست نفس الحالة الجديدة

2. أرسل الطلب من Postman

3. **النتيجة المتوقعة:**
   - ✅ استجابة فورية (خلال 0.1-0.5 ثانية)
   - ✅ رسالة نجاح: "تم تحديث حالة الشكوى بنجاح"
   - ✅ لا انتظار لإرسال الإشعارات!

---

## 🔍 الخطوة 3: التحقق من Queue

### 3.1 التحقق من جدول `jobs`

افتح قاعدة البيانات أو استخدم Tinker:

```bash
php artisan tinker
```

ثم في Tinker:

```php
// عرض المهام في الانتظار
DB::table('jobs')->get();

// أو عد المهام
DB::table('jobs')->count();
```

**النتيجة المتوقعة:**
- بعد إرسال الطلب مباشرة: يجب أن ترى 2 Jobs (SendNotificationJob و SendFcmNotificationJob)
- بعد بضع ثواني: يجب أن تختفي المهام (تم تنفيذها)

### 3.2 مراقبة Queue Worker

في نافذة Queue Worker، يجب أن ترى:

```
[2025-12-07 11:30:05] Processing: App\Jobs\SendNotificationJob
[2025-12-07 11:30:05] Processed:  App\Jobs\SendNotificationJob
[2025-12-07 11:30:06] Processing: App\Jobs\SendFcmNotificationJob
[2025-12-07 11:30:07] Processed:  App\Jobs\SendFcmNotificationJob
```

---

## ✅ الخطوة 4: التحقق من النتائج

### 4.1 التحقق من الإشعارات في قاعدة البيانات

```sql
SELECT * FROM notifications 
WHERE user_id = {complaint_user_id} 
ORDER BY created_at DESC 
LIMIT 5;
```

**يجب أن ترى:**
- إشعار جديد بعنوان "تحديث حالة الشكوى"
- رسالة تحتوي على رقم الشكوى والحالة الجديدة

### 4.2 التحقق من السجلات (Logs)

```bash
tail -f storage/logs/laravel.log
```

**ابحث عن:**
- رسائل نجاح إرسال الإشعارات
- أي أخطاء في حالة الفشل

---

## 🧪 الخطوة 5: اختبار إضافة ملاحظة

### 5.1 إعداد Postman

**الطريقة:**
- `PUT`

**URL:**
```
http://localhost:8000/api/employee/complaints/{complaint_id}/note
```

**Headers:**
```
Authorization: Bearer {your_employee_token}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "note": "تمت مراجعة الشكوى وسيتم متابعتها"
}
```

### 5.2 إرسال الطلب والتحقق

نفس الخطوات السابقة:
1. أرسل الطلب
2. تحقق من Queue Worker
3. تحقق من جدول `jobs`
4. تحقق من الإشعارات

---

## 🐛 اختبار سيناريوهات الفشل

### اختبار 1: إيقاف Queue Worker

1. أوقف Queue Worker (Ctrl+C)
2. أرسل طلب تحديث حالة شكوى
3. تحقق من جدول `jobs` - يجب أن ترى المهام تتراكم
4. أعد تشغيل Queue Worker
5. راقب تنفيذ المهام المتراكمة

### اختبار 2: المهام الفاشلة

```bash
# عرض المهام الفاشلة
php artisan queue:failed

# عرض تفاصيل مهمة فاشلة
php artisan queue:failed {id}

# إعادة محاولة
php artisan queue:retry all
```

---

## 📊 أوامر مفيدة للاختبار

### عرض حالة Queue

```bash
# عرض المهام في الانتظار (من Tinker)
php artisan tinker
>>> DB::table('jobs')->count();

# عرض المهام الفاشلة
php artisan queue:failed

# مراقبة Queue
php artisan queue:monitor database:default
```

### مسح المهام

```bash
# مسح جميع المهام الفاشلة
php artisan queue:flush

# حذف مهمة محددة
php artisan queue:forget {id}
```

---

## ✅ قائمة التحقق النهائية

- [ ] Queue Worker يعمل
- [ ] طلب تحديث حالة الشكوى يعمل
- [ ] المهام تظهر في جدول `jobs`
- [ ] المهام تُنفذ من Queue Worker
- [ ] الإشعارات تُحفظ في قاعدة البيانات
- [ ] لا توجد أخطاء في السجلات
- [ ] الاستجابة سريعة (أقل من ثانية)

---

## 🎯 النتيجة المتوقعة

### قبل Queue:
- ⏱️ وقت الاستجابة: 3-5 ثواني
- ⚠️ المستخدم ينتظر إرسال الإشعارات

### بعد Queue:
- ⚡ وقت الاستجابة: 0.1-0.5 ثانية
- ✅ المستخدم يحصل على رد فوري
- ✅ الإشعارات تُرسل في الخلفية

---

## 🔧 استكشاف الأخطاء

### المشكلة: المهام لا تُنفذ

**الحل:**
1. تأكد من تشغيل Queue Worker
2. تحقق من `QUEUE_CONNECTION=database` في `.env`
3. أعد تحميل الإعدادات: `php artisan config:clear`

### المشكلة: المهام تتراكم ولا تُنفذ

**الحل:**
1. تحقق من وجود أخطاء في Queue Worker
2. راجع `storage/logs/laravel.log`
3. تحقق من `failed_jobs` table

### المشكلة: الإشعارات لا تُرسل

**الحل:**
1. تحقق من جدول `notifications` في قاعدة البيانات
2. راجع سجلات Laravel
3. تحقق من إعدادات FCM في `.env`

---

## 📝 ملاحظات

1. **في التطوير:** استخدم `php artisan queue:work` في نافذة منفصلة
2. **في الإنتاج:** استخدم Supervisor أو Systemd (راجع `QUEUE_SETUP.md`)
3. **لإعادة تحميل الكود:** أعد تشغيل Queue Worker بعد تحديث Jobs

---

## 🎉 تهانينا!

إذا نجحت جميع الاختبارات، فإن Queue يعمل بشكل صحيح! 🚀

