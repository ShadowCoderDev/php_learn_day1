<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // بررسی وجود توکن
            if (!$request->bearerToken()) {
                return response()->json([
                    'status' => false,
                    'message' => 'توکن احراز هویت ارائه نشده است',
                    'errors' => [
                        'token' => ['لطفاً توکن را در هدر Authorization ارسال کنید']
                    ]
                ], 401);
            }

            // بررسی اعتبار توکن
            if (!$request->user()) {
                return response()->json([
                    'status' => false,
                    'message' => 'توکن احراز هویت نامعتبر است',
                    'errors' => [
                        'token' => ['توکن ارائه شده نامعتبر یا منقضی شده است']
                    ]
                ], 401);
            }

            return $next($request);

        } catch (AuthenticationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطای احراز هویت',
                'errors' => [
                    'token' => ['خطا در احراز هویت: ' . $e->getMessage()]
                ]
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطای سیستمی',
                'errors' => [
                    'server' => ['خطای سیستمی در احراز هویت، لطفاً دوباره تلاش کنید']
                ]
            ], 500);
        }
    }
}
