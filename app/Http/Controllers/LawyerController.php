<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\RegisterLawyerRequest;
use App\Http\Requests\Agency\StoreLawyerForAgencyRequest;
use App\Http\Requests\Employee\UpdateLawyerInfoRequest;
use App\Http\Requests\Lawyer\IndexFilterRequest;
use App\Http\Resources\LawyerResource;
use App\Http\Resources\NotificationResource;
use App\Models\Lawyer;
use App\Models\Representative;
use App\Http\Services\LawyerService;
use App\Traits\ResponseTrait;
use Auth;
use Cache;

class LawyerController extends Controller
{
    use ResponseTrait;

    protected $lawyerService;
    public function __construct(LawyerService $lawyerService)
    {
        $this->lawyerService = $lawyerService;
    }

    /**
     * Display list of lawyers
     * @param \App\Http\Requests\Lawyer\IndexFilterRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index(IndexFilterRequest $request)
    {
        $response = $this->lawyerService->getList($request->validated());
        return $response['status']
            ? $this->success('data', $response['lawyers'], 200)
            : $this->error($response['msg'], $response['code']);
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
            ? $this->tokenResponse($response['access_token'], $response['refresh_token'], 'lawyer')
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Display specified lawyer
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if (!Auth::guard('api')->check() || !Auth::guard('lawyer')->check() || !Auth::guard('representative')->check()) {
            throw new HttpResponseException($this->error('This action is unauthorized', 422));
        }
        $lawyer = Cache::remember('lawyer_' . $id, 600, function () use ($id) {
            return Lawyer::find($id);
        });

        if (!$lawyer) {
            return $this->error("Lawyer Not Found!", 404);
        }
        return $this->success("lawyer", new LawyerResource($lawyer), 200);
    }

    /**
     * Update lawyer info by employee
     * @param \App\Http\Requests\Employee\UpdateLawyerInfoRequest $request
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateLawyerInfoRequest $request, $id)
    {
        $lawyer = Cache::remember('lawyer_' . $id, 600, function () use ($id) {
            return Lawyer::find($id);
        });
        if (!$lawyer) {
            return $this->error('Lawyer Not Found!', 404);
        }

        $response = $this->lawyerService->update($request->validated(), $lawyer);
        return $response['status']
            ? $this->success("msg", "Lawyer updated profile successfully", 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Delete lawyer account by employee
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $lawyer = Cache::remember('lawyer_' . $id, 600, function () use ($id) {
            return Lawyer::find($id);
        });
        if (!$lawyer) {
            return $this->error('Lawyer Not Found!', 404);
        }

        $response = $this->lawyerService->destroy($lawyer);
        return $response['status']
            ? $this->success('msg', 'Deleted Account Successfully', 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Get list of lawyers forward to AI
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function lawyersAI()
    {
        $lawyers = Cache::remember('lawyers', 1200, function () {
            return Lawyer::all();
        });
        return $this->success('lawyers', LawyerResource::collection($lawyers), 200);
    }

    /**
     * Get account info
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $lawyer = Cache::remember('lawyer_' . Auth::guard('lawyer')->id(), 600, function () {
            return Lawyer::where('id', Auth::guard('lawyer')->id())->first();
        });

        if ($lawyer && $lawyer->role->name !== 'lawyer') {
            throw new HttpResponseException($this->error('This action is unauthorized', 422));
        }
        return $this->success("profile", new LawyerResource($lawyer), 200);
    }

    /**
     * Agency request is accepted & send notification to representative
     * @param \App\Http\Requests\Agency\StoreLawyerForAgencyRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function agencyAccepted(StoreLawyerForAgencyRequest $request)
    {
        $representative = Cache::remember("representative_" . Auth::guard("representative")->id(), 600, function () use ($request) {
            return Representative::find($request['representative_id']);
        });

        $response = $this->lawyerService->send($request->validated());
        return $response['status']
            ? $this->success('msg', 'Send request to representative ' . $representative->name, 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Get list of notifications
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getNotifications()
    {
        $lawyer = Auth::guard('lawyer')->user();
        return $this->success('notifications', NotificationResource::collection($lawyer->notifications), 200);
    }
}
