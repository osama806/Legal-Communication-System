<?php

namespace App\Services;

use App\Models\User;
use Auth;
use DB;
use Exception;
use Hash;

class EmployeeService
{
    protected $assetService;
    public function __construct(AssetsService $assetService)
    {
        $this->assetService = $assetService;
    }

    /**
     * register
     * @param array $data
     * @return array
     */
    public function signup(array $data)
    {
        $avatarResponse = $this->assetService->storeImage($data['avatar']);
        try {
            DB::beginTransaction();
            $employee = User::create($data);
            $employee->password = Hash::make($data["password"]);
            $employee->avatar = $avatarResponse['url'];
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

            DB::commit();
            return [
                'status' => true,
                'token' => $token,
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

        // Check if the user is null or does not have the 'employee' role
        if (!$role_user || !$role_user->hasRole('employee')) {
            return [
                'status' => false,
                'msg' => 'Does not have employee privileges!',
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
     * Get employees by admin
     * @return array
     */
    public function fetchAll()
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user()->hasRole('admin')) {
            return [
                'status' => false,
                'msg' => 'This action is unauthorized',
                'code' => 422
            ];
        }
        $employees = User::whereHas('role', function ($query) {
            $query->where('name', 'employee');
        })->get();

        return [
            'status' => true,
            'employees' => $employees
        ];
    }

    /**
     * Get one employee
     * @param string $id
     * @return array
     */
    public function fetchOne(string $id)
    {
        $employee = User::where('id', $id)->whereHas('role', function ($query) {
            $query->where('name', 'employee');
        })->first();
        if (!$employee) {
            return [
                'status' => false,
                'msg' => 'Employee Not Found',
                'code' => 404
            ];
        }

        return [
            'status' => true,
            'employee' => $employee
        ];
    }
}
