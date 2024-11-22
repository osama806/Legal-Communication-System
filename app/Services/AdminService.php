<?php

namespace App\Services;

use App\Models\Lawyer;
use App\Models\Representative;
use App\Models\User;
use Auth;
use Exception;
use Hash;

class AdminService
{
    /**
     * Admin login
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

        try {
            $admin = User::create($data);
            $admin->password = Hash::make($data["password"]);
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
            return [
                'status' => true,
                'token' => $token
            ];

        } catch (Exception $e) {
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
            $user = User::create($data);
            $user->password = Hash::make($data["password"]);
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

            return [
                'status' => true,
                'token' => $token
            ];

        } catch (Exception $e) {
            return [
                'status' => false,
                'msg' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * register employee
     * @param array $data
     * @return array
     */
    public function signupEmployee(array $data)
    {
        if (!Auth::user()->hasRole('admin')) {
            return [
                'status' => false,
                'msg' => 'This action is unauthorized.',
                'code' => 422
            ];
        }

        try {
            $employee = User::create($data);
            $employee->password = Hash::make($data["password"]);
            $employee->save();

            $employee->role()->create([
                'name' => 'employee'
            ]);

            // Mail::to($employee->email)->send(new VerifyCodeMail($employee));

            // تسجيل الدخول وتوليد التوكن
            $credentials = ['email' => $data['email'], 'password' => $data['password']];
            if (!$token = Auth::guard('api')->attempt($credentials)) {
                return [
                    'status' => false,
                    'msg' => 'Failed to generate token, but employee registered successfully',
                    'code' => 401
                ];
            }

            return [
                'status' => true,
                'token' => $token
            ];

        } catch (Exception $e) {
            return ['status' => false, 'msg' => $e->getMessage(), 'code' => 500];
        }
    }

    /**
     * register lawyer
     * @param array $data
     * @return array
     */
    public function signupLawyer(array $data)
    {
        try {
            $lawyer = Lawyer::create($data);
            $lawyer->password = Hash::make($data["password"]);
            $lawyer->save();

            $lawyer->role()->create([
                'name' => 'lawyer'
            ]);

            // Mail::to($user->email)->send(new VerifyCodeMail($user));

            // تسجيل الدخول وتوليد التوكن
            $credentials = ['email' => $data['email'], 'password' => $data['password']];
            if (!$token = Auth::guard('lawyer')->attempt($credentials)) {
                return [
                    'status' => false,
                    'msg' => 'Failed to generate token, but lawyer registered successfully',
                    'code' => 401
                ];
            }

            return [
                'status' => true,
                'token' => $token
            ];

        } catch (Exception $e) {
            return ['status' => false, 'msg' => $e->getMessage(), 'code' => 500];
        }
    }

    /**
     * register representative
     * @param array $data
     * @return array
     */
    public function signupRepresentative(array $data)
    {
        try {
            $representative = Representative::create($data);
            $representative->password = Hash::make($data["password"]);
            $representative->save();

            $representative->role()->create([
                'name' => 'representative'
            ]);

            // Mail::to($user->email)->send(new VerifyCodeMail($user));

            // تسجيل الدخول وتوليد التوكن
            $credentials = ['email' => $data['email'], 'password' => $data['password']];
            if (!$token = Auth::guard('representative')->attempt($credentials)) {
                return [
                    'status' => false,
                    'msg' => 'Failed to generate token, but representative registered successfully',
                    'code' => 401
                ];
            }

            return [
                'status' => true,
                'token' => $token
            ];

        } catch (Exception $e) {
            return ['status' => false, 'msg' => $e->getMessage(), 'code' => 500];
        }
    }
}
