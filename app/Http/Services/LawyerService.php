<?php

namespace App\Http\Services;

use App\Http\Resources\LawyerResource;
use App\Traits\PaginateResourceTrait;
use Auth;
use Cache;
use Hash;
use Illuminate\Support\Facades\DB;
use Exception;
use Log;
use Notification;
use App\Models\Agency;
use App\Models\Lawyer;
use App\Models\Representative;
use App\Notifications\LawyerToRepresentativeNotification;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class LawyerService
{
    use PaginateResourceTrait;
    protected $assetService;
    public function __construct(AssetsService $assetService)
    {
        $this->assetService = $assetService;
    }

    /**
     * Get list of lawyers
     * @param array $data
     * @return array
     */
    public function getList(array $data)
    {
        $lawyers = Cache::remember("lawyers", 1200, function () use ($data) {
            return Lawyer::filter($data)->paginate($data['per_page'] ?? 10);
        });

        if ($lawyers->isEmpty()) {
            return [
                'status' => false,
                'msg' => 'Not Found Any Lawyer!',
                'code' => 404
            ];
        }

        return [
            'status' => true,
            'lawyers' => $this->formatPagination($lawyers, LawyerResource::class, 'lawyers')
        ];
    }

    /**
     * register lawyer
     * @param array $data
     * @return array
     */
    public function signupLawyer(array $data)
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
            $lawyer = Lawyer::create($data);

            // تعيين الدور
            if (method_exists($lawyer, 'role')) {
                $lawyer->role()->create([
                    'name' => 'lawyer'
                ]);
            } else {
                throw new Exception("Role relationship not defined in Lawyer model.");
            }

            // إرسال بريد إلكتروني للتحقق (معلق في الكود)
            // Mail::to($lawyer->email)->send(new VerifyCodeMail($lawyer));

            // تسجيل الدخول وتوليد التوكن
            $credentials = ['email' => $data['email'], 'password' => $plainPassword]; // استخدم كلمة المرور الأصلية هنا
            if (!$access_token = Auth::guard('lawyer')->attempt($credentials)) {
                throw new Exception('Failed to generate token');
            }

            // توليد Refresh Token
            $refresh_token = JWTAuth::customClaims(['refresh' => true])->fromUser($lawyer);
            DB::commit();

            // إزالة الكاش (إذا تم تخزين المستخدمين في الكاش)
            Cache::forget('lawyers');
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
    public function signin(array $data)
    {
        // محاولة تسجيل الدخول باستخدام البريد الإلكتروني وكلمة المرور
        if (!$access_token = Auth::guard('lawyer')->attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return [
                'status' => false,
                'msg' => 'Email or password is incorrect!',
                'code' => 401
            ];
        }

        // استرجاع المستخدم المصادق عليه
        $lawyer = Auth::guard('lawyer')->user();
        if (!$lawyer) {
            return [
                'status' => false,
                'msg' => 'Lawyer not found!',
                'code' => 404
            ];
        }

        // التحقق من دور المستخدم
        if ($lawyer->role->name !== 'lawyer') {
            return [
                'status' => false,
                'msg' => 'Does not have lawyer privileges!',
                'code' => 403
            ];
        }

        // إنشاء Refresh Token
        $refresh_token = JWTAuth::customClaims(['refresh' => true])->fromUser($lawyer);

        return [
            'status' => true,
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
        ];
    }

    /**
     * Update agency record and send notification to representative
     * @param array $data
     * @return array
     */
    public function send(array $data)
    {
        $agency = Cache::remember('agency' . $data['agency_id'], 600, function () use ($data) {
            return Agency::find($data['agency_id']);
        });

        $representative = Cache::remember('representative' . $data['representative_id'], 600, function () use ($data) {
            return Representative::find($data['representative_id']);
        });

        try {
            DB::beginTransaction();
            $agency->representative_id = $data['representative_id'];
            $agency->type = $data['type'];
            $agency->authorizations = $data['authorizations'];
            $agency->exceptions = $data['exceptions'];
            $agency->save();
            Notification::send($representative, new LawyerToRepresentativeNotification($agency));
            DB::commit();

            Cache::forget('agency' . $agency->id);
            return ['status' => true];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'msg' => $e->getMessage(),
                'code' => 500,
            ];
        }
    }

    /**
     * Update lawyer info by employee
     * @param array $data
     * @param \App\Models\Lawyer $lawyer
     * @return array
     */
    public function update(array $data, Lawyer $lawyer)
    {
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

            $lawyer->update($filteredData);

            if ($data['avatar']) {
                $avatarResponse = $this->assetService->storeImage($data['avatar']);
                $lawyer->avatar = $avatarResponse['url'];
                $lawyer->save();
            }

            Cache::forget('lawyers');
            return ['status' => true];
        } catch (Exception $e) {
            return [
                'status' => false,
                'msg' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Delete lawyer account by employee
     * @param \App\Models\Lawyer $lawyer
     * @return array
     */
    public function destroy(Lawyer $lawyer)
    {
        if (Auth::user()->role->name !== 'employee') {
            return [
                'status' => false,
                'msg' => 'This action is unauthorized.',
                'code' => 422
            ];
        }

        try {
            // Check if the token is valid
            if (JWTAuth::parseToken()->check()) {
                JWTAuth::invalidate(JWTAuth::getToken());
            }
            $lawyer->delete();

            Cache::forget('lawyers');
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
     * Get one lawyer forward to user
     * @param string $id
     * @return array
     */
    public function fetchOne(string $id)
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user()->hasRole('user')) {
            return [
                'status' => false,
                'msg' => 'This action is unauthorized',
                'code' => 422
            ];
        }

        $lawyer = Cache::remember('lawyer' . $id, 600, function () use ($id) {
            return Lawyer::find($id);
        });

        if (!$lawyer) {
            return [
                'status' => false,
                'msg' => 'Lawyer Not Found!',
                'code' => 404
            ];
        }

        return [
            'status' => true,
            'lawyer' => $lawyer
        ];
    }

    /**
     * Get one lawyer forward to employee
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

        $lawyer = Cache::remember('lawyer' . $id, 600, function () use ($id) {
            return Lawyer::find($id);
        });
        if (!$lawyer) {
            return [
                'status' => false,
                'msg' => 'Lawyer Not Found!',
                'code' => 404
            ];
        }

        return [
            'status' => true,
            'lawyer' => $lawyer
        ];
    }
}
