# دليل إعداد وتشغيل المشروع - خطوة بخطوة

## 📋 المتطلبات الأساسية

قبل البدء، تأكد من تثبيت:

1. **PHP 8.2 أو أحدث**
   ```bash
   php -v
   ```

2. **Composer** (مدير الحزم)
   ```bash
   composer --version
   ```

3. **MySQL/MariaDB** (قاعدة البيانات)
   ```bash
   mysql --version
   ```

4. **Node.js & NPM** (اختياري - للـ assets)
   ```bash
   node -v
   npm -v
   ```

---

## 🚀 خطوات الإعداد

### الخطوة 1: فك ضغط المشروع

1. فك ضغط الملف المضغوط في مجلد مناسب
2. افتح Terminal/PowerShell في مجلد المشروع

---

### الخطوة 2: تثبيت المتطلبات (Dependencies)

```bash
composer install
```

**ملاحظة:** قد يستغرق هذا الأمر بضع دقائق.

---

### الخطوة 3: إعداد ملف البيئة (.env)

#### 3.1 إنشاء ملف .env

```bash
# Windows
copy .env.example .env

# Linux/Mac
cp .env.example .env
```

#### 3.2 تعديل ملف .env

افتح ملف `.env` وعدّل الإعدادات التالية:

```env
APP_NAME="Complaints API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# إعدادات قاعدة البيانات
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=complaints_db
DB_USERNAME=root
DB_PASSWORD=your_password

# إعدادات الكاش
CACHE_DRIVER=file
QUEUE_CONNECTION=database

# إعدادات Firebase (إذا كنت تستخدم FCM)
FIREBASE_PROJECT_ID=your_project_id
FIREBASE_CREDENTIALS=path/to/credentials.json
```

**⚠️ مهم:** استبدل:
- `DB_DATABASE` - اسم قاعدة البيانات
- `DB_USERNAME` - اسم المستخدم
- `DB_PASSWORD` - كلمة المرور

---

### الخطوة 4: إنشاء مفتاح التطبيق

```bash
php artisan key:generate
```

---

### الخطوة 5: إنشاء قاعدة البيانات

1. افتح MySQL:
```bash
mysql -u root -p
```

2. أنشئ قاعدة البيانات:
```sql
CREATE DATABASE complaints_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

**ملاحظة:** استبدل `complaints_db` بالاسم الذي وضعته في `.env`

---

### الخطوة 6: تشغيل Migrations

```bash
php artisan migrate
```

هذا الأمر سينشئ جميع الجداول في قاعدة البيانات.

---

### الخطوة 7: (اختياري) إضافة بيانات تجريبية

```bash
php artisan db:seed
```

**ملاحظة:** تأكد من وجود Seeder في المشروع أولاً.

---

### الخطوة 8: إنشاء رابط التخزين (Storage Link)

```bash
php artisan storage:link
```

---

### الخطوة 9: مسح الكاش

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## 🎯 تشغيل السيرفر

### الطريقة 1: Laravel Development Server (موصى به للتطوير)

```bash
php artisan serve
```

**النتيجة:**
```
Starting Laravel development server: http://127.0.0.1:8000
```

**الوصول:** افتح المتصفح على `http://localhost:8000`

---

### الطريقة 2: استخدام XAMPP/WAMP

1. ضع المشروع في مجلد `htdocs` (XAMPP) أو `www` (WAMP)
2. افتح `http://localhost/complaints-api/public`

---

## ⚙️ تشغيل Queue Worker (مهم!)

**⚠️ مهم جداً:** يجب تشغيل Queue Worker في نافذة Terminal منفصلة!

افتح **نافذة Terminal جديدة** واكتب:

```bash
php artisan queue:work
```

**اترك هذه النافذة مفتوحة دائماً!**

بدون Queue Worker، الإشعارات لن تُرسل!

---

## 🧪 اختبار API

### 1. اختبار الاتصال الأساسي

افتح المتصفح على:
```
http://localhost:8000/api/agencies
```

**النتيجة المتوقعة:** قائمة الجهات (JSON)

---

