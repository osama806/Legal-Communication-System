<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordFormRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Employee\UpdateUserInfoRequest;
use App\Http\Requests\User\FilterForEmployeeRequest;
use App\Http\Requests\User\IndexFilterRequest;
use App\Http\Requests\User\RegisterUserRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Http\Services\UserService;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Traits\ResponseTrait;
use Auth;
use Cache;

class UserController extends Controller
{
    use ResponseTrait;
    protected $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Login
     * @param \App\Http\Requests\Auth\LoginRequest $request
     * @return mixed|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        $response = $this->userService->login($request->validated());
        return $response['status']
            ? $this->tokenResponse($response['access_token'], $response['refresh_token'], 'user')
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Logout
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function logout()
    {
        if (Auth::guard('api')->check() && Auth::guard('api')->user()->hasRole('user')) {
            Auth::guard('api')->logout();
            return $this->logoutResponse('user');
        } else
            return $this->error('This action is unauthorized', 422);
    }

    /**
     * Change password by user
     * @param \App\Http\Requests\Auth\ChangePasswordFormRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function changePassword(ChangePasswordFormRequest $request)
    {
        $response = $this->userService->updatePassword($request->validated());
        return $response['status']
            ? $this->success('msg', 'Changed password successfully', 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Get list of users by admin
     * @param \App\Http\Requests\User\IndexFilterRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index(IndexFilterRequest $request)
    {
        $response = $this->userService->getList($request->validated());
        return $response['status']
            ? $this->success('users', $response['users'], 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Create new user
     * @param \App\Http\Requests\User\RegisterUserRequest $registerUserRequest
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(RegisterUserRequest $registerUserRequest)
    {
        $response = $this->userService->register($registerUserRequest->validated());
        return $response['status']
            ? $this->tokenResponse($response['access_token'], $response['refresh_token'], 'user')
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Get account info
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $user = Cache::remember('user_' . Auth::guard('api')->id(), 600, function () {
            return User::find(Auth::guard('api')->id());
        });
        if ($user && !$user->hasRole('user')) {
            return $this->error('This action is unauthorized', 422);
        }

        return $this->success("profile", new UserResource($user), 200);
    }

    /**
     * Update account info by owned
     * @param \App\Http\Requests\Auth\UpdateProfileRequest $updateProfileRequest
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateProfileRequest $updateProfileRequest)
    {
        $response = $this->userService->updateProfile($updateProfileRequest->validated());
        return $response['status']
            ? $this->success("msg", "User updated profile successfully", 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Delete account by owned
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy()
    {
        $response = $this->userService->deleteAccount();
        return $response['status']
            ? $this->success('msg', 'Deleted Account Successfully', 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Get list of notifications
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getNotifications()
    {
        $user = Auth::guard('api')->user();
        return $this->success('notifications', NotificationResource::collection($user->notifications), 200);
    }

    /**
     * Create new user by admin
     * @param \App\Http\Requests\Admin\RegisterUserRequest $registerUserRequest
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function registerUser(RegisterUserRequest $registerUserRequest)
    {
        $response = $this->userService->signupUser($registerUserRequest->validated());
        return $response['status']
            ? $this->tokenResponse($response['access_token'], $response['refresh_token'], 'user')
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Get user info by admin
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $response = $this->userService->fetchOne($id);
        return $response['status']
            ? $this->success('user', new UserResource($response['user']), 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Update user account by employee
     * @param \App\Http\Requests\Employee\UpdateUserInfoRequest $request
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function updateUser(UpdateUserInfoRequest $request, $id)
    {
        $user = Cache::remember('user_' . $id, 600, function () use ($id) {
            return User::find($id);
        });
        if (!$user) {
            return $this->error('User Not Found!', 404);
        }

        $response = $this->userService->updateUser($request->validated(), $user);
        return $response['status']
            ? $this->success("msg", "Updated user profile successfully", 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Delete user account by employee
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroyUser($id)
    {
        $user = Cache::remember('user_' . $id, 600, function () use ($id) {
            return User::find($id);
        });
        if (!$user) {
            return $this->error('User Not Found!', 404);
        }

        $response = $this->userService->deleteUser($user);
        return $response['status']
            ? $this->success('msg', 'Deleted Account Successfully', 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Get list of users forward to employee
     * @param \App\Http\Requests\User\FilterForEmployeeRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function indexForEmployee(FilterForEmployeeRequest $request)
    {
        $response = $this->userService->getList($request->validated());
        return $response['status']
            ? $this->success('users', $response['users'], 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Get one user by employee
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function showForEmployee($id)
    {
        $response = $this->userService->fetchOneForEmployee($id);
        return $response['status']
            ? $this->success('user', new UserResource($response['user']), 200)
            : $this->error($response['msg'], $response['code']);
    }
}
