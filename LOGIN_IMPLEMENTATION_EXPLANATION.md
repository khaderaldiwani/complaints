# شرح تنفيذ نظام تسجيل الدخول (Login System)

## 1. المقدمة

تم تنفيذ نظام تسجيل دخول للمستخدمين الإداريين باستخدام Laravel Sanctum، حيث يسمح النظام للمديرين والموظفين بتسجيل الدخول باستخدام اسم المستخدم وكلمة المرور، ثم يحصلون على رمز وصول (Token) يُستخدم للمصادقة في الطلبات اللاحقة.

## 2. البنية الأساسية

### 2.1 تعريف المسار (Route)

في ملف `routes/api.php`، تم تعريف مسار تسجيل الدخول كالتالي:

```php
Route::post('admin/login', [AdminAuthController::class, 'login']);
```

- **المسار**: `POST /api/admin/login`
- **الوصول**: عام (لا يتطلب تسجيل دخول مسبق)
- **البيانات المطلوبة**: `user_name` و `password`

### 2.2 النموذج (Model)

يستخدم النظام نموذج `Administrative` الذي يحتوي على خاصية `HasApiTokens` من Laravel Sanctum:

```php
class Administrative extends Model
{
    use HasApiTokens;  // يمكّن النموذج من إنشاء وإدارة Tokens
    
    protected $table = 'administrative';
    protected $hidden = ['password'];  // إخفاء كلمة المرور من الاستجابات
}
```

## 3. خوارزمية تسجيل الدخول

### 3.1 الخطوات التنفيذية

تتبع دالة `login()` في `AdminAuthController` الخطوات التالية:

#### الخطوة 1: التحقق من البيانات (Validation)
```php
$data = $request->validate([
    'user_name' => 'required|string',
    'password' => 'required|string',
]);
```
- التأكد من إرسال اسم المستخدم وكلمة المرور
- في حال عدم توفر البيانات، يتم إرجاع خطأ برمز 422

#### الخطوة 2: البحث عن المستخدم
```php
$admin = Administrative::where('user_name', $data['user_name'])->first();
```
- البحث في جدول `administrative` عن مستخدم يطابق اسم المستخدم المرسل

#### الخطوة 3: التحقق من صحة كلمة المرور
```php
if (!$admin || !Hash::check($data['password'], $admin->password)) {
    return response()->json([
        'success' => false,
        'message' => 'اسم المستخدم أو كلمة المرور غير صحيحة.',
        'status_code' => 401
    ], 401);
}
```
- التحقق من وجود المستخدم وصحة كلمة المرور باستخدام `Hash::check()`
- يتم مقارنة كلمة المرور المشفرة المخزنة مع كلمة المرور المدخلة
- رسالة الخطأ عامة لحماية النظام من محاولات الاختراق

#### الخطوة 4: إنشاء رمز الوصول (Token)
```php
$token = $admin->createToken("admin_token")->plainTextToken;
```
- إنشاء Token فريد للمستخدم باستخدام Laravel Sanctum
- يتم تخزين Token مشفر في قاعدة البيانات
- يُرجع Token غير مشفر للعميل (يُعرض مرة واحدة فقط)

#### الخطوة 5: إرجاع الاستجابة
```php
return response()->json([
    'success' => true,
    'message' => 'تم تسجيل الدخول بنجاح.',
    'data' => [
        'admin' => [
            'id' => $admin->id,
            'name' => $admin->name,
            'user_name' => $admin->user_name,
            'role' => $admin->role,
            'id_agency' => $admin->id_agency,
        ],
        'token' => $token
    ],
    'status_code' => 200,
    'timestamp' => Carbon::now()->toIso8601String(),
], 200);
```

### 3.2 مثال على الاستخدام

**الطلب (Request):**
```http
POST /api/admin/login
Content-Type: application/json

{
    "user_name": "admin",
    "password": "password123"
}
```

**الاستجابة الناجحة (Success Response):**
```json
{
    "success": true,
    "message": "تم تسجيل الدخول بنجاح.",
    "data": {
        "admin": {
            "id": 1,
            "name": "أحمد محمد",
            "user_name": "admin",
            "role": 1,
            "id_agency": null
        },
        "token": "1|abcdef123456789..."
    },
    "status_code": 200,
    "timestamp": "2025-12-10T14:30:00+00:00"
}
```

**الاستجابة عند الفشل (Error Response):**
```json
{
    "success": false,
    "message": "اسم المستخدم أو كلمة المرور غير صحيحة.",
    "data": null,
    "status_code": 401,
    "timestamp": "2025-12-10T14:30:00+00:00"
}
```

## 4. آلية تخزين Token

### 4.1 هيكل الجدول

يتم تخزين Tokens في جدول `personal_access_tokens` الذي يحتوي على:

- `id`: معرف فريد للـ Token
- `tokenable_type`: نوع النموذج (مثل `App\Models\Administrative`)
- `tokenable_id`: معرف المستخدم
- `name`: اسم الـ Token (مثل `"admin_token"`)
- `token`: Token مشفر باستخدام SHA256 (64 حرف)
- `abilities`: الصلاحيات (JSON)
- `expires_at`: تاريخ انتهاء الصلاحية
- `last_used_at`: تاريخ آخر استخدام
- `created_at`, `updated_at`: تواريخ الإنشاء والتحديث

### 4.2 آلية التشفير

- **في قاعدة البيانات**: يتم تخزين Token مشفر باستخدام SHA256
- **للعميل**: يُرسل Token غير مشفر مرة واحدة فقط عند تسجيل الدخول
- **الأمان**: لا يمكن استرجاع Token الأصلي من النسخة المشفرة في قاعدة البيانات

## 5. استخدام Token للمصادقة

### 5.1 آلية التحقق

عند إرسال طلب محمي، يجب تضمين Token في Header:

```http
GET /api/admin/complaints
Authorization: Bearer 1|abcdef123456789...
```

يقوم Laravel Sanctum تلقائياً بـ:
1. قراءة Token من Header
2. فصله إلى ID و Token الفعلي
3. تشفير Token الفعلي والبحث في قاعدة البيانات
4. إذا وُجد: تحميل بيانات المستخدم وتحديده في الطلب
5. إذا لم يُوجد: إرجاع خطأ 401 Unauthorized

### 5.2 مثال على الاستخدام

```php
// في Controller محمي بـ auth:sanctum
Route::middleware('auth:sanctum')->get('/admin/complaints', function(Request $request) {
    $admin = $request->user(); // المستخدم محمّل تلقائياً
    return $admin->name;
});
```

## 6. المميزات الأمنية

1. **تشفير كلمات المرور**: جميع كلمات المرور مخزنة بشكل مشفر
2. **رسائل خطأ عامة**: لا تكشف للمهاجم أي من الحقول غير صحيح
3. **Token آمن**: Tokens مشفرة في قاعدة البيانات
4. **مصادقة قوية**: استخدام Hash::check() للمقارنة الآمنة
5. **إخفاء كلمة المرور**: لا تُرسل كلمة المرور في الاستجابات

## 7. الخلاصة

نظام تسجيل الدخول المطبق يتبع أفضل الممارسات في الأمان، حيث يستخدم:
- Laravel Sanctum للمصادقة
- تشفير آمن لكلمات المرور و Tokens
- آلية مصادقة واضحة ومنظمة
- استجابة منظمة ومهيكلة لجميع الحالات

يعمل النظام على توفير تجربة مستخدم آمنة وسهلة مع الحفاظ على أعلى معايير الأمان.

