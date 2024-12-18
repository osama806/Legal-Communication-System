<?php

namespace App\Http\Services;

use App\Http\Resources\UserResource;
use App\Models\CodeGenerate;
use App\Models\User;
use App\Traits\PaginateResourceTrait;
use Auth;
use Cache;
use Carbon\Carbon;
use DB;
use Exception;
use Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

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
    public function register(array $data)
    {
        try {
            $code = CodeGenerate::where('email', $data['email'])->first();
            if (!$code) {
                return [
                    'status' => false,
                    'msg' => 'No verification code found for this email!',
                    'code' => 404
                ];
            }
            $date = Carbon::parse($code->expiration_date);
            if ($date->isFuture() || !$code->is_verify) {
                return [
                    'status' => false,
                    'msg' => 'This Email Not Verify!',
                    'code' => 403
                ];
            }

            $avatarResponse = $this->assetService->storeImage($data['avatar']);
            DB::beginTransaction();

            $plainPassword = $data['password'];
            $data['password'] = Hash::make($plainPassword);
            $data['avatar'] = $avatarResponse['url'];

            $user = User::create($data);

            if (method_exists($user, 'role')) {
                $user->role()->create([
                    'name' => 'employee'
                ]);
            } else {
                throw new Exception("Role relationship not defined in User model.");
            }

            $credentials = ['email' => $data['email'], 'password' => $plainPassword]; // استخدم كلمة المرور الأصلية هنا
            if (!$access_token = Auth::guard('api')->attempt($credentials)) {
                throw new Exception('Failed to generate token');
            }

            $refresh_token = JWTAuth::customClaims(['refresh' => true])->fromUser($user);
            DB::commit();
            Cache::forget('employees');
            return [
                'status' => true,
                'access_token' => $access_token,
                'refresh_token' => $refresh_token
            ];

        } catch (Exception $e) {
            DB::rollBack();
            return ['status' => false, 'msg' => $e->getMessage(), 'code' => 500];
        }
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
        $employee = Cache::remember('employee_' . $id, 600, function () use ($id) {
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
