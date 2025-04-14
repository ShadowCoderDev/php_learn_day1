<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class AuthController extends Controller
{
    /**
     * ثبت نام کاربر جدید
     */
    public function register(Request $request)
    {
        try {
            // اعتبارسنجی داده‌ها با پیام‌های خطای سفارشی
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
            ], [
                'email.unique' => 'این ایمیل قبلاً ثبت شده است. لطفاً از ایمیل دیگری استفاده کنید یا وارد شوید.',
                'email.required' => 'وارد کردن ایمیل الزامی است.',
                'email.email' => 'لطفاً یک ایمیل معتبر وارد کنید.',
                'name.required' => 'وارد کردن نام الزامی است.',
                'password.required' => 'وارد کردن رمز عبور الزامی است.',
                'password.min' => 'رمز عبور باید حداقل 6 کاراکتر باشد.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'خطا در اعتبارسنجی اطلاعات',
                    'errors' => $validator->errors()
                ], 422);
            }

            // بررسی اضافی ایمیل تکراری
            $existingUser = User::where('email', $request->email)->first();
            if ($existingUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'این ایمیل قبلاً ثبت شده است.',
                    'errors' => [
                        'email' => ['این ایمیل قبلاً ثبت شده است. لطفاً از ایمیل دیگری استفاده کنید یا وارد شوید.']
                    ]
                ], 422);
            }

            // ایجاد کاربر
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // ایجاد توکن
            $token = $user->createToken('auth_token')->plainTextToken;

            // پاسخ
            return response()->json([
                'status' => true,
                'message' => 'ثبت نام با موفقیت انجام شد',
                'user' => $user,
                'token' => $token
            ], 201);

        } catch (QueryException $e) {
            // خطای دیتابیس
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1062) { // کد خطای تکراری بودن در MySQL
                return response()->json([
                    'status' => false,
                    'message' => 'این ایمیل قبلاً ثبت شده است.',
                    'errors' => [
                        'email' => ['این ایمیل قبلاً ثبت شده است. لطفاً از ایمیل دیگری استفاده کنید یا وارد شوید.']
                    ]
                ], 422);
            }

            return response()->json([
                'status' => false,
                'message' => 'خطا در ذخیره اطلاعات',
                'errors' => ['database' => ['خطا در ذخیره اطلاعات. لطفا دوباره تلاش کنید.']]
            ], 500);

        } catch (\Exception $e) {
            // سایر خطاها
            return response()->json([
                'status' => false,
                'message' => 'خطا در ثبت نام',
                'errors' => ['server' => ['خطای سیستمی رخ داده است. لطفا دوباره تلاش کنید.']]
            ], 500);
        }
    }

    /**
     * ورود کاربر
     */
    public function login(Request $request)
    {
        try {
            // اعتبارسنجی داده‌ها
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ], [
                'email.required' => 'وارد کردن ایمیل الزامی است.',
                'email.email' => 'لطفاً یک ایمیل معتبر وارد کنید.',
                'password.required' => 'وارد کردن رمز عبور الزامی است.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'خطا در اعتبارسنجی اطلاعات',
                    'errors' => $validator->errors()
                ], 422);
            }

            // بررسی وجود کاربر
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'کاربری با این ایمیل یافت نشد.',
                    'errors' => [
                        'email' => ['کاربری با این ایمیل یافت نشد.']
                    ]
                ], 401);
            }

            // بررسی اعتبار
            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'status' => false,
                    'message' => 'رمز عبور اشتباه است.',
                    'errors' => [
                        'password' => ['رمز عبور وارد شده صحیح نیست.']
                    ]
                ], 401);
            }

            // ایجاد توکن
            $token = $user->createToken('auth_token')->plainTextToken;

            // پاسخ
            return response()->json([
                'status' => true,
                'message' => 'ورود با موفقیت انجام شد',
                'user' => $user,
                'token' => $token
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطا در ورود به سیستم',
                'errors' => ['server' => ['خطای سیستمی رخ داده است. لطفا دوباره تلاش کنید.']]
            ], 500);
        }
    }

    /**
     * اطلاعات کاربر
     */
    public function user(Request $request)
    {
        try {
            return response()->json([
                'status' => true,
                'message' => 'اطلاعات کاربر',
                'user' => $request->user()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطا در دریافت اطلاعات کاربر',
                'errors' => ['server' => ['خطای سیستمی رخ داده است. لطفا دوباره تلاش کنید.']]
            ], 500);
        }
    }

    /**
     * خروج کاربر
     */
    /**
     * خروج کاربر
     */
    public function logout(Request $request)
    {
        try {
            // بررسی وجود کاربر احراز هویت شده
            if (!$request->user()) {
                return response()->json([
                    'status' => false,
                    'message' => 'کاربر احراز هویت نشده است',
                    'errors' => [
                        'auth' => ['لطفاً ابتدا وارد سیستم شوید']
                    ]
                ], 401);
            }

            // حذف توکن فعلی
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => true,
                'message' => 'خروج با موفقیت انجام شد'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطا در خروج از سیستم',
                'errors' => [
                    'server' => ['خطای سیستمی رخ داده است. لطفا دوباره تلاش کنید: ' . $e->getMessage()]
                ]
            ], 500);
        }
    }
}
