<?php

namespace App\Http\Services;

use Auth;
use Hash;
use Illuminate\Support\Facades\DB;
use Exception;
use Notification;
use App\Models\Agency;
use App\Models\Lawyer;
use App\Models\Representative;
use App\Notifications\LawyerToRepresentativeNotification;

class LawyerService
{
    protected $assetService;
    public function __construct(AssetsService $assetService)
    {
        $this->assetService = $assetService;
    }

    /**
     * register lawyer
     * @param array $data
     * @return array
     */
    public function signupLawyer(array $data)
    {
        $avatarResponse = $this->assetService->storeImage($data['avatar']);
        try {
            DB::beginTransaction();
            $lawyer = Lawyer::create($data);
            $lawyer->password = Hash::make($data["password"]);
            $lawyer->avatar = $avatarResponse['url'];
            $lawyer->save();

            $lawyer->role()->create([
                'name' => 'lawyer'
            ]);

            $lawyer->specializations()->attach($data['specialization_id'], [
                'created_at' => now(),
                'updated_at' => now(),
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
     * Update agency record and send notification to representative
     * @param array $data
     * @return array
     */
    public function send(array $data)
    {
        $agency = Agency::find($data['agency_id']);
        $representative = Representative::find($data['representative_id']);
        try {
            DB::beginTransaction();
            $agency->representative_id = $data['representative_id'];
            $agency->type = $data['type'];
            $agency->authorizations = $data['authorizations'];
            $agency->exceptions = $data['exceptions'];
            $agency->save();
            Notification::send($representative, new LawyerToRepresentativeNotification($agency));
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
     * Get one lawyer
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

        $lawyer = Lawyer::find($id);
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