### 2. اختبار من Postman

#### تسجيل الدخول كموظف:

**Method:** `POST`  
**URL:** `http://localhost:8000/api/admin/login`  
**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "user_name": "admin",
    "password": "password"
}
```

**النتيجة:** ستحصل على `token` - احفظه!

---

#### اختبار تحديث حالة شكوى:

**Method:** `PUT`  
**URL:** `http://localhost:8000/api/employee/complaints/{complaint_id}/status`  
**Headers:**
```
Authorization: Bearer {your_token}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "status": 2
}
```

---

## 📝 ملخص الأوامر السريعة

```bash
# 1. تثبيت المتطلبات
composer install

# 2. إنشاء .env
copy .env.example .env  # Windows
# أو
cp .env.example .env    # Linux/Mac

# 3. تعديل .env (قاعدة البيانات)

# 4. إنشاء مفتاح التطبيق
php artisan key:generate

# 5. إنشاء قاعدة البيانات (من MySQL)
CREATE DATABASE complaints_db;

# 6. تشغيل Migrations
php artisan migrate

# 7. مسح الكاش
php artisan config:clear
php artisan cache:clear

# 8. تشغيل السيرفر (نافذة 1)
php artisan serve

# 9. تشغيل Queue Worker (نافذة 2)
php artisan queue:work
```

---

## 🔍 استكشاف الأخطاء

### المشكلة: "Class not found"

**الحل:**
```bash
composer dump-autoload
```

---

### المشكلة: "No application encryption key"

**الحل:**
```bash
php artisan key:generate
```

---

### المشكلة: "SQLSTATE[HY000] [1045] Access denied"

**الحل:**
- تحقق من إعدادات قاعدة البيانات في `.env`
- تأكد من أن MySQL يعمل
- تحقق من اسم المستخدم وكلمة المرور

---

### المشكلة: "Table doesn't exist"

**الحل:**
```bash
php artisan migrate
```

---

### المشكلة: الإشعارات لا تُرسل

**الحل:**
- تأكد من تشغيل Queue Worker: `php artisan queue:work`
- تحقق من `QUEUE_CONNECTION=database` في `.env`

---

### المشكلة: "Storage link not found"

**الحل:**
```bash
php artisan storage:link
```

---

## 📊 التحقق من حالة المشروع

### 1. التحقق من الإعدادات

```bash
php artisan config:show
```

### 2. التحقق من Routes

```bash
php artisan route:list
```

### 3. التحقق من قاعدة البيانات

```bash
php artisan migrate:status
```

---

## 🎯 نافذتان Terminal مطلوبتان

### النافذة 1: Laravel Server
```bash
php artisan serve
```

### النافذة 2: Queue Worker
```bash
php artisan queue:work
```

**⚠️ كلاهما يجب أن يكونا قيد التشغيل!**

---

## 📚 ملفات مهمة

- `.env` - إعدادات المشروع
- `routes/api.php` - مسارات API
- `app/Http/Controllers/` - Controllers
- `database/migrations/` - Migrations

---

## ✅ قائمة التحقق النهائية

- [ ] PHP 8.2+ مثبت
- [ ] Composer مثبت
- [ ] MySQL يعمل
- [ ] قاعدة البيانات منشأة
- [ ] `.env` معدّل بشكل صحيح
- [ ] `composer install` تم بنجاح
- [ ] `php artisan key:generate` تم
- [ ] `php artisan migrate` تم بنجاح
- [ ] السيرفر يعمل (`php artisan serve`)
- [ ] Queue Worker يعمل (`php artisan queue:work`)
- [ ] API يعمل من Postman

---

## 🆘 الحصول على المساعدة

إذا واجهت مشاكل:

1. تحقق من ملف `storage/logs/laravel.log`
2. تأكد من أن جميع المتطلبات مثبتة
3. تحقق من إعدادات `.env`
4. تأكد من تشغيل Queue Worker

---

## 🎉 تهانينا!

إذا اكتملت جميع الخطوات بنجاح، المشروع جاهز للاستخدام! 🚀

**الوصول:** `http://localhost:8000`

