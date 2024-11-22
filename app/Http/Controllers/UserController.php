<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agency\StoreAgencyRequest;
use App\Http\Requests\Auth\ChangePasswordFormRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Employee\UpdateUserInfoRequest;
use App\Http\Requests\User\RegisterUserRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\UserResource;
use App\Models\Agency;
use App\Models\Lawyer;
use App\Models\User;
use App\Services\UserService;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Traits\ResponseTrait;
use Auth;
use Hash;

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
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return $this->getResponse('error', 'Email or password is incorrect!', 401);
        }

        // Get the authenticated user
        $role_user = Auth::guard('api')->user();

        // Check if the user is null or does not have the 'user' role
        if (!$role_user || !$role_user->hasRole('user')) {
            return $this->getResponse('error', 'Does not have user privileges!', 403);
        }

        return response([
            "isSuccess" => true,
            'token' => $token,
            'role' => $role_user->role->name
        ], 201);
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
     * Change password by owned
     * @param \App\Http\Requests\Auth\ChangePasswordFormRequest $changePasswordFormRequest
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function changePassword(ChangePasswordFormRequest $changePasswordFormRequest)
    {
        $user = Auth::user();
        $validatedData = $changePasswordFormRequest->validated();

        // Check if the current password matches
        if (!Hash::check($validatedData['current_password'], $user->password)) {
            return $this->getResponse('error', 'The current password is incorrect.', 400);
        }

        // Update the user's password
        $user->password = Hash::make($validatedData['new_password']);
        $user->save();
        return $this->getResponse('msg', 'Changed password successfully', 200);
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
     * Update account info owned
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
     * Delete account owned
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
     * Agency request to lawyer by user
     * @param \App\Http\Requests\Agency\StoreAgencyRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function agencyRequest(StoreAgencyRequest $request)
    {
        $response = $this->userService->createAgency($request->validated());
        $lawyer = Lawyer::find($request['lawyer_id']);
        return $response['status']
            ? $this->getResponse('msg', 'Send request to lawyer ' . $lawyer->name, 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }

    /**
     * Agency isolate by user
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function agencyIsolate($id)
    {
        $agency = Agency::where('id', $id)->where('user_id', Auth::guard('api')->id())->first();
        if (!$agency) {
            return $this->getResponse('error', 'Agency Not Found', 404);
        }

        if (!$agency->is_active && $agency->status === 'pending') {
            return $this->getResponse('error', 'Agency Not Found', 404);
        }

        if (!$agency->is_active || $agency->status !== 'approved') {
            return $this->getResponse('error', 'Agency is Expired', 403);
        }

        $agency->is_active = false;
        $agency->save();
        return $this->getResponse('msg', 'Agency Isolated Successfully', 200);
    }

    /**
     * Get list of users forward to AI
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function usersAI()
    {
        $users = User::whereHas('role', function ($query) {
            $query->where('name', 'user');
        })->get();

        return $this->getResponse('users', UserResource::collection($users), 200);
    }
}
