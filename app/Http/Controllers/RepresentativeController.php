<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\RegisterRepresentativeRequest;
use App\Http\Requests\Agency\StoreRepresentativeForAgencyRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Employee\UpdateRepresentativeInfoRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\RepresentativeResource;
use App\Models\Agency;
use App\Models\Representative;
use App\Services\RepresentativeService;
use App\Traits\ResponseTrait;
use Auth;

class RepresentativeController extends Controller
{
    use ResponseTrait;
    protected $representativeService;
    public function __construct(RepresentativeService $representativeService)
    {
        $this->representativeService = $representativeService;
    }

    /**
     * Create new representative by admin
     * @param \App\Http\Requests\Admin\RegisterRepresentativeRequest $registerRepresentativeRequest
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function registerRepresentative(RegisterRepresentativeRequest $registerRepresentativeRequest)
    {
        $response = $this->representativeService->signupRepresentative($registerRepresentativeRequest->validated());
        return $response['status']
            ? $this->getResponse("token", $response['token'], 201)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    /**
     * Login
     * @param \App\Http\Requests\Auth\LoginRequest $request
     * @return mixed|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        if (!$token = Auth::guard('representative')->attempt($credentials)) {
            return $this->getResponse('error', 'Email or password is incorrect!', 401);
        }

        return response([
            "isSuccess" => true,
            'token' => $token,
            'role' => 'representative'
        ], 201);
    }

    /**
     * Logout
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::guard('representative')->logout();
        return $this->getResponse('msg', 'Successfully logged out', 200);
    }

    /**
     * Get account info
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $representative = Representative::where('id', Auth::guard('representative')->user()->id)->first();
        if ($representative && $representative->role->name !== 'representative') {
            return $this->getResponse('error', 'This action is unauthorized', 422);
        }

        return $this->getResponse("profile", new RepresentativeResource($representative), 200);
    }

    /**
     * Send notifications to user and lawyer both
     * @param \App\Http\Requests\Agency\StoreRepresentativeForAgencyRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function sendNotificationsToAll(StoreRepresentativeForAgencyRequest $request)
    {
        $response = $this->representativeService->sendResponse($request->validated());
        $agency = Agency::find($request['agency_id']);

        return $response['status']
            ? $this->getResponse('msg', 'Agency Status is ' . $agency->status, 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }

    /**
     * Get list of notifications
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getNotifications()
    {
        $representative = Auth::guard('representative')->user();
        return $this->getResponse('notifications', NotificationResource::collection($representative->notifications), 200);
    }

    /**
     * Get list of representatives by admin
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
     * Get representative info by admin
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

    /**
     * Update representative info by employee
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
        $response = $this->representativeService->updateRepresentative($updateRepresentativeRequest->validated(), $representative);

        return $response['status']
            ? $this->getResponse("msg", "Representative updated profile successfully", 200)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    /**
     * Delete representative account by employee
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroyRepresentative($id)
    {
        $representative = Representative::find($id);
        if (!$representative) {
            return $this->getResponse('error', 'Representative Not Found!', 404);
        }
        $response = $this->representativeService->destroyRepresentative($representative);

        return $response['status']
            ? $this->getResponse('msg', 'Deleted Account Successfully', 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }
}
