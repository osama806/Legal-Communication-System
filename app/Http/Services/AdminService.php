<?php

namespace App\Http\Services;

use Auth;
use Cache;
use Exception;
use Hash;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminService
{
    protected $assetService;
    public function __construct(AssetsService $assetService)
    {
        $this->assetService = $assetService;
    }

    /**
     * Create new admin
     * @param array $data
     * @return array
     */
    public function store(array $data)
    {
        if (strpos($data['email'], '@admin') === false) {
            return [
                'status' => false,
                'msg' => 'Email address must contains mark @admin',
                'code' => 400
            ];
        }
        $avatarResponse = $this->assetService->storeImage($data['avatar']);

        try {
            DB::beginTransaction();
            $admin = User::create($data);
            $admin->password = Hash::make($data["password"]);
            $admin->avatar = $avatarResponse['url'];
            $admin->save();

            $admin->role()->create([
                'name' => 'admin'
            ]);

            // تسجيل الدخول وتوليد التوكن
            $credentials = ['email' => $data['email'], 'password' => $data['password']];
            if (!$access_token = Auth::guard('api')->attempt($credentials)) {
                return [
                    'status' => false,
                    'msg' => 'Failed to generate token',
                    'code' => 401
                ];
            }

            $refresh_token = JWTAuth::customClaims(['refresh' => true])->fromUser(Auth::guard('api')->user());
            DB::commit();

            Cache::forget('users');
            return [
                'status' => true,
                'access_token' => $access_token,
                'refresh_token' => $refresh_token
            ];

        } catch (Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'msg' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Login
     * @param array $data
     * @return mixed
     */
    public function login(array $data)
    {
        // محاولة تسجيل الدخول باستخدام البريد الإلكتروني وكلمة المرور
        if (!$access_token = Auth::guard('api')->attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return [
                'status' => false,
                'msg' => 'Email or password is incorrect!',
                'code' => 401
            ];
        }

        // استرجاع المستخدم المصادق عليه
        $admin = Auth::guard('api')->user();
        if (!$admin) {
            return [
                'status' => false,
                'msg' => 'User not found!',
                'code' => 404
            ];
        }

        // التحقق من دور المستخدم
        if (!$admin->hasRole('admin')) {
            return [
                'status' => false,
                'msg' => 'Does not have admin privileges!',
                'code' => 403
            ];
        }

        // إنشاء Refresh Token
        $refresh_token = JWTAuth::customClaims(['refresh' => true])->fromUser($admin);

        return [
            'status' => true,
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
        ];
    }
}
