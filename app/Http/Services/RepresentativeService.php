<?php

namespace App\Http\Services;

use App\Http\Resources\RepresentativeResource;
use App\Traits\PaginateResourceTrait;
use Cache;
use Hash;
use Illuminate\Support\Facades\DB;
use Log;
use Auth;
use Exception;
use App\Models\User;
use App\Models\Agency;
use App\Models\Lawyer;
use App\Models\Representative;
use Illuminate\Support\Facades\Notification;
use App\Notifications\RepresentativeToAllNotification;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class RepresentativeService
{
    use PaginateResourceTrait;
    protected $assetService;
    public function __construct(AssetsService $assetService)
    {
        $this->assetService = $assetService;
    }

    /**
     * Get list of representatives by admin
     * @param array $data
     * @return array
     */
    public function getList(array $data)
    {
        $representatives = Cache::remember("representatives", 1200, function () use ($data) {
            return Representative::filter($data)->paginate($data['per_page'] ?? 10);
        });

        if ($representatives->isEmpty()) {
            return [
                'status' => false,
                'msg' => 'Not Found Any Representative!',
                'code' => 404
            ];
        }

        return [
            'status' => true,
            'representatives' => $this->formatPagination($representatives, RepresentativeResource::class, 'representatives')
        ];
    }

    /**
     * Reply notification response to user and lawyer
     * @param array $data
     * @return array
     */
    public function sendResponse(array $data)
    {
        $agency = Cache::remember('agency' . $data['agency_id'], 600, function () use ($data) {
            return Agency::find($data['agency_id']);
        });

        $user = Cache::remember('user' . $agency->user_id, 600, function () use ($agency) {
            return User::find($agency->user_id);
        });

        $lawyer = Cache::remember('lawyer' . $agency->lawyer_id, 600, function () use ($agency) {
            return Lawyer::find($agency->lawyer_id);
        });

        try {
            DB::beginTransaction();
            $agency->sequential_number = $data['sequential_number'];
            $agency->record_number = $data['record_number'];
            $agency->place_of_issue = $data['place_of_issue'];
            $agency->status = $data['status'];

            if ($data['status'] === 'approved') {
                $agency->is_active = true;
            }

            $agency->save();
            Notification::send([$user, $lawyer], new RepresentativeToAllNotification($agency));
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
     * register representative
     * @param array $data
     * @return array
     */
    public function signupRepresentative(array $data)
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
            $representative = Representative::create($data);

            // تعيين الدور
            if (method_exists($representative, 'role')) {
                $representative->role()->create([
                    'name' => 'representative'
                ]);
            } else {
                throw new Exception("Role relationship not defined in Representative model.");
            }

            // إرسال بريد إلكتروني للتحقق (معلق في الكود)
            // Mail::to($representative->email)->send(new VerifyCodeMail($representative));

            // تسجيل الدخول وتوليد التوكن
            $credentials = ['email' => $data['email'], 'password' => $plainPassword]; // استخدم كلمة المرور الأصلية هنا
            if (!$access_token = Auth::guard('representative')->attempt($credentials)) {
                throw new Exception('Failed to generate token');
            }

            // توليد Refresh Token
            $refresh_token = JWTAuth::customClaims(['refresh' => true])->fromUser($representative);

            DB::commit();

            // إزالة الكاش (إذا تم تخزين المستخدمين في الكاش)
            Cache::forget('representatives');

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
        if (!$access_token = Auth::guard('representative')->attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return [
                'status' => false,
                'msg' => 'Email or password is incorrect!',
                'code' => 401
            ];
        }

        // استرجاع المستخدم المصادق عليه
        $representative = Auth::guard('representative')->user();
        if (!$representative) {
            return [
                'status' => false,
                'msg' => 'Representative not found!',
                'code' => 404
            ];
        }

        // التحقق من دور المستخدم
        if ($representative->role->name !== 'representative') {
            return [
                'status' => false,
                'msg' => 'Does not have representative privileges!',
                'code' => 403
            ];
        }

        // إنشاء Refresh Token
        $refresh_token = JWTAuth::customClaims(['refresh' => true])->fromUser($representative);

        return [
            'status' => true,
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
        ];
    }

    /**
     * Update representative info by employee
     * @param array $data
     * @param \App\Models\Representative $representative
     * @return array
     */
    public function update(array $data, Representative $representative)
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
            $representative->update($filteredData);

            if ($data['avatar']) {
                $avatarResponse = $this->assetService->storeImage($data['avatar']);
                $representative->avatar = $avatarResponse['url'];
                $representative->save();
            }

            Cache::forget('representative' . $representative->id);
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
     * Delete representative account by employee
     * @param \App\Models\Representative $representative
     * @return array
     */
    public function destroy(Representative $representative)
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
            $representative->delete();
            Cache::forget('representative' . $representative->id);
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
}
