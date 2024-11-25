<?php

namespace App\Http\Controllers;

use App\Http\Requests\Agency\StoreLawyerForAgencyRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Employee\UpdateLawyerInfoRequest;
use App\Http\Resources\LawyerResource;
use App\Http\Resources\NotificationResource;
use App\Models\Lawyer;
use App\Models\Representative;
use App\Services\LawyerService;
use App\Traits\ResponseTrait;
use Auth;

class LawyerController extends Controller
{
    use ResponseTrait;

    protected $lawyerService;
    public function __construct(LawyerService $lawyerService)
    {
        $this->lawyerService = $lawyerService;
    }

    /**
     * Lawyer login
     * @param \App\Http\Requests\Auth\LoginRequest $request
     * @return mixed|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('lawyer')->attempt($credentials)) {
            return $this->getResponse('error', 'Email or password is incorrect!', 401);
        }

        return response([
            "isSuccess" => true,
            'token' => $token,
            'role' => "lawyer"
        ], 201);
    }

    /**
     * Lawyer logout
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::guard('lawyer')->logout();
        return $this->getResponse('msg', 'Successfully logged out', 200);
    }

    /**
     * Get account info
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $lawyer = Lawyer::where('id', Auth::guard('lawyer')->user()->id)->first();

        if ($lawyer && $lawyer->role->name !== 'lawyer') {
            return $this->getResponse('error', 'This action is unauthorized', 422);
        }

        return $this->getResponse("profile", new LawyerResource($lawyer), 200);
    }

    /**
     * Send notification to representative
     * @param \App\Http\Requests\Agency\StoreLawyerForAgencyRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function sendNotificationToRep(StoreLawyerForAgencyRequest $request)
    {
        $representative = Representative::find($request['representative_id']);
        $response = $this->lawyerService->send($request->validated());
        return $response['status']
            ? $this->getResponse('msg', 'Send request to representative ' . $representative->name, 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }

    /**
     * Get list of notifications
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getNotifications()
    {
        $lawyer = Auth::guard('lawyer')->user();
        return $this->getResponse('notifications', NotificationResource::collection($lawyer->notifications), 200);
    }

    /**
     * Get list of lawyers forward to AI
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function lawyersAI()
    {
        $lawyers = Lawyer::all();
        return $this->getResponse('lawyers', LawyerResource::collection($lawyers), 200);
    }
}
