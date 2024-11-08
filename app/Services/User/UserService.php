<?php

namespace App\Services\User;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Mail\VerifyCodeMail;
use App\Models\User;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Mail;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserService
{
    use ResponseTrait;

    public function register(array $data)
    {
        try {
            $user = User::create($data);
            $user->password = Hash::make($data["password"]);
            $user->save();

            $user->role()->create([
                'name' => 'user'
            ]);

            // Mail::to($user->email)->send(new VerifyCodeMail($user));

            return ['status' => true];
        } catch (Exception $e) {
            return ['status' => false, 'msg' => 'Unable to create new user. Please try again later.', 'code' => 500];
        }
    }

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
            return ['status' => true];
        } catch (Exception $e) {

            return [
                'status' => false,
                'msg' => 'Failed to update profile. Please try again.',
                'code' => 500
            ];
        }
    }

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
        } catch (\Exception $e) {
            Log::error('Error deleting account: ' . $e->getMessage());
            return ['status' => false, 'msg' => 'Failed to delete account, please try again.', 'code' => 500];
        }
    }

    public function updateUserProfileByEmployee(array $data, User $user)
    {
        if (!$user->hasRole('user')) {
            return $this->getResponse('error', 'Not allow this permission.', 400);
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
            return ['status' => true];
        } catch (Exception $e) {
            return [
                'status' => false,
                'msg' => 'Failed to update profile. Please try again.',
                'code' => 500
            ];
        }
    }

    public function deleteUserAccountByEmployee(User $user)
    {
        if (!$user->hasRole('user')) {
            return ['status' => false, 'msg' => 'Not allow this permission.', 'code' => 400];
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
            return ['status' => false, 'msg' => 'Failed to delete account, please try again.', 'code' => 500];
        }
    }

}
