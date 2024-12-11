<?php

namespace App\Http\Services;

use App\Models\Lawyer;
use App\Models\Representative;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    /**
     * Login
     * @param array $data
     * @return array
     */
    public function authenticate(array $data)
    {
        $role = $this->checkRole($data['email']);
        $guard = match ($role) {
            'admin', 'employee', 'user' => 'api',
            'lawyer' => 'lawyer',
            'representative' => 'representative',
            default => null
        };

        // محاولة تسجيل الدخول باستخدام البريد الإلكتروني وكلمة المرور
        if (!$access_token = Auth::guard($guard)->attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return [
                'status' => false,
                'msg' => 'Email or password is incorrect!',
                'code' => 401
            ];
        }

        // استرجاع المستخدم المصادق عليه
        $authenticated = Auth::guard($guard)->user();
        if (!$authenticated) {
            return [
                'status' => false,
                'msg' => ucfirst($guard) . ' not found!',
                'code' => 404
            ];
        }

        // التحقق من دور المستخدم
        if ($guard === 'lawyer' || $guard === 'representative') {
            if ($authenticated->role->name !== $role) {
                return [
                    'status' => false,
                    'msg' => 'Does not have ' . $role . 'privileges!',
                    'code' => 403
                ];
            }
        } else {
            if (!$authenticated->hasRole($role)) {
                return [
                    'status' => false,
                    'msg' => 'Does not have ' . $role . ' privileges!',
                    'code' => 403
                ];
            }
        }

        // إنشاء Refresh Token
        $refresh_token = JWTAuth::customClaims(['refresh' => true])->fromUser($authenticated);

        return [
            'status' => true,
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'role' => $role
        ];
    }

    /**
     * Logout
     * @param array $data
     * @return array
     */
    public function signout(array $data)
    {
        $guard = null;
        if ($data['role'] === 'lawyer') {
            $guard = Auth::guard('lawyer')->check() ? 'lawyer' : null;
        } elseif ($data['role'] === 'representative') {
            $guard = Auth::guard('representative')->check() ? 'representative' : null;
        } else {
            $guard = Auth::guard('api')->check() ? 'api' : null;
        }

        // التحقق من أن الحارس موجود ومصادق
        if (!$guard) {
            return [
                'status' => false,
                'msg' => 'No authenticated user found for the provided role!',
                'code' => 403
            ];
        }

        Auth::guard($guard)->logout();
        return [
            'status' => true,
            'role' => Auth::guard($guard)->user()->role->name,
        ];
    }

    /**
     * Check role
     * @param mixed $email
     * @return mixed
     */
    private function checkRole($email)
    {
        $person = User::where('email', $email)->first();
        if ($person && !$person->hasRole('user')) {
            return $person->role ? $person->role->name : null;
        }

        $lawyer = Lawyer::where('email', $email)->first();
        if ($lawyer) {
            return $lawyer->role ? $lawyer->role->name : null;
        }

        $representative = Representative::where('email', $email)->first();
        if ($representative) {
            return $representative->role ? $representative->role->name : null;
        }
        return null;
    }
}
