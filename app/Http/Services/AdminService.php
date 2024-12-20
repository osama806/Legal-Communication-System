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
    public function register(array $data)
    {
        $avatarURL = $this->assetService->storeImage($data['avatar']);
        if (!$avatarURL['status']) {
            return [
                'status' => false,
                'msg' => $avatarURL['msg'],
                'code' => $avatarURL['code']
            ];
        }

        try {
            DB::beginTransaction();
            $admin = User::create($data);
            $admin->password = Hash::make($data["password"]);
            $admin->avatar = $avatarURL['url'];
            $admin->save();

            $admin->role()->create([
                'name' => 'admin'
            ]);

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
}
