<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RegisterAdminRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Http\Services\AdminService;
use App\Traits\ResponseTrait;
use Auth;
use Cache;
use Illuminate\Http\Exceptions\HttpResponseException;

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
    public function store(RegisterAdminRequest $registerRequest)
    {
        $response = $this->adminService->register($registerRequest->validated());
        return $response['status']
            ? $this->tokenResponse($response['access_token'], $response['refresh_token'], 'admin')
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Get account info
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $admin = Cache::remember('admin_' . Auth::guard('api')->id(), 600, function () {
            return User::find(Auth::guard('api')->id());
        });

        if ($admin && !$admin->hasRole('admin')) {
            throw new HttpResponseException($this->error('This action is unauthorized', 422));
        }

        return $this->success("profile", new UserResource($admin), 200);
    }
}
