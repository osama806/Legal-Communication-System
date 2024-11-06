<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\ChangePasswordFormRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Traits\ResponseTrait;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    use ResponseTrait;
    protected $authService;

    /**
     * Create a new class instance.
     * @param \App\Services\AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Create a new user in storage.
     * @param \App\Http\Requests\Auth\RegisterRequest $registerRequest
     * @return \Illuminate\Http\Response
     */
    public function register(RegisterRequest $registerRequest)
    {
        $validatedData = $registerRequest->validated();
        $response = $this->authService->register($validatedData);
        return $response['status']
            ? $this->getResponse("msg", "User registered successfully", 201)
            : $this->getResponse("msg", $response['msg'], $response['code']);
    }

    /**
     * Check if user authorize or unAuthorize
     * @param \App\Http\Requests\Auth\LoginRequest $authRequest
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $authRequest)
    {
        $validatedData = $authRequest->validated();
        $response = $this->authService->login($validatedData);
        return $response['status']
            ? $this->getResponse("token", $response['token'], 201)
            : $this->getResponse("msg", $response['msg'], $response['code']);
    }

    /**
     * To make logout for user if be authorize
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::parseToken());
            return $this->getResponse("msg", "User logged out successfully", 200);
        } catch (JWTException $e) {
            // throw new JWTException("Failed to logout, please try again", 500);
            return $this->getResponse("msg", "Failed to logout, please try again", 500);
        }
    }

    /**
     * Change password
     * @param \App\Http\Requests\Auth\ChangePasswordFormRequest $changePasswordFormRequest
     * @return \Illuminate\Http\Response
     */
    public function changePassword(ChangePasswordFormRequest $changePasswordFormRequest)
    {
        $validatedData = $changePasswordFormRequest->validated();
        $response = $this->authService->changePassword($validatedData);
        return $response['status']
            ? $this->getResponse('msg', 'Changed password successfully', 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }

    /**
     * Get user profile data
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $response = $this->authService->show();
        return $response['status']
            ? $this->getResponse("profile", $response['profile'], 200)
            : $this->getResponse("msg", "There is error in server", 500);
    }

    /**
     * Update user profile in storage
     * @param \App\Http\Requests\Auth\UpdateProfileRequest $updateProfileRequest
     * @return \Illuminate\Http\Response
     */
    public function updateProfile(UpdateProfileRequest $updateProfileRequest)
    {
        $validatedData = $updateProfileRequest->validated();
        $response = $this->authService->updateProfile($validatedData);
        return $response['status']
            ? $this->getResponse("msg", "User updated profile successfully", 200)
            : $this->getResponse("msg", $response['msg'], $response['code']);
    }

    /**
     * Delete user from storage.
     * @return \Illuminate\Http\Response
     */
    public function deleteUser()
    {
        $response = $this->authService->deleteUser();
        return $response['status']
            ? $this->getResponse("msg", "Deleted user successfully", 200)
            : $this->getResponse("msg", $response['msg'], $response['code']);
    }
}
