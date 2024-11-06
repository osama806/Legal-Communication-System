<?php

namespace App\Services;

use App\Mail\VerifyCodeMail;
use App\Models\Role;
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

class AuthService
{
    use ResponseTrait;

    /**
     * Create a new user in storage.
     * @param mixed $data
     * @return array
     */
    public function register(array $data)
    {
        $role = false;

        // check if email request contains @admin
        if (strpos($data['email'], '@admin') !== false) {
            $role = true;
        }
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'address' => $data['address'],
                'birthdate' => $data['birthdate'],
                'birth_place' => $data['birth_place'],
                'national_number' => $data['national_number'],
                'gender' => $data['gender'],
                'phone' => $data['phone'],
            ]);

            $user->role()->create([
                'name' => $role ? 'admin' : 'user'
            ]);

            Mail::to($user->email)->send(new VerifyCodeMail($user));

            return ['status' => true];
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return ['status' => false, 'msg' => 'Unable to create new user. Please try again later.', 'code' => 500];
        }
    }

    /**
     * Check if user authorize or unAuthorize
     * @param array $data
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     * @return array
     */
    public function login(array $data)
    {
        $credentials = [
            "email" => $data['email'],
            "password" => $data['password']
        ];
        $token = JWTAuth::attempt($credentials);
        if (!$token) {
            return ['status' => false, 'msg' => "username or password is incorrect", 'code' => 401];
        }
        return ["status" => true, "token" => $token];
    }

    /**
     * Change password
     * @param array $data
     * @return array
     */
    public function changePassword(array $data)
    {
        $user = Auth::user();
        Log::info($user);

        // Check if the current password matches
        if (!Hash::check($data['current_password'], $user->password)) {
            return [
                'status' => false,
                'msg' => 'The current password is incorrect.',
                'code' => 400
            ];
        }

        // Update the user's password
        $user->password = Hash::make($data['new_password']);
        $user->save();

        return ['status' => true];
    }


    /**
     * Get user profile data
     * @return array
     */
    public function show()
    {
        $user = Auth::user();
        $role = Role::where('rolable_id', $user->id)->first();
        $data = [
            "name" => $user->name,
            "email" => $user->email,
            "is_admin" => $role->name == 'admin' ? 'Yes' : 'No'
        ];
        return ['status' => true, 'profile' => $data];
    }

    /**
     * Update user profile in storage
     * @param array $data
     * @throws \Exception
     * @return bool[]
     */
    public function updateProfile(array $data)
    {
        try {
            $user = Auth::user();
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


    /**
     * Delete user from storage.
     * @throws \Exception
     * @return bool[]
     */
    public function deleteUser()
    {
        try {
            // Get the authenticated user before invalidating the token
            $user = Auth::user();

            // Check if the token is valid
            if (JWTAuth::parseToken()->check()) {
                // Invalidate the token
                JWTAuth::invalidate(JWTAuth::getToken());
            }

            // Delete the user after the token is invalidated
            DB::table('users')->where('id', $user->id)->delete();

            return ['status' => true];
        } catch (TokenInvalidException $e) {
            Log::error('Error Invalid token: ' . $e->getMessage());
            return ['status' => false, 'msg' => 'Invalid token.', 'code' => 401];
        } catch (JWTException $e) {
            Log::error('Error invalidating token: ' . $e->getMessage());
            return ['status' => false, 'msg' => 'Failed to invalidate token, please try again.', 'code' => 500];
        } catch (Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            return ['status' => false, 'msg' => 'Failed to delete user, please try again.', 'code' => 500];
        }
    }
}
