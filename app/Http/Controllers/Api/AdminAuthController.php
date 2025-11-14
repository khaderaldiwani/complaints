<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpCodeMail;
use App\Models\Administrative;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use League\Config\Exception\ValidationException;

class AdminAuthController extends Controller
{


// public function register(Request $request)
// {
//     try {
//         // 1️⃣ التحقق من صلاحية المدير
//         $currentUser = $request->user();
        
//         if (!$currentUser || $currentUser->role != 1) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'غير مصرح لك بتنفيذ هذه العملية',
//                 'status_code' => 403,
//                 'timestamp' => now()->toIso8601String()
//             ], 403);
//         }

//         // 2️⃣ التحقق من صحة البيانات
//         $data = $request->validate([
//             'name' => 'required|string|max:255',
//             'user_name' => 'required|string|max:255',       
//             'password' => 'required|string|min:6|confirmed',
//             'role' => 'required|integer|in:1,2', // 1 = admin, 2 = employee
//             'id_agency' => 'sometimes|exists:agencies,id'
//         ]);

//         // 3️⃣ التحقق اليدوي من اسم المستخدم
//         $exists = Administrative::where('user_name', $data['user_name'])->exists();
//         if ($exists) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'اسم المستخدم مستخدم مسبقًا',
//                 'status_code' => 409,
//                 'timestamp' => now()->toIso8601String()
//             ], 409);
//         }

//         // 4️⃣ إنشاء المستخدم
//         $user = Administrative::create([
//             'name' => $data['name'],
//             'user_name' => $data['user_name'],
//             'password' => Hash::make($data['password']),
//             'role' => $data['role'], // سيتم حفظه كـ 1 أو 2
//             'id_agency' => $data['id_agency'] ?? 1,            
//         ]);

//         // 5️⃣ الرد الناجح مع تحويل الرقم إلى نص للقراءة
//         $roleText = $this->getRoleText($data['role']);
        
//         return response()->json([
//             'success' => true,
//             'message' => 'تم إنشاء المستخدم بنجاح',
//             'data' => [
//                 'id' => $user->id,
//                 'name' => $user->name,
//                 'user_name' => $user->user_name,
//                 'role' => $roleText, // إرجاع النص للقراءة
//                 'role_id' => $user->role, // إرجاع الرقم أيضاً إذا needed
//                 'agency_id' => $user->id_agency
//             ],
//             'status_code' => 201,
//             'timestamp' => now()->toIso8601String(),
//         ], 201);

//     } catch (ValidationException $e) {
//         return response()->json([
//             'success' => false,
//             'message' => 'خطأ في البيانات المدخلة',
//             'errors' => null,
//             'status_code' => 422,
//             'timestamp' => now()->toIso8601String()
//         ], 422);
//     } catch (\Exception $e) {
//         return response()->json([
//             'success' => false,
//             'message' => 'حدث خطأ في الخادم: ' . $e->getMessage(),
//             'status_code' => 500,
//             'timestamp' => now()->toIso8601String()
//         ], 500);
//     }
// }
public function register(Request $request)
{
    try {
        // 1️⃣ التحقق من صلاحية المدير
        $currentUser = $request->user();
        
        if (!$currentUser || $currentUser->role != 1) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتنفيذ هذه العملية',
                'status_code' => 403,
                'timestamp' => now()->toIso8601String()
            ], 403);
        }

        // 2️⃣ التحقق من صحة البيانات
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'user_name' => 'required|string|max:255',       
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|integer|in:1,2',
            'id_agency' => 'nullable|exists:agencies,id'
        ]);

        // 3️⃣ التحقق اليدوي من اسم المستخدم
        $exists = Administrative::where('user_name', $data['user_name'])->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'اسم المستخدم مستخدم مسبقًا',
                'status_code' => 409,
                'timestamp' => now()->toIso8601String()
            ], 409);
        }

        // 4️⃣ إنشاء المستخدم - استخدم isset للتحقق من وجود id_agency
        $userData = [
            'name' => $data['name'],
            'user_name' => $data['user_name'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ];

        // أضف id_agency فقط إذا كانت موجودة وليست null
        if (isset($data['id_agency']) && $data['id_agency'] !== null) {
            $userData['id_agency'] = $data['id_agency'];
        }

        $user = Administrative::create($userData);

        // 5️⃣ الرد الناجح
        $roles = [
            1 => 'مدير نظام',
            2 => 'موظف'
        ];
        $roleText = $roles[$data['role']] ?? 'غير معروف';
        
        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء المستخدم بنجاح',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'user_name' => $user->user_name,
                'role' => $roleText,
                'role_id' => $user->role,
                'agency_id' => $user->id_agency
            ],
            'status_code' => 201,
            'timestamp' => now()->toIso8601String(),
        ], 201);

    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'خطأ في البيانات المدخلة',
            'errors' => null, // أعد التفاصيل لمعرفة الأخطاء
            'status_code' => 422,
            'timestamp' => now()->toIso8601String()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ في الخادم: ' . $e->getMessage(),
            'status_code' => 500,
            'timestamp' => now()->toIso8601String()
        ], 500);
    }
}
// public function register(Request $request)
// {
//     try {
//         // 1️⃣ التحقق من صلاحية المدير
//         $currentUser = $request->user();
        
