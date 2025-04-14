<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckForRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        if (!$request->user()->tokenCan($role)) {
            return response()->json([
                'status' => false,
                'message' => 'شما دسترسی لازم برای این عملیات را ندارید.',
                'errors' => ['access' => ['دسترسی غیرمجاز']]
            ], 403);
        }

        return $next($request);
    }
}
