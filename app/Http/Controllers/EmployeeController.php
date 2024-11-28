<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\RegisterEmployeeRequest;
use App\Http\Requests\Employee\IndexFilterRequest;
use Auth;
use App\Models\User;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Http\Services\EmployeeService;
use App\Traits\ResponseTrait;
use Cache;

class EmployeeController extends Controller
{
    use ResponseTrait;
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    /**
     * Get list of employees by admin
     * @param \App\Http\Requests\Employee\IndexFilterRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index(IndexFilterRequest $request)
    {
        $response = $this->employeeService->getList($request->validated());
        return $response["status"]
            ? $this->getResponse("employees", $response['employees'], 200)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    /**
     * Create new employee by admin
     * @param \App\Http\Requests\Admin\RegisterEmployeeRequest $registerRequest
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(RegisterEmployeeRequest $registerRequest)
    {
        $response = $this->employeeService->signup($registerRequest->validated());
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
        $response = $this->employeeService->login($request->validated());

        return $response['status']
            ? response([
                "isSuccess" => true,
                'token' => $response['token'],
                'role' => $response['role'],
            ], 201)
            : $this->getResponse('error', $response['msg'], $response['code']);
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
        $user = Cache::remember('user', 3600, function () {
            return User::where('id', Auth::guard('api')->user()->id)->first();
        });
        if ($user && $user->role->name !== 'employee') {
            return $this->getResponse('error', 'This action is unauthorized', 422);
        }

        return $this->getResponse("profile", new UserResource($user), 200);
    }

    /**
     * Get employee info by admin
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user()->hasRole('admin')) {
            return $this->getResponse('error', 'This action is unauthorized', 422);
        }
        $response = $this->employeeService->fetchOne($id);

        return $response['status']
            ? $this->getResponse('employee', new UserResource($response['employee']), 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }
}
