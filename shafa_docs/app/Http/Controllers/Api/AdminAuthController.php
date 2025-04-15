<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminAuthController extends Controller
{
    /**
     * ورود مدیر
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

            // تغییر روش احراز هویت
            $credentials = $request->only('email', 'password');

            // جستجوی ادمین با ایمیل مورد نظر
            $admin = Admin::where('email', $credentials['email'])->first();

            // بررسی وجود ادمین و صحت رمز عبور
            if (!$admin || !Hash::check($credentials['password'], $admin->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'ایمیل یا رمز عبور اشتباه است.',
                    'errors' => ['credentials' => ['اطلاعات ورود صحیح نیست.']]
                ], 401);
            }

            // ایجاد توکن برای ادمین
            $token = $admin->createToken('AdminToken', ['admin'])->accessToken;

            return response()->json([
                'status' => true,
                'message' => 'ورود مدیر با موفقیت انجام شد',
                'admin' => $admin,
                'token' => $token
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطا در ورود به سیستم',
                'errors' => ['server' => ['خطای سیستمی رخ داده است: ' . $e->getMessage()]]
            ], 500);
        }
    }

    /**
     * اطلاعات مدیر
     */
    public function admin(Request $request)
    {
        return response()->json([
            'status' => true,
            'message' => 'اطلاعات مدیر',
            'admin' => $request->user()
        ]);
    }

    /**
     * خروج مدیر
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->token()->revoke();

            return response()->json([
                'status' => true,
                'message' => 'خروج با موفقیت انجام شد'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطا در خروج از سیستم',
                'errors' => ['server' => ['خطای سیستمی رخ داده است: ' . $e->getMessage()]]
            ], 500);
        }
    }

    /**
     * ایجاد مدیر جدید (فقط برای مدیران ارشد)
     */
    /**
     * ایجاد مدیر جدید
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:admins',
                'password' => 'required|string|min:6|confirmed',
            ], [
                'email.unique' => 'این ایمیل قبلاً ثبت شده است.',
                'email.required' => 'وارد کردن ایمیل الزامی است.',
                'email.email' => 'لطفاً یک ایمیل معتبر وارد کنید.',
                'name.required' => 'وارد کردن نام الزامی است.',
                'password.required' => 'وارد کردن رمز عبور الزامی است.',
                'password.min' => 'رمز عبور باید حداقل 6 کاراکتر باشد.',
                'password.confirmed' => 'تأیید رمز عبور مطابقت ندارد.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'خطا در اعتبارسنجی اطلاعات',
                    'errors' => $validator->errors()
                ], 422);
            }

            // ایجاد مدیر
            $admin = Admin::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // ایجاد توکن
            $token = $admin->createToken('AdminToken', ['admin'])->accessToken;

            // پاسخ
            return response()->json([
                'status' => true,
                'message' => 'ثبت مدیر با موفقیت انجام شد',
                'admin' => $admin,
                'token' => $token
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطا در ثبت مدیر',
                'errors' => ['server' => ['خطای سیستمی رخ داده است: ' . $e->getMessage()]]
            ], 500);
        }
    }



    public function createAdmin(Request $request)
    {
        try {
            // اعتبارسنجی داده‌ها
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:admins',
                'password' => 'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'خطا در اعتبارسنجی اطلاعات',
                    'errors' => $validator->errors()
                ], 422);
            }

            // ایجاد مدیر جدید
            $admin = Admin::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'مدیر جدید با موفقیت ایجاد شد',
                'admin' => $admin
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطا در ایجاد مدیر جدید',
                'errors' => ['server' => ['خطای سیستمی رخ داده است: ' . $e->getMessage()]]
            ], 500);
        }
    }
}