//         if (!$currentUser || $currentUser->role != 1) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'غير مصرح لك بتنفيذ هذه العملية',
//                 'status_code' => 403,
//                 'timestamp' => now()->toIso8601String()
//             ], 403);
//         }

//         // 2️⃣ التحقق من صحة البيانات
//         $data = $request->validate([
//             'name' => 'required|string|max:255',
//             'user_name' => 'required|string|max:255',       
//             'password' => 'required|string|min:6|confirmed',
//             'role' => 'required|integer|in:1,2',
//             'id_agency' => 'nullable|exists:agencies,id'
//         ]);

//         // 3️⃣ التحقق اليدوي من اسم المستخدم
//         $exists = Administrative::where('user_name', $data['user_name'])->exists();
//         if ($exists) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'اسم المستخدم مستخدم مسبقًا',
//                 'status_code' => 409,
//                 'timestamp' => now()->toIso8601String()
//             ], 409);
//         }

//         // 4️⃣ إنشاء المستخدم
//         $user = Administrative::create([
//             'name' => $data['name'],
//             'user_name' => $data['user_name'],
//             'password' => Hash::make($data['password']),
//             'role' => $data['role'],
//             'id_agency' => $data['id_agency'] ?? null,            
//         ]);

//         // 5️⃣ الرد الناجح مع تحويل الرقم إلى نص للقراءة
//         $roleText = $this->getRoleText($data['role']);
        
//         return response()->json([
//             'success' => true,
//             'message' => 'تم إنشاء المستخدم بنجاح',
//             'data' => [
//                 'id' => $user->id,
//                 'name' => $user->name,
//                 'user_name' => $user->user_name,
//                 'role' => $roleText,
//                 'role_id' => $user->role,
//                 'agency_id' => $user->id_agency
//             ],
//             'status_code' => 201,
//             'timestamp' => now()->toIso8601String(),
//         ], 201);

//     } catch (ValidationException $e) {
//         return response()->json([
//             'success' => false,
//             'message' => 'خطأ في البيانات المدخلة',
//             'errors' => null,
//             'status_code' => 422,
//             'timestamp' => now()->toIso8601String()
//         ], 422);
//     } catch (\Exception $e) {
//         return response()->json([
//             'success' => false,
//             'message' => 'حدث خطأ في الخادم: ' . $e->getMessage(),
//             'status_code' => 500,
//             'timestamp' => now()->toIso8601String()
//         ], 500);
//     }
// }

// // 6️⃣ أضف هذه الدالة داخل الـ Controller
private function getRoleText($roleId)
{
    $roles = [
        1 => 'مدير نظام',
        2 => 'موظف'
    ];
    
    return $roles[$roleId] ?? 'غير معروف';
}

    // تسجيل الدخول
    public function login(Request $request)
    {
        $data = $request->validate([
            'user_name' => 'required|string',
            'password' => 'required|string',
        ]);

        $admin = Administrative::where('user_name', $data['user_name'])->first();

        if (!$admin || !Hash::check($data['password'], $admin->password)) {
            return response()->json([
                'success' => false,
                'message' => 'اسم المستخدم أو كلمة المرور غير صحيحة.',
                'data' => null,
                'status_code' => 401,
                'timestamp' => Carbon::now()->toIso8601String(),
            ], 401);
        }

        // توليد Token
        $token = $admin->createToken("admin_token")->plainTextToken;

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
    }

    // تسجيل الخروج
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح.',
            'data' => null,
            'status_code' => 200,
            'timestamp' => Carbon::now()->toIso8601String(),
        ], 200);
    }
}
