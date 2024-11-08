<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordFormRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Employee\UpdateUserInfoRequest;
use App\Http\Requests\User\RegisterUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\User\UserService;
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

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return $this->getResponse('error', 'Email or password is incorrect!', 401);
        }

        return $this->getResponse('token', $token, 201);
    }

    public function logout()
    {
        Auth::guard('api')->logout();
        return $this->getResponse('msg', 'Successfully logged out', 200);
    }

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

    public function index()
    {
        $users = User::all();
        return $this->getResponse("users", UserResource::collection($users), 200);
    }

    public function store(RegisterUserRequest $registerUserRequest)
    {
        $response = $this->userService->register($registerUserRequest->validated());
        return $response['status']
            ? $this->getResponse("msg", "User registered successfully", 201)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->getResponse('error', 'User Not Found', 404);
        }
        return $this->getResponse("profile", new UserResource($user), 200);
    }

    public function update(UpdateProfileRequest $updateProfileRequest)
    {
        $response = $this->userService->updateProfile($updateProfileRequest->validated());
        return $response['status']
            ? $this->getResponse("msg", "User updated profile successfully", 200)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    public function destroy()
    {
        $response = $this->userService->deleteAccount();
        return $response['status']
            ? $this->getResponse('msg', 'Deleted Account Successfully', 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }

    public function updateByEmployee(UpdateUserInfoRequest $updateUserInfoRequest, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->getResponse('error', 'User Not Found!', 404);
        }
       

        $response = $this->userService->updateUserProfileByEmployee($updateUserInfoRequest->validated(), $user);
        return $response['status']
            ? $this->getResponse("msg", "User updated profile successfully", 200)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    public function destroyByEmployee($id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->getResponse('error', 'User Not Found!', 404);
        }

        $response = $this->userService->deleteUserAccountByEmployee($user);
        return $response['status']
            ? $this->getResponse('msg', 'Deleted Account Successfully', 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }

}
