<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Services\AuthService;
use App\Models\Lawyer;
use App\Models\Representative;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    use ResponseTrait;
    protected $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Login admin, employee, lawyer and representative
     * @param \App\Http\Requests\Auth\LoginRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        $response = $this->authService->authenticate($request->validated());
        return $response['status']
            ? $this->tokenResponse($response['access_token'], $response['refresh_token'], $response['role'])
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Logout admin, employee, lawyer and representative
     * @param \App\Http\Requests\Auth\LogoutRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function logout(LogoutRequest $request)
    {
        $response = $this->authService->signout($request->validated());
        return $response['status']
            ? $this->logoutResponse($response['role'])
            : $this->error($response['msg'], $response['code']);
    }
}
