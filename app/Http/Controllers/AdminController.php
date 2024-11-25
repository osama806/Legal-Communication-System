<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RegisterAdminRequest;
use App\Http\Requests\Admin\RegisterEmployeeRequest;
use App\Http\Requests\Admin\RegisterUserRequest;
use App\Http\Requests\Admin\RegisterLawyerRequest;
use App\Http\Requests\Admin\RegisterRepresentativeRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\LawyerResource;
use App\Http\Resources\RepresentativeResource;
use App\Http\Resources\UserResource;
use App\Models\Lawyer;
use App\Models\Representative;
use App\Models\User;
use App\Services\AdminService;
use App\Traits\ResponseTrait;
use Auth;

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
            ? $this->getResponse("token", $response['token'], 201)
            : $this->getResponse("error", $response['msg'], $response['code']);
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

        // Check if the user is null or does not have the 'admin' role
        if (!$role_user || !$role_user->hasRole('admin')) {
            return $this->getResponse('error', 'Does not have admin privileges!', 403);
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
     * Create new user
     * @param \App\Http\Requests\Admin\RegisterUserRequest $registerUserRequest
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function registerUser(RegisterUserRequest $registerUserRequest)
    {
        $response = $this->adminService->signupUser($registerUserRequest->validated());
        return $response['status']
            ? $this->getResponse("token", $response['token'], 201)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    /**
     * Get list of users by admin
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getUsers()
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user()->hasRole('admin')) {
            return $this->getResponse('error', 'This action is unauthorized', 422);
        }

        $users = User::whereHas('role', function ($query) {
            $query->where('name', 'user');
        })->get();

        return $this->getResponse("users", UserResource::collection($users), 200);
    }

    /**
     * Get user info by admin
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getUser($id)
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user()->hasRole('admin')) {
            return $this->getResponse('error', 'This action is unauthorized', 422);
        }

        $user = User::where('id', $id)->whereHas('role', function ($query) {
            $query->where('name', 'user');
        })->first();

        if (!$user) {
            return $this->getResponse('error', 'User Not Found', 404);
        }
        return $this->getResponse("user", new UserResource($user), 200);
    }

    /**
     * Get account info by admin
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $user = User::where('id', Auth::guard('api')->user()->id)->first();

        if ($user && $user->role->name !== 'admin') {
            return $this->getResponse('error', 'This action is unauthorized', 422);
        }
        return $this->getResponse("profile", new UserResource($user), 200);
    }

    /**
     * Create new employee
     * @param \App\Http\Requests\Admin\RegisterEmployeeRequest $registerRequest
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function registerEmployee(RegisterEmployeeRequest $registerRequest)
    {
        $response = $this->adminService->signupEmployee($registerRequest->validated());
        return $response['status']
            ? $this->getResponse("token", $response['token'], 201)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    /**
     * Get list of employees by admin
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getEmployees()
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user()->hasRole('admin')) {
            return $this->getResponse('error', 'This action is unauthorized', 422);
        }

        $employees = User::whereHas('role', function ($query) {
            $query->where('name', 'employee');
        })->get();

        return $this->getResponse("employees", UserResource::collection($employees), 200);
    }

    /**
     * Get employee info by admin
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getEmployee($id)
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user()->hasRole('admin')) {
            return $this->getResponse('error', 'This action is unauthorized', 422);
        }

        $employee = User::where('id', $id)->whereHas('role', function ($query) {
            $query->where('name', 'employee');
        })->first();

        if (!$employee) {
            return $this->getResponse('error', 'Employee Not Found', 404);
        }
        return $this->getResponse("employee", new UserResource($employee), 200);
    }

    /**
     * Create new lawyer
     * @param \App\Http\Requests\Admin\RegisterLawyerRequest $registerLawyerRequest
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function registerLawyer(RegisterLawyerRequest $registerLawyerRequest)
    {
        $response = $this->adminService->signupLawyer($registerLawyerRequest->validated());
        return $response['status']
            ? $this->getResponse("token", $response['token'], 201)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    /**
     * Display list of lawyers
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getLawyers()
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user()->hasRole('admin')) {
            return $this->getResponse('error', 'This action is unauthorized', 422);
        }
        $lawyers = Lawyer::all();
        return $this->getResponse("lawyers", LawyerResource::collection($lawyers), 200);
    }

    /**
     * Display specified lawyer
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getLawyer($id)
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user()->hasRole('admin')) {
            return $this->getResponse('error', 'This action is unauthorized', 422);
        }
        $lawyer = Lawyer::find($id);
        if (!$lawyer) {
            return $this->getResponse("error", "Lawyer Not Found!", 404);
        }
        return $this->getResponse("lawyer", new LawyerResource($lawyer), 200);
    }

    /**
     * Create new representative
     * @param \App\Http\Requests\Admin\RegisterRepresentativeRequest $registerRepresentativeRequest
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function registerRepresentative(RegisterRepresentativeRequest $registerRepresentativeRequest)
    {
        $response = $this->adminService->signupRepresentative($registerRepresentativeRequest->validated());
        return $response['status']
            ? $this->getResponse("token", $response['token'], 201)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    /**
     * Get list of representatives
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getRepresentatives()
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user()->hasRole('admin')) {
            return $this->getResponse('error', 'This action is unauthorized', 422);
        }
        $representatives = Representative::all();
        return $this->getResponse("representatives", RepresentativeResource::collection($representatives), 200);
    }

    /**
     * Get representative info
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getRepresentative($id)
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user()->hasRole('admin')) {
            return $this->getResponse('error', 'This action is unauthorized', 422);
        }
        $representative = Representative::find($id);
        if (!$representative) {
            return $this->getResponse("error", "Representative Not Found", 404);
        }
        return $this->getResponse("representative", new RepresentativeResource($representative), 200);
    }
}
