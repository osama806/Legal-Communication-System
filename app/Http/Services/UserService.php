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
        $avatarResponse = $this->assetService->storeImage($data['avatar']);
        try {
            DB::beginTransaction();
            $user = User::create($data);
            $user->password = Hash::make($data["password"]);
            $user->avatar = $avatarResponse['url'];
            $user->save();

            $user->role()->create([
                'name' => 'user'
            ]);

            // Mail::to($user->email)->send(new VerifyCodeMail($user));

            // تسجيل الدخول وتوليد التوكن
            $credentials = ['email' => $data['email'], 'password' => $data['password']];
            if (!$token = Auth::guard('api')->attempt($credentials)) {
                return [
                    'status' => false,
                    'msg' => 'Failed to generate token, but user registered successfully',
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
            return ['status' => false, 'msg' => $e->getMessage(), 'code' => 500];
        }
    }

    /**
     * Login
     * @param array $data
     * @return array
     */
    public function login(array $data)
    {
        if (!$token = Auth::guard('api')->attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return [
                'status' => false,
                'msg' => 'Email or password is incorrect!',
                'code' => 401
            ];
        }

        // Get the authenticated user
        $role_user = Auth::guard('api')->user();

        // Check if the user is null or does not have the 'user' role
        if (!$role_user || !$role_user->hasRole('user')) {
            return [
                'status' => false,
                'msg' => 'Does not have user privileges!',
                'code' => 403
            ];
        }

        return [
            'status' => true,
            'token' => $token,
            'role' => $role_user->role->name
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

        // Check if the current password matches
        if (!Hash::check($data['current_password'], $user->password)) {
            return [
                'status' => false,
                'msg' => 'The current password is incorrect',
                'code' => 400
            ];
        }

        // Update the user's password
        $user->password = Hash::make($data['new_password']);
        $user->save();

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
        $avatarResponse = $this->assetService->storeImage($data['avatar']);
        try {
            DB::beginTransaction();
            $user = User::create($data);
            $user->password = Hash::make($data["password"]);
            $user->avatar = $avatarResponse['url'];
            $user->save();

            $user->role()->create([
                'name' => 'user'
            ]);

            // Mail::to($user->email)->send(new VerifyCodeMail($user));

            // تسجيل الدخول وتوليد التوكن
            $credentials = ['email' => $data['email'], 'password' => $data['password']];
            if (!$token = Auth::guard('api')->attempt($credentials)) {
                return [
                    'status' => false,
                    'msg' => 'Failed to generate token, but user registered successfully',
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
        $users = Cache::remember('users', 3600, function () use ($data) {
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
     * Get one user by admin
     * @param string $id
     * @return array
     */
    public function fetchOne(string $id)
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user()->hasRole('admin')) {
            return [
                'status' => false,
                'msg' => 'This action is unauthorized',
                'code' => 422
            ];
        }

        $user = User::where('id', $id)->whereHas('role', function ($query) {
            $query->where('name', 'user');
        })->first();

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

    /**
     * Get one user by employee
     * @param string $id
     * @return array
     */
    public function fetchOneForEmployee(string $id)
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user()->hasRole('employee')) {
            return [
                'status' => false,
                'msg' => 'This action is unauthorized',
                'code' => 422
            ];
        }

        $user = User::where('id', $id)->whereHas('role', function ($query) {
            $query->where('name', 'user');
        })->first();

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
