<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\RegisterEmployeeRequest;
use App\Http\Requests\Employee\IndexFilterRequest;
use Auth;
use App\Models\User;
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
            ? $this->success("employees", $response['employees'], 200)
            : $this->error($response['msg'], $response['code']);
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
            ? $this->tokenResponse($response['access_token'], $response['refresh_token'], 'employee')
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Get account info
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $employee = Cache::remember('employee_' . Auth::guard('api')->id(), 600, function () {
            return User::find(Auth::guard('api')->id());
        });
        if ($employee && !$employee->hasRole('employee')) {
            return $this->error('This action is unauthorized', 422);
        }

        return $this->success("profile", new UserResource($employee), 200);
    }

    /**
     * Get employee info by admin
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user()->hasRole('admin')) {
            return $this->error('This action is unauthorized', 422);
        }

        $response = $this->employeeService->fetchOne($id);
        return $response['status']
            ? $this->success('employee', new UserResource($response['employee']), 200)
            : $this->error($response['msg'], $response['code']);
    }
}
