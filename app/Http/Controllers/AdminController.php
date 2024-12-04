<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RegisterAdminRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Http\Services\AdminService;
use App\Traits\ResponseTrait;
use Auth;
use Cache;

class AdminController extends Controller
{
    use ResponseTrait;
    protected $adminService;
    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * Create new admin
     * @param \App\Http\Requests\Admin\RegisterAdminRequest $registerRequest
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function signup(RegisterAdminRequest $registerRequest)
    {
        $response = $this->adminService->store($registerRequest->validated());
        return $response['status']
            ? $this->tokenResponse($response['access_token'], $response['refresh_token'], 'admin')
            : $this->success("error", $response['msg'], $response['code']);
    }

    /**
     * Get account info by admin
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $user = Cache::remember('user' . Auth::guard('api')->id(), 600, function () {
            return User::where('id', Auth::guard('api')->id())->first();
        });

        if ($user && $user->role->name !== 'admin') {
            return $this->error('This action is unauthorized', 422);
        }

        return $this->success("profile", new UserResource($user), 200);
    }
}
