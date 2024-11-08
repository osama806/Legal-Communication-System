<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RegisterRequest;
use App\Http\Requests\Admin\RegisterLawyerRequest;
use App\Http\Requests\Admin\RegisterRepresentativeRequest;
use App\Services\Admin\AdminService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Log;

class AdminController extends Controller
{
    use ResponseTrait;
    protected $adminService;
    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function store(RegisterRequest $registerRequest)
    {
        $response = $this->adminService->store($registerRequest->validated());

        return $response['status']
            ? $this->getResponse("msg", "Created Admin Successfully", 201)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    public function registerEmployee(RegisterRequest $registerRequest)
    {
        $response = $this->adminService->signupEmployee($registerRequest->validated());
        return $response['status']
            ? $this->getResponse("msg", "Created Employee Successfully", 201)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    public function registerLawyer(RegisterLawyerRequest $registerLawyerRequest)
    {
        $response = $this->adminService->signupLawyer($registerLawyerRequest->validated());
        return $response['status']
            ? $this->getResponse("msg", "Created Lawyer Successfully", 201)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    public function registerRepresentative(RegisterRepresentativeRequest $registerRepresentativeRequest)
    {
        $response = $this->adminService->signupRepresentative($registerRepresentativeRequest->validated());
        return $response['status']
            ? $this->getResponse("msg", "Created Representative Successfully", 201)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    public function registerUser(RegisterRequest $registerUserRequest)
    {
        $response = $this->adminService->signupUser($registerUserRequest->validated());
        return $response['status']
            ? $this->getResponse("msg", "Created User Successfully", 201)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }
}
