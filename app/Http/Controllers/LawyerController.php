<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\RegisterLawyerRequest;
use App\Http\Requests\Agency\StoreLawyerForAgencyRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Employee\UpdateLawyerInfoRequest;
use App\Http\Resources\LawyerResource;
use App\Http\Resources\NotificationResource;
use App\Models\Lawyer;
use App\Models\Representative;
use App\Http\Services\LawyerService;
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
     * Create new lawyer by admin
     * @param \App\Http\Requests\Admin\RegisterLawyerRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(RegisterLawyerRequest $request)
    {
        $response = $this->lawyerService->signupLawyer($request->validated());
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
     * Logout
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
     * Agency request is accepted & send notification to representative
     * @param \App\Http\Requests\Agency\StoreLawyerForAgencyRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function agencyAccepted(StoreLawyerForAgencyRequest $request)
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
     * Display list of lawyers by admin
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user()->hasRole('admin')) {
            return $this->getResponse('error', 'This action is unauthorized', 422);
        }
        $lawyers = Lawyer::all();

        return $this->getResponse("lawyers", LawyerResource::collection($lawyers), 200);
    }

    /**
     * Display specified lawyer by admin
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show($id)
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
     * Update lawyer info by employee
     * @param \App\Http\Requests\Employee\UpdateLawyerInfoRequest $request
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateLawyerInfoRequest $request, $id)
    {
        $lawyer = Lawyer::find($id);
        if (!$lawyer) {
            return $this->getResponse('error', 'Lawyer Not Found!', 404);
        }
        $response = $this->lawyerService->update($request->validated(), $lawyer);
        return $response['status']
            ? $this->getResponse("msg", "Lawyer updated profile successfully", 200)
            : $this->getResponse("error", $response['msg'], $response['code']);
    }

    /**
     * Delete lawyer account by employee
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy($id)
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

    /**
     * Display list of lawyers by user
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function indexForUser()
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user()->hasRole('user')) {
            return $this->getResponse('error', 'This action is unauthorized', 422);
        }
        $lawyers = Lawyer::all();

        return $this->getResponse("lawyers", LawyerResource::collection($lawyers), 200);
    }

    /**
     * Display specified lawyer by user
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function showForUser($id)
    {
        $response = $this->lawyerService->fetchOne($id);
        return $response['status']
            ? $this->getResponse('lawyer', new LawyerResource($response['lawyer']), 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
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
