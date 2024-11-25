<?php

namespace App\Services;

use App\Models\Agency;
use App\Models\Lawyer;
use App\Notifications\UserToLawyerNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Mail;
use Exception;
use App\Models\User;
use App\Traits\ResponseTrait;
use Notification;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class UserService
{
    use ResponseTrait;

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
        } catch (\Exception $e) {
            Log::error('Error deleting account: ' . $e->getMessage());
            return ['status' => false, 'msg' => $e->getMessage(), 'code' => 500];
        }
    }

    /**
     * Create new agency request with send notification to lawyer
     * @param array $data
     * @return array
     */
    public function createAgency(array $data)
    {
        $lawyer = Lawyer::find($data["lawyer_id"]);
        try {
            // تحقق من عدد الطلبات التي قام بها المستخدم في اليوم الحالي
            $userId = Auth::guard('api')->id();
            $todayRequestsCount = Agency::where('user_id', $userId)->where('lawyer_id', $lawyer->id)
                ->whereDate('created_at', Carbon::today())
                ->count();

            if ($todayRequestsCount >= 3) {
                // إذا تجاوز المستخدم ثلاثة طلبات في نفس اليوم، قم بإرجاع رسالة
                return [
                    'status' => false,
                    'msg' => "You have exceeded your limit for requesting agencies today. Please try again tomorrow.",
                    'code' => 403
                ];
            }

            DB::beginTransaction();
            $agency = Agency::firstOrCreate([
                "user_id" => Auth::guard('api')->id(),
                "lawyer_id" => $data["lawyer_id"],
                "cause" => $data['cause']
            ]);

            Notification::send($lawyer, new UserToLawyerNotification($agency));
            DB::commit();

            return [
                'status' => true,
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
}
