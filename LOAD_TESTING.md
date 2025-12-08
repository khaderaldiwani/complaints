# دليل اختبار التحمل (Load Testing) - 100 مستخدم متزامن

## المتطلبات
- Queue Worker يعمل (`php artisan queue:work`)
- قاعدة البيانات مهيأة (الجداول + الفهارس الجديدة)
- توكن موظف صالح (role = 2) لتحديث حالة الشكوى
- قاعدة بيانات تحتوي على شكاوى مرتبطة بالموظف/الجهة

## سيناريو الاختبار المقترح
تحديث حالة شكاوى بالتوازي (الجزء الأثقل لأنه يطلق إشعارين عبر الـ Queue).

### أداة مقترحة: k6

1) تثبيت k6
- Windows: عبر Chocolatey `choco install k6`
- Linux: `sudo apt install k6` أو حسب التوزيعة

2) إنشاء ملف اختبار `k6-script.js`
```js
import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  vus: 100,           // 100 مستخدم متزامن
  duration: '60s',    // لمدة دقيقة
};

const BASE_URL = 'http://localhost:8000';
const TOKEN = '__REPLACE_WITH_EMPLOYEE_BEARER_TOKEN__';
const COMPLAINT_ID = '__REPLACE_WITH_COMPLAINT_ID__';

export default function () {
  const payload = JSON.stringify({ status: 2 });
  const headers = {
    Authorization: `Bearer ${TOKEN}`,
    'Content-Type': 'application/json',
    Accept: 'application/json',
  };

  const res = http.put(
    `${BASE_URL}/api/employee/complaints/${COMPLAINT_ID}/status`,
    payload,
    { headers }
  );

  check(res, {
    'status is 200': (r) => r.status === 200,
    'body has success': (r) => r.body && r.body.includes('success'),
  });

  sleep(1); // لتقليل الضغط قليلاً
}
```

3) تشغيل الاختبار
```bash
k6 run k6-script.js
```

4) ما يجب مراقبته
- وقت الاستجابة (p95) أقل من 1 ثانية
- نسبة الأخطاء (HTTP 5xx أو 4xx غير متوقعة) أقل من 1%
- جدول `jobs` يجب أن يفرغ بسرعة (Queue Worker يعمل)
- سجل الأخطاء `storage/logs/laravel.log` خالٍ من أخطاء جديدة
- جدول `failed_jobs` يجب أن يكون فارغاً

5) إذا ظهرت أخطاء
- تحقق من تشغيل Queue Worker
- راقب `php artisan queue:failed`
- تأكد من الفهارس (تشغيل `php artisan migrate` بعد إضافة الفهارس)
- راجع إعدادات قاعدة البيانات (max_connections / pool)

## ملاحظات
- يمكن تعديل `vus` و`duration` لسيناريوهات أخرى (مثل 50 مستخدم لـ 5 دقائق).
- يفضل تشغيل الاختبار على بيئة staging ببيانات حقيقية قدر الإمكان.

