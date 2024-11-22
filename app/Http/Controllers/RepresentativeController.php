<?php

namespace App\Http\Controllers;

use App\Http\Requests\Agency\StoreRepresentativeForAgencyRequest;
use App\Http\Requests\Auth\LoginRequest;
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
     * Representative login
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
     * Representative logout
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
}
