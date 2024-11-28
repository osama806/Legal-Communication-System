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
        $representatives = Cache::remember("representatives", 3600, function () use ($data) {
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
        $agency = Agency::find($data['agency_id']);
        $user = User::find($agency->user_id);
        $lawyer = Lawyer::find($agency->lawyer_id);
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
        $avatarResponse = $this->assetService->storeImage($data['avatar']);
        try {
            DB::beginTransaction();
            $representative = Representative::create($data);
            $representative->password = Hash::make($data["password"]);
            $representative->avatar = $avatarResponse['url'];
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
