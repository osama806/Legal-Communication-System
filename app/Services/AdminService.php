<?php

namespace App\Services;

use App\Models\User;
use App\Models\Lawyer;
use App\Models\Representative;
use Illuminate\Support\Facades\DB;
use Auth;
use Exception;
use Hash;

class AdminService
{
    protected $assetService;
    public function __construct(AssetsService $assetService)
    {
        $this->assetService = $assetService;
    }

    /**
     * Admin login
     * @param array $data
     * @return array
     */
    public function store(array $data)
    {
        if (strpos($data['email'], '@admin') === false) {
            return [
                'status' => false,
                'msg' => 'Email address must contains mark @admin',
                'code' => 400
            ];
        }
        $avatarResponse = $this->assetService->storeImage($data['avatar']);

        try {
            DB::beginTransaction();
            $admin = User::create($data);
            $admin->password = Hash::make($data["password"]);
            $admin->avatar = $avatarResponse['url'];
            $admin->save();

            $admin->role()->create([
                'name' => 'admin'
            ]);

            // تسجيل الدخول وتوليد التوكن
            $credentials = ['email' => $data['email'], 'password' => $data['password']];
            if (!$token = Auth::guard('api')->attempt($credentials)) {
                return [
                    'status' => false,
                    'msg' => 'Failed to generate token, but admin registered successfully',
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
            return [
                'status' => false,
                'msg' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * register user
     * @param array $data
     * @return array
     */
    public function signupUser(array $data)
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
            return [
                'status' => false,
                'msg' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * register employee
     * @param array $data
     * @return array
     */
    public function signupEmployee(array $data)
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
                'token' => $token
            ];

        } catch (Exception $e) {
            DB::rollBack();
            return ['status' => false, 'msg' => $e->getMessage(), 'code' => 500];
        }
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
                'created_at'    =>  now(),
                'updated_at'    =>  now(),
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
}
