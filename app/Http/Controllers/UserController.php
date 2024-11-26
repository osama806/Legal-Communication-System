<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordFormRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Employee\UpdateUserInfoRequest;
use App\Http\Requests\User\RegisterUserRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Http\Services\UserService;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Traits\ResponseTrait;
use Auth;

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
            ? response([
                "isSuccess" => true,
                'token' => $response['token'],
                'role' => $response['role']
            ], 201)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }

    /**
     * Logout
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::guard('api')->logout();
        return $this->getResponse('msg', 'Successfully logged out', 200);
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
            ? $this->getResponse('msg', 'Changed password successfully', 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
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
            ? $this->getResponse("token", $response['token'], 201)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    /**
     * Get account info
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $user = User::where('id', Auth::guard('api')->user()->id)->first();
        if ($user && $user->role->name !== 'user') {
            return $this->getResponse('error', 'This action is unauthorized', 422);
        }

        return $this->getResponse("profile", new UserResource($user), 200);
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
            ? $this->getResponse("msg", "User updated profile successfully", 200)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    /**
     * Delete account by owned
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy()
    {
        $response = $this->userService->deleteAccount();
        return $response['status']
            ? $this->getResponse('msg', 'Deleted Account Successfully', 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }

    /**
     * Get list of notifications
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getNotifications()
    {
        $user = Auth::guard('api')->user();
        return $this->getResponse('notifications', NotificationResource::collection($user->notifications), 200);
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
            ? $this->getResponse("token", $response['token'], 201)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    /**
     * Get list of users by admin
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $response = $this->userService->fetchAll();
        return $response['status']
            ? $this->getResponse('users', UserResource::collection($response['users']), 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
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
            ? $this->getResponse('user', new UserResource($response['user']), 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }

    /**
     * Update user account by employee
     * @param \App\Http\Requests\Employee\UpdateUserInfoRequest $updateUserInfoRequest
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function updateUser(UpdateUserInfoRequest $updateUserInfoRequest, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->getResponse('error', 'User Not Found!', 404);
        }
        $response = $this->userService->updateUser($updateUserInfoRequest->validated(), $user);

        return $response['status']
            ? $this->getResponse("msg", "Updated user profile successfully", 200)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    /**
     * Delete user account by employee
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroyUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->getResponse('error', 'User Not Found!', 404);
        }
        $response = $this->userService->deleteUser($user);

        return $response['status']
            ? $this->getResponse('msg', 'Deleted Account Successfully', 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }
}
