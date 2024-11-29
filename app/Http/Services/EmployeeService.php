<?php

namespace App\Http\Services;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\PaginateResourceTrait;
use Auth;
use Cache;
use DB;
use Exception;
use Hash;

class EmployeeService
{
    use PaginateResourceTrait;
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
            Cache::forget('employees');
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
     * @param array $data
     * @return array
     */
    public function getList(array $data)
    {
        $employees = Cache::remember('employees', 1200, function () use ($data) {
            return User::filter($data)->whereHas('role', function ($query) {
                return $query->where('name', 'employee');
            })->paginate($data['per_page'] ?? 10);
        });

        if ($employees->isEmpty()) {
            return [
                'status' => false,
                'msg' => 'Not Found Any Employee!',
                'code' => 404
            ];
        }

        return [
            'status' => true,
            'employees' => $this->formatPagination($employees, UserResource::class, 'employees')
        ];
    }

    /**
     * Get one employee
     * @param string $id
     * @return array
     */
    public function fetchOne(string $id)
    {
        $employee = Cache::remember('employee' . $id, 600, function () use ($id) {
            return User::where('id', $id)->whereHas('role', function ($query) {
                return $query->where('name', 'employee');
            })->first();
        });

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
