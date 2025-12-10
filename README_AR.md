# Complaints API - دليل سريع

## ⚡ الإعداد السريع

```bash
# 1. تثبيت المتطلبات
composer install

# 2. إنشاء ملف .env
copy .env.example .env

# 3. تعديل .env (قاعدة البيانات)

# 4. إنشاء مفتاح التطبيق
php artisan key:generate

# 5. إنشاء قاعدة البيانات في MySQL
CREATE DATABASE complaints_db;

# 6. تشغيل Migrations
php artisan migrate

# 7. مسح الكاش
php artisan config:clear
php artisan cache:clear
```

## 🚀 التشغيل

### نافذة 1: السيرفر
```bash
php artisan serve
```

### نافذة 2: Queue Worker (مهم!)
```bash
php artisan queue:work
```

## 🧪 اختبار API

**Base URL:** `http://localhost:8000/api`

**مثال:**
```
GET http://localhost:8000/api/agencies
```

## 📚 للمزيد

راجع `SETUP_GUIDE.md` للدليل الكامل.

