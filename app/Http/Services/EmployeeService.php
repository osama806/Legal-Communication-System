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
    public function signup(array $data)
    {
        try {
            // تحميل الصورة باستخدام الخدمة
            $avatarResponse = $this->assetService->storeImage($data['avatar']);
            DB::beginTransaction();

            // حفظ كلمة المرور الأصلية للاستخدام لاحقًا في محاولة تسجيل الدخول
            $plainPassword = $data['password'];

            // تشفير كلمة المرور قبل إنشاء المستخدم
            $data['password'] = Hash::make($plainPassword);
            $data['avatar'] = $avatarResponse['url'];

            // إنشاء المستخدم
            $user = User::create($data);

            // تعيين الدور
            if (method_exists($user, 'role')) {
                $user->role()->create([
                    'name' => 'employee'
                ]);
            } else {
                throw new Exception("Role relationship not defined in User model.");
            }

            // إرسال بريد إلكتروني للتحقق (معلق في الكود)
            // Mail::to($user->email)->send(new VerifyCodeMail($user));

            // تسجيل الدخول وتوليد التوكن
            $credentials = ['email' => $data['email'], 'password' => $plainPassword]; // استخدم كلمة المرور الأصلية هنا
            if (!$access_token = Auth::guard('api')->attempt($credentials)) {
                throw new Exception('Failed to generate token');
            }

            // توليد Refresh Token
            $refresh_token = JWTAuth::customClaims(['refresh' => true])->fromUser($user);

            DB::commit();

            // إزالة الكاش (إذا تم تخزين المستخدمين في الكاش)
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
     * Login
     * @param array $data
     * @return array
     */
    public function login(array $data)
    {
        // محاولة تسجيل الدخول باستخدام البريد الإلكتروني وكلمة المرور
        if (!$access_token = Auth::guard('api')->attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return [
                'status' => false,
                'msg' => 'Email or password is incorrect!',
                'code' => 401
            ];
        }

        // استرجاع المستخدم المصادق عليه
        $employee = Auth::guard('api')->user();
        if (!$employee) {
            return [
                'status' => false,
                'msg' => 'Employee not found!',
                'code' => 404
            ];
        }

        // التحقق من دور المستخدم
        if (!$employee->hasRole('employee')) {
            return [
                'status' => false,
                'msg' => 'Does not have employee privileges!',
                'code' => 403
            ];
        }

        // إنشاء Refresh Token
        $refresh_token = JWTAuth::customClaims(['refresh' => true])->fromUser($employee);

        return [
            'status' => true,
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
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
