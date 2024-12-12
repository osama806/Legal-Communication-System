<?php

namespace App\Http\Services;

use App\Http\Resources\UserResource;
use App\Traits\PaginateResourceTrait;
use Cache;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\User;
use App\Traits\ResponseTrait;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class UserService
{
    use ResponseTrait, PaginateResourceTrait;

    protected $assetService;
    public function __construct(AssetsService $assetService)
    {
        $this->assetService = $assetService;
    }

    /**
     * Create new user
     * @param array $data
     * @return array
     */
    public function register(array $data)
    {
        try {
            $avatarResponse = $this->assetService->storeImage($data['avatar']);
            DB::beginTransaction();

            $plainPassword = $data['password'];
            $data['password'] = Hash::make($plainPassword);
            $data['avatar'] = $avatarResponse['url'];
            $user = User::create($data);

            if (method_exists($user, 'role')) {
                $user->role()->create([
                    'name' => 'user'
                ]);
            } else {
                throw new Exception("Role relationship not defined in User model.");
            }

            // Mail::to($user->email)->send(new VerifyCodeMail($user));

            $credentials = ['email' => $data['email'], 'password' => $plainPassword]; // استخدم كلمة المرور الأصلية هنا
            if (!$access_token = Auth::guard('api')->attempt($credentials)) {
                throw new Exception('Failed to generate token');
            }

            $refresh_token = JWTAuth::customClaims(['refresh' => true])->fromUser($user);
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
     * @return array
     */
    public function login(array $data)
    {
        if (!$access_token = Auth::guard('api')->attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return [
                'status' => false,
                'msg' => 'Email or password is incorrect!',
                'code' => 401
            ];
        }

        $user = Auth::guard('api')->user();
        if (!$user) {
            return [
                'status' => false,
                'msg' => 'User not found!',
                'code' => 404
            ];
        }

        if (!$user->hasRole('user')) {
            return [
                'status' => false,
                'msg' => 'Does not have user privileges!',
                'code' => 403
            ];
        }

        $refresh_token = JWTAuth::customClaims(['refresh' => true])->fromUser($user);
        return [
            'status' => true,
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
        ];
    }

    /**
     * Update account info owned
     * @param array $data
     * @return array
     */
    public function updateProfile(array $data)
    {
        $user = Auth::user();
        try {
            $filteredData = array_filter($data, function ($value) {
                return !is_null($value) && trim($value) !== '';
            });

            if (count($filteredData) < 1) {
                return [
                    'status' => false,
                    'msg' => 'Not Found Any Data to Update',
                    'code' => 404
                ];
            }
            $user->update($filteredData);

            if ($data['avatar']) {
                $avatarResponse = $this->assetService->storeImage($data['avatar']);
                $user->avatar = $avatarResponse['url'];
                $user->save();
            }

            Cache::forget('users');
            Cache::forget('user_' . $user->id);
            return ['status' => true];
        } catch (Exception $e) {

            return [
                'status' => false,
                'msg' => 'Failed to update profile. Please try again.',
                'code' => 500
            ];
        }
    }

    /**
     * Change password by owned
     * @param array $data
     * @return array
     */
    public function updatePassword(array $data)
    {
        $user = Auth::user();
        if (!Hash::check($data['current_password'], $user->password)) {
            return [
                'status' => false,
                'msg' => 'The current password is incorrect',
                'code' => 400
            ];
        }

        $user->password = Hash::make($data['new_password']);
        $user->save();
        Cache::forget('user_' . $user->id);
        return ['status' => true];
    }

    /**
     * Delete account owned
     * @return array
     */
    public function deleteAccount()
    {
        $user = Auth::user();
        try {
            // Check if the token is valid
            if (JWTAuth::parseToken()->check()) {
                JWTAuth::invalidate(JWTAuth::getToken());
            }
            $user->delete();
            Cache::forget('users');
            return ['status' => true];

        } catch (TokenInvalidException $e) {
            Log::error('Error Invalid token: ' . $e->getMessage());
            return ['status' => false, 'msg' => 'Invalid token.', 'code' => 401];
        } catch (JWTException $e) {
            Log::error('Error invalidating token: ' . $e->getMessage());
            return ['status' => false, 'msg' => 'Failed to invalidate token, please try again.', 'code' => 500];
        } catch (Exception $e) {
            Log::error('Error deleting account: ' . $e->getMessage());
            return ['status' => false, 'msg' => $e->getMessage(), 'code' => 500];
        }
    }

    /**
     * register user
     * @param array $data
     * @return array
     */
    public function signupUser(array $data)
    {
        try {
            $avatarResponse = $this->assetService->storeImage($data['avatar']);
            DB::beginTransaction();

            $plainPassword = $data['password'];
            $data['password'] = Hash::make($plainPassword);
            $data['avatar'] = $avatarResponse['url'];
            $user = User::create($data);

            // تعيين الدور
            if (method_exists($user, 'role')) {
                $user->role()->create([
                    'name' => 'user'
                ]);
            } else {
                throw new Exception("Role relationship not defined in User model.");
            }

            // Mail::to($user->email)->send(new VerifyCodeMail($user));

            $credentials = ['email' => $data['email'], 'password' => $plainPassword]; // استخدم كلمة المرور الأصلية هنا
            if (!$access_token = Auth::guard('api')->attempt($credentials)) {
                throw new Exception('Failed to generate token');
            }

            $refresh_token = JWTAuth::customClaims(['refresh' => true])->fromUser($user);
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
     * Update user info
     * @param array $data
     * @param \App\Models\User $user
     * @return array
     */
    public function updateUser(array $data, User $user)
    {
        if (!$user->hasRole('user')) {
            return [
                'status' => false,
                'msg' => 'Not allow this permission.',
                'code' => 422,
            ];
        }

        try {
            $filteredData = array_filter($data, function ($value) {
                return !is_null($value) && trim($value) !== '';
            });

            if (count($filteredData) < 1) {
                return [
                    'status' => false,
                    'msg' => 'Not Found Any Data to Update',
                    'code' => 404
                ];
            }

            $user->update($filteredData);

            if ($data['avatar']) {
                $avatarResponse = $this->assetService->storeImage($data['avatar']);
                $user->avatar = $avatarResponse['url'];
                $user->save();
            }
            Cache::forget('users');
            Cache::forget('user_' . $user->id);
            return ['status' => true];

        } catch (Exception $e) {
            return [
                'status' => false,
                'msg' => 'Failed to update profile. Please try again.',
                'code' => 500
            ];
        }
    }

    /**
     * Delete user account
     * @param \App\Models\User $user
     * @return array
     */
    public function deleteUser(User $user)
    {
        if (!$user->hasRole('user')) {
            return ['status' => false, 'msg' => 'Not allow this permission.', 'code' => 422];
        }

        try {
            if (JWTAuth::parseToken()->check()) {
                JWTAuth::invalidate(JWTAuth::getToken());
            }
            $user->delete();
            Cache::forget('users');
            Cache::forget('user_' . $user->id);
            return ['status' => true];

        } catch (TokenInvalidException $e) {
            Log::error('Error Invalid token: ' . $e->getMessage());
            return ['status' => false, 'msg' => 'Invalid token.', 'code' => 401];
        } catch (JWTException $e) {
            Log::error('Error invalidating token: ' . $e->getMessage());
            return ['status' => false, 'msg' => 'Failed to invalidate token, please try again.', 'code' => 500];
        } catch (Exception $e) {
            Log::error('Error deleting account: ' . $e->getMessage());
            return ['status' => false, 'msg' => $e->getMessage(), 'code' => 500];
        }
    }

    /**
     * Get all users
     * @param array $data
     * @return array
     */
    public function getList(array $data)
    {
        $users = Cache::remember('users', 1200, function () use ($data) {
            return User::filter($data)->whereHas('role', function ($query) {
                $query->where('name', 'user');
            })->paginate($data['per_page'] ?? 10);
        });

        if ($users->isEmpty()) {
            return [
                'status' => false,
                'msg' => 'Not Found Any User!',
                'code' => 404
            ];
        }
        return [
            'status' => true,
            'users' => $this->formatPagination($users, UserResource::class, 'users')
        ];
    }

    /**
     * Get one user by admin & employee
     * @param string $id
     * @return array
     */
    public function fetchOne(string $id)
    {
        if (!Auth::guard('api')->check() || Auth::guard('api')->user()->hasRole('user')) {
            return [
                'status' => false,
                'msg' => 'This action is unauthorized',
                'code' => 422
            ];
        }
        $user = Cache::remember('user_' . $id, 600, function () use ($id) {
            return User::where('id', $id)->whereHas('role', function ($query) {
                $query->where('name', 'user');
            })->first();
        });

        if (!$user) {
            return [
                'status' => false,
                'msg' => 'User Not Found',
                'code' => 404
            ];
        }
        return [
            'status' => true,
            'user' => $user
        ];
    }

}
