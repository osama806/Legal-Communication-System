<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\User;
use App\Models\Lawyer;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Employee\UpdateLawyerInfoRequest;
use App\Http\Requests\Employee\UpdateRepresentativeInfoRequest;
use App\Http\Requests\Employee\UpdateUserInfoRequest;
use App\Http\Resources\UserResource;
use App\Models\Representative;
use App\Services\EmployeeService;
use App\Traits\ResponseTrait;

class EmployeeController extends Controller
{
    use ResponseTrait;
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    /**
     * Login
     * @param \App\Http\Requests\Auth\LoginRequest $request
     * @return mixed|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function signin(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return $this->getResponse('error', 'Email or password is incorrect!', 401);
        }

        // Get the authenticated user
        $role_user = Auth::guard('api')->user();

        // Check if the user is null or does not have the 'employee' role
        if (!$role_user || !$role_user->hasRole('employee')) {
            return $this->getResponse('error', 'Does not have employee privileges!', 403);
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
    public function signout()
    {
        Auth::guard('api')->logout();
        return $this->getResponse('msg', 'Successfully logged out', 200);
    }

    /**
     * Get account info
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $user = User::where('id', Auth::guard('api')->user()->id)->first();

        if ($user && $user->role->name !== 'employee') {
            return $this->getResponse('error', 'This action is unauthorized', 422);
        }
        return $this->getResponse("profile", new UserResource($user), 200);
    }

    /**
     * Update user account
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

        $response = $this->employeeService->updateUser($updateUserInfoRequest->validated(), $user);
        return $response['status']
            ? $this->getResponse("msg", "Updated user profile successfully", 200)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    /**
     * Delete user account
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroyUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->getResponse('error', 'User Not Found!', 404);
        }

        $response = $this->employeeService->deleteUser($user);
        return $response['status']
            ? $this->getResponse('msg', 'Deleted Account Successfully', 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }

    /**
     * Update lawyer info
     * @param \App\Http\Requests\Employee\UpdateLawyerInfoRequest $updateLawyerRequest
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function updateLawyer(UpdateLawyerInfoRequest $updateLawyerRequest, $id)
    {
        $lawyer = Lawyer::find($id);
        if (!$lawyer) {
            return $this->getResponse('error', 'Lawyer Not Found!', 404);
        }
        $response = $this->employeeService->updateLawyer($updateLawyerRequest->validated(), $lawyer);
        return $response['status']
            ? $this->getResponse("msg", "Lawyer updated profile successfully", 200)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    /**
     * Delete lawyer account
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroyLawyer($id)
    {
        $lawyer = Lawyer::find($id);
        if (!$lawyer) {
            return $this->getResponse('error', 'Lawyer Not Found!', 404);
        }

        $response = $this->employeeService->destroyLawyer($lawyer);
        return $response['status']
            ? $this->getResponse('msg', 'Deleted Account Successfully', 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }

    /**
     * Update representative info
     * @param \App\Http\Requests\Employee\UpdateRepresentativeInfoRequest $updateRepresentativeRequest
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function updateRepresentative(UpdateRepresentativeInfoRequest $updateRepresentativeRequest, $id)
    {
        $representative = Representative::find($id);
        if (!$representative) {
            return $this->getResponse('error', 'Representative Not Found!', 404);
        }

        $response = $this->employeeService->updateRepresentative($updateRepresentativeRequest->validated(), $representative);
        return $response['status']
            ? $this->getResponse("msg", "Representative updated profile successfully", 200)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    /**
     * Delete representative account
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroyRepresentative($id)
    {
        $representative = Representative::find($id);
        if (!$representative) {
            return $this->getResponse('error', 'Representative Not Found!', 404);
        }

        $response = $this->employeeService->destroyRepresentative($representative);
        return $response['status']
            ? $this->getResponse('msg', 'Deleted Account Successfully', 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }

    /**
     * Get employee avatar
     * @param mixed $employeeID
     * @param mixed $avatarID
     * @return mixed|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function getAvatar($employeeID, $avatarID)
    {
        $employee = User::where("id", $employeeID)->where("avatar", $avatarID)->first();
        if (!$employee) {
            return $this->getResponse("error", "Avatar Not Found", 404);
        }

        if (Auth::guard('api')->id() !== $employeeID) {
            return $this->getResponse('error', 'This action is unauthorized', 422);
        }

        $response = $this->employeeService->avatar($avatarID);
        if ($response['status']) {
            // Directly return the image content with headers
            return response($response['avatar'], 200)
                ->header('Content-Type', $response['type']);
        } else {
            return $this->getResponse('error', $response['msg'], $response['code']);
        }
    }
}
