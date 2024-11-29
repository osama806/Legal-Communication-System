<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class RefreshTokenMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            // محاول المصادقة باستخدام التوكن
            $user = JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            try {
                // تجديد التوكن
                $newAccessToken = JWTAuth::refresh(JWTAuth::getToken());
                $newRefreshToken = JWTAuth::customClaims(['refresh' => true])->fromUser(JWTAuth::user());

                // متابعة الطلب وإرجاع الاستجابة
                $response = $next($request);

                // إضافة التوكنات في الهيدر
                return $response->header('Authorization', 'Bearer ' . $newAccessToken)
                    ->header('Refresh-Token', $newRefreshToken);
            } catch (JWTException $e) {
                return response()->json(['message' => 'Token expired, please log in again'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token invalid'], 401);
        }

        return $next($request);
    }
}
