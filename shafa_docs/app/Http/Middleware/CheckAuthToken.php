<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthToken
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Check for Authorization header
            if (!$request->hasHeader('Authorization')) {
                return response()->json([
                    'status' => false,
                    'message' => 'هدر Authorization یافت نشد',
                    'errors' => [
                        'token' => ['لطفاً هدر Authorization را با فرمت Bearer {token} ارسال کنید']
                    ]
                ], 401);
            }

            // Check for correct Authorization format
            $authHeader = $request->header('Authorization');
            if (!str_starts_with($authHeader, 'Bearer ')) {
                return response()->json([
                    'status' => false,
                    'message' => 'فرمت توکن نامعتبر است',
                    'errors' => [
                        'token' => ['فرمت هدر Authorization باید به صورت Bearer {token} باشد']
                    ]
                ], 401);
            }

            // Continue to the next middleware
            return $next($request);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطا در بررسی توکن',
                'errors' => [
                    'token' => ['خطایی در بررسی توکن رخ داده است: ' . $e->getMessage()]
                ]
            ], 500);
        }
    }
}
