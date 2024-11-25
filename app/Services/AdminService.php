<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Auth;
use Exception;
use Hash;

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
            if (!$token = Auth::guard('api')->attempt($credentials)) {
                return [
                    'status' => false,
                    'msg' => 'Failed to generate token, but admin registered successfully',
                    'code' => 401
                ];
            }
            DB::commit();
            return [
                'status' => true,
                'token' => $token
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
        if (
            !$token = Auth::guard('api')->attempt([
                'email' => $data['email'],
                'password' => $data['password']
            ])
        ) {
            return [
                'status' => false,
                'msg' => 'Email or password is incorrect!',
                'code' => 401
            ];
        }

        // Get the authenticated user
        $role_user = Auth::guard('api')->user();

        // Check if the user is null or does not have the 'admin' role
        if (!$role_user || !$role_user->hasRole('admin')) {
            return [
                'status' => false,
                'msg' => 'Does not have admin privileges!',
                'code' => 403
            ];
        }

        return [
            "status" => true,
            'token' => $token,
            'role' => $role_user->role->name
        ];
    }
}
