<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserAuthController extends Controller
{
    /**
     * ثبت نام کاربر جدید
     */
    public function register(Request $request)
    {
        try {
            // اعتبارسنجی داده‌ها
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
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

            // ایجاد کاربر
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // ایجاد توکن
            $token = $user->createToken('UserToken', ['user'])->accessToken;

            // پاسخ
            return response()->json([
                'status' => true,
                'message' => 'ثبت نام با موفقیت انجام شد',
                'user' => $user,
                'token' => $token
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطا در ثبت نام',
                'errors' => ['server' => ['خطای سیستمی رخ داده است: ' . $e->getMessage()]]
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

            // بررسی اعتبار
            if (Auth::attempt($request->only('email', 'password'))) {
                $user = Auth::user();
                $token = $user->createToken('UserToken', ['user'])->accessToken;

                return response()->json([
                    'status' => true,
                    'message' => 'ورود با موفقیت انجام شد',
                    'user' => $user,
                    'token' => $token
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'ایمیل یا رمز عبور اشتباه است.',
                'errors' => ['credentials' => ['اطلاعات ورود صحیح نیست.']]
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطا در ورود به سیستم',
                'errors' => ['server' => ['خطای سیستمی رخ داده است: ' . $e->getMessage()]]
            ], 500);
        }
    }

    /**
     * اطلاعات کاربر
     */
    public function user(Request $request)
    {
        return response()->json([
            'status' => true,
            'message' => 'اطلاعات کاربر',
            'user' => $request->user()
        ]);
    }

    /**
     * خروج کاربر
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
}
