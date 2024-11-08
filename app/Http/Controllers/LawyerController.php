<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Employee\UpdateLawyerInfoRequest;
use App\Http\Resources\LawyerResource;
use App\Models\Lawyer;
use App\Services\Lawyer\LawyerService;
use App\Traits\ResponseTrait;
use Auth;
use Illuminate\Http\Request;

class LawyerController extends Controller
{
    use ResponseTrait;

    protected $lawyerService;
    public function __construct(LawyerService $lawyerService)
    {
        $this->lawyerService = $lawyerService;
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('lawyer')->attempt($credentials)) {
            return $this->getResponse('error', 'Email or password is incorrect!', 401);
        }

        return $this->getResponse('token', $token, 201);
    }

    public function logout()
    {
        Auth::guard('lawyer')->logout();
        return $this->getResponse('msg', 'Successfully logged out', 200);
    }

    public function index()
    {
        $lawyers = Lawyer::all();
        return $this->getResponse("lawyers", LawyerResource::collection($lawyers), 200);
    }

    public function show($id)
    {
        $lawyer = Lawyer::find($id);
        if (!$lawyer) {
            return $this->getResponse("error", "Lawyer Not Found!", 404);
        }
        return $this->getResponse("lawyer", new LawyerResource($lawyer), 200);
    }

    public function updateByEmployee(UpdateLawyerInfoRequest $updateLawyerRequest, $id)
    {
        $lawyer = Lawyer::find($id);
        if (!$lawyer) {
            return $this->getResponse('error', 'Lawyer Not Found!', 404);
        }
        $response = $this->lawyerService->update($updateLawyerRequest->validated(), $lawyer);
        return $response['status']
            ? $this->getResponse("msg", "Lawyer updated profile successfully", 200)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    public function destroyByEmployee($id)
    {
        $lawyer = Lawyer::find($id);
        if (!$lawyer) {
            return $this->getResponse('error', 'Lawyer Not Found!', 404);
        }

        $response = $this->lawyerService->destroy($lawyer);
        return $response['status']
            ? $this->getResponse('msg', 'Deleted Account Successfully', 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }
}
