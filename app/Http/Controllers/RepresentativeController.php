<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\RegisterRepresentativeRequest;
use App\Http\Requests\Agency\StoreRepresentativeForAgencyRequest;
use App\Http\Requests\Employee\UpdateRepresentativeInfoRequest;
use App\Http\Requests\Representative\IndexFilterRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\RepresentativeResource;
use App\Models\Agency;
use App\Models\Representative;
use App\Http\Services\RepresentativeService;
use App\Traits\ResponseTrait;
use Auth;
use Cache;

class RepresentativeController extends Controller
{
    use ResponseTrait;
    protected $representativeService;
    public function __construct(RepresentativeService $representativeService)
    {
        $this->representativeService = $representativeService;
    }

    /**
     * Get list of representatives by admin, employee & lawyer.
     * @param \App\Http\Requests\Representative\IndexFilterRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index(IndexFilterRequest $request)
    {
        $response = $this->representativeService->getList($request->validated());
        return $response['status']
            ? $this->success('data', $response['representatives'], 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Create new representative by admin
     * @param \App\Http\Requests\Admin\RegisterRepresentativeRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(RegisterRepresentativeRequest $request)
    {
        $response = $this->representativeService->signupRepresentative($request->validated());
        return $response['status']
            ? $this->tokenResponse($response['access_token'], $response['refresh_token'], 'representative')
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Get representative info by admin, employee & lawyer.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if ((Auth::guard('api')->check() && (Auth::guard('api')->user()->hasRole('admin') || Auth::guard('api')->user()->hasRole('employee'))) || Auth::guard('lawyer')->check()) {
            $representative = Cache::remember('representative_' . $id, 600, function () use ($id) {
                return Representative::find($id);
            });

            if (!$representative) {
                return $this->error("Representative Not Found", 404);
            }
            return $this->success("representative", new RepresentativeResource($representative), 200);
        } else {
            return $this->error('This action is unauthorized', 422);
        }
    }

    /**
     * Update representative info by employee
     * @param \App\Http\Requests\Employee\UpdateRepresentativeInfoRequest $request
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateRepresentativeInfoRequest $request, $id)
    {
        $representative = Cache::remember('representative_' . $id, 600, function () use ($id) {
            return Representative::find($id);
        });
        if (!$representative) {
            return $this->error('Representative Not Found!', 404);
        }

        $response = $this->representativeService->update($request->validated(), $representative);
        return $response['status']
            ? $this->success("msg", "Representative updated profile successfully", 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Delete representative account by employee
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $representative = Cache::remember('representative_' . $id, 600, function () use ($id) {
            return Representative::find($id);
        });
        if (!$representative) {
            return $this->error('Representative Not Found!', 404);
        }

        $response = $this->representativeService->destroy($representative);
        return $response['status']
            ? $this->success('msg', 'Deleted Account Successfully', 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Get account info
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $representative = Cache::remember('representative_' . Auth::guard('representative')->id(), 600, function () {
            return Representative::find(Auth::guard('representative')->id());
        });

        if ($representative && $representative->role->name !== 'representative') {
            return $this->error('This action is unauthorized', 422);
        }
        return $this->success("profile", new RepresentativeResource($representative), 200);
    }

    /**
     * Acceptance agency coming from lawyer & send notifications to user and lawyer both
     * @param \App\Http\Requests\Agency\StoreRepresentativeForAgencyRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function agencyAcceptance(StoreRepresentativeForAgencyRequest $request)
    {
        $response = $this->representativeService->sendResponse($request->validated());
        $agency = Cache::remember('agency_' . $request['agency_id'], 600, function () use ($request) {
            return Agency::find($request['agency_id']);
        });

        return $response['status']
            ? $this->success('msg', 'Agency Status is ' . $agency->status, 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Get list of notifications
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getNotifications()
    {
        $representative = Auth::guard('representative')->user();
        return $this->success('notifications', NotificationResource::collection($representative->notifications), 200);
    }
}
