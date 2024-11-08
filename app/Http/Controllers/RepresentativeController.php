<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Employee\UpdateRepresentativeInfoRequest;
use App\Http\Resources\RepresentativeResource;
use App\Models\Representative;
use App\Services\Representative\RepresentativeService;
use App\Traits\ResponseTrait;
use Auth;
use Illuminate\Http\Request;

class RepresentativeController extends Controller
{
    use ResponseTrait;

    protected $representativeService;
    public function __construct(RepresentativeService $representativeService)
    {
        $this->representativeService = $representativeService;
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('representative')->attempt($credentials)) {
            return $this->getResponse('error', 'Email or password is incorrect!', 401);
        }

        return $this->getResponse('token', $token, 201);
    }

    public function logout()
    {
        Auth::guard('representative')->logout();
        return $this->getResponse('msg', 'Successfully logged out', 200);
    }

    public function index()
    {
        $representatives = Representative::all();
        return $this->getResponse("representatives", RepresentativeResource::collection($representatives), 200);
    }

    public function show($id)
    {
        $representative = Representative::find($id);
        if (!$representative) {
            return $this->getResponse("error", "Representative Not Found", 404);
        }
        return $this->getResponse("representative", new RepresentativeResource($representative), 200);
    }

    public function updateByEmployee(UpdateRepresentativeInfoRequest $updateRepresentativeRequest, $id)
    {
        $representative = Representative::find($id);
        if (!$representative) {
            return $this->getResponse('error', 'Representative Not Found!', 404);
        }

        $response = $this->representativeService->update($updateRepresentativeRequest->validated(), $representative);
        return $response['status']
            ? $this->getResponse("msg", "Representative updated profile successfully", 200)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    public function destroyByEmployee($id)
    {
        $representative = Representative::find($id);
        if (!$representative) {
            return $this->getResponse('error', 'Representative Not Found!', 404);
        }

        $response = $this->representativeService->destroy($representative);
        return $response['status']
            ? $this->getResponse('msg', 'Deleted Account Successfully', 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }
}
