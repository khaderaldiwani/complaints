<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Mail\OtpCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    
    public function register(Request $request)
    {
        try {
              $data = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email', 
        'password' => 'required|string|min:6|confirmed',
    ]);

    $exists = User::where('email', $request->email)->exists();
    if ($exists) {
        return response()->json([
            'success' => false,
            'message' => 'هذا البريد مستخدم مسبقًا',
            'status_code' => 409,
            'timestamp' => now()->toIso8601String()
        ], 409);
    }
    // send via email if provided
        // if ($data['email']) {
        //     Mail::to($data['email'])->send(new OtpCodeMail("000000", "ka"));
        // }


        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            
            'password' => Hash::make($data['password']),
            'is_verified' => false,
        ]);

        $code = rand(100000, 999999); 
        $user->verification_code = $code;
        $user->verification_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        // send via email if provided
        if ($user->email) {
            Mail::to($user->email)->send(new OtpCodeMail($code, $user->name));
        }

     
        return response()->json(
            [
    'success' => true,
    'message' => 'تمت العملية بنجاح',
    'data' => [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        
    ],
    'status_code' => 200,
    'timestamp' => Carbon::now()->toIso8601String(), 
], 201);
        } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'خطأ في البيانات المدخلة',
            'errors' => $e->errors(),
            'status_code' => 422,
            'timestamp' => now()->toIso8601String()
        ], 422);
    }
        
    }


    public function verifyOtp(Request $request)
    {
        try {
            $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'code' => 'required|string',
        ]);

        $user = User::find($data['user_id']);

        if (!$user) {
            return response()->json(['message' => 'المستخدم غير موجود.'], 404);
        }

        if ($user->is_verified) {
            return response()->json(['message' => 'الحساب موثّق مسبقًا.'], 200);
        }

        if (!$user->verification_code || !$user->verification_expires_at) {
            return response()->json(['message' => 'لم يتم إرسال رمز تحقق.'], 400);
        }

        if (Carbon::now()->gt($user->verification_expires_at)) {
            return response()->json(['message' => 'انتهت صلاحية الرمز. اطلب رمزًا جديدًا.'], 400);
        }

        if ($data['code'] !== $user->verification_code) {
            return response()->json(['message' => 'الرمز غير صحيح.'], 422);
        }

        $user->is_verified = true;
        $user->verification_code = null;
        $user->verification_expires_at = null;
        $user->email_verified_at = $user->email ? Carbon::now() : $user->email_verified_at;
        $user->save();

        $token = $user->createToken('api_token')->plainTextToken;

        return  response()->json([
        'success' => true,
        'message' => 'تم التحقق بنجاح.',
        'data' => [
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
                'is_verified'=>$user->is_verified
            ],
            'token' => $token,
        ],
        'status_code' => 200,
        'timestamp' => Carbon::now()->toIso8601String(),
    ], 200);
        } catch (ValidationException $e) {
        return  response()->json([
            'success' => false,
            'message' => 'خطأ في البيانات المدخلة',
            'errors' => $e->errors(),
            'status_code' => 422,
            'timestamp' => now()->toIso8601String()
        ], 422);
        }
    }

    
    public function resendOtp(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $user = User::find($data['user_id']);

        if (!$user) {
          return  response()->json([
        'success' => false,
        'message' => 'المستخدم غير موجود.',
        'data' =>null,
        'status_code' => 404,
        'timestamp' => Carbon::now()->toIso8601String(),
    ], 404);
 }

        if ($user->is_verified) {
        return  response()->json([
        'success' => false,
        'message' => 'الحساب موثّق مسبقًا.',
        'data' =>null,
        'status_code' => 200,
        'timestamp' => Carbon::now()->toIso8601String(),
    ], 200);
        }

        $code = rand(100000, 999999);
        $user->verification_code = $code;
        $user->verification_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        if ($user->email) {
            Mail::to($user->email)->send(new OtpCodeMail($code, $user->name));
        }

    

        return  response()->json([
        'success' => true,
        'message' => 'أُعيد إرسال رمز التحقق.',
        'data' =>null,
        'status_code' => 200,
        'timestamp' => Carbon::now()->toIso8601String(),
    ], 200);
    }
public function login(Request $request)
{
    $data = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    $user = User::where('email', $data['email'])->first();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'البريد الإلكتروني غير موجود.',
            'data' => null,
            'status_code' => 404,
            'timestamp' => Carbon::now()->toIso8601String(),
        ], 404);
    }

    if (!Hash::check($data['password'], $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'كلمة المرور غير صحيحة.',
            'data' => null,
            'status_code' => 401,
            'timestamp' => Carbon::now()->toIso8601String(),
        ], 401);
    }

    if (!$user->is_verified) {
        return response()->json([
            'success' => false,
            'message' => 'الحساب غير مفعّل. يرجى تأكيد البريد أولاً.',
            'data' => null,
            'status_code' => 403,
            'timestamp' => Carbon::now()->toIso8601String(),
        ], 403);
    }

    $token = $user->createToken('api_token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'تم تسجيل الدخول بنجاح.',
        'data' => [
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
            'token' => $token,
        ],
        'status_code' => 200,
        'timestamp' => Carbon::now()->toIso8601String(),
    ], 200);
}

public function logout(Request $request)
{
    try {
        $token = $request->user()->currentAccessToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد جلسة تسجيل دخول نشطة.',
                'data' => null,
                'status_code' => 401,
                'timestamp' => Carbon::now()->toIso8601String(),
            ], 401);
        }

        $token->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح.',
            'data' => null,
            'status_code' => 200,
            'timestamp' => Carbon::now()->toIso8601String(),
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ غير متوقع أثناء تسجيل الخروج.',
            'data' => [
                'error' => $e->getMessage(),
            ],
            'status_code' => 500,
            'timestamp' => Carbon::now()->toIso8601String(),
        ], 500);
    }
}


}



    
