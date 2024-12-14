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
            $avatarResponse = $this->assetService->storeImage($data['avatar']);
            DB::beginTransaction();

            $plainPassword = $data['password'];
            $data['password'] = Hash::make($plainPassword);
            $data['avatar'] = $avatarResponse['url'];

            $lawyer = Lawyer::create($data);
            $lawyer->specializations()->attach($data['specialization_Ids'], [
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if (method_exists($lawyer, 'role')) {
                $lawyer->role()->create([
                    'name' => 'lawyer'
                ]);
            } else {
                throw new Exception("Role relationship not defined in Lawyer model.");
            }

            // Mail::to($lawyer->email)->send(new VerifyCodeMail($lawyer));

            $credentials = ['email' => $data['email'], 'password' => $plainPassword]; // استخدم كلمة المرور الأصلية هنا
            if (!$access_token = Auth::guard('lawyer')->attempt($credentials)) {
                throw new Exception('Failed to generate token');
            }

            $refresh_token = JWTAuth::customClaims(['refresh' => true])->fromUser($lawyer);
            DB::commit();
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
     * Update agency record and send notification to representative
     * @param array $data
     * @return array
     */
    public function send(array $data)
    {
        $agency = Cache::remember('agency_' . $data['agency_id'], 600, function () use ($data) {
            return Agency::find($data['agency_id']);
        });

        $representative = Cache::remember('representative_' . $data['representative_id'], 600, function () use ($data) {
            return Representative::find($data['representative_id']);
        });

        if ($agency->representative_id !== null || $agency->type !== null) {
            return [
                'status' => false,
                'msg' => 'You Send Notification Already!',
                'code' => 403
            ];
        }

        try {
            DB::beginTransaction();
            $agency->representative_id = $data['representative_id'];
            $agency->type = $data['type'];
            $agency->authorizations()->attach($data['authorization_Ids'], [
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $agency->exceptions()->attach($data['exception_Ids'], [
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $agency->save();

            Notification::send($representative, new LawyerToRepresentativeNotification($agency));
            DB::commit();
            Cache::forget('agency_' . $agency->id);
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
        if (!Auth::guard('api')->user()->hasRole('employee')) {
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
}
