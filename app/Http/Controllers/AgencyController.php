<?php

namespace App\Http\Controllers;

use App\Http\Requests\Agency\FilterForLawyerRequest;
use App\Http\Requests\Agency\FilterForRepresentativeRequest;
use App\Http\Requests\Agency\FilterForUserRequest;
use App\Http\Requests\Agency\FilterRequest;
use App\Http\Requests\Agency\StoreAgencyRequest;
use App\Http\Resources\AgencyResource;
use App\Models\Agency;
use App\Models\Lawyer;
use App\Http\Services\AgencyService;
use App\Traits\ResponseTrait;
use Auth;
use Cache;
use Illuminate\Http\Request;

class AgencyController extends Controller
{
    use ResponseTrait;
    protected $agencyService;
    public function __construct(AgencyService $agencyService)
    {
        $this->agencyService = $agencyService;
    }

    /**
     * Display a listing of the agencies by admin & employee.
     * @param \App\Http\Requests\Agency\FilterRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index(FilterRequest $request)
    {
        $response = $this->agencyService->getList($request->validated());
        return $response['status']
            ? $this->success('data', $response['agencies'], 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Agency request to lawyer by user
     * @param \App\Http\Requests\Agency\StoreAgencyRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(StoreAgencyRequest $request)
    {
        $response = $this->agencyService->createAgency($request->validated());

        return $response['status']
            ? $this->success('msg', 'Send Request To Lawyer Successfully', 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Display the specified agency by admin & employee.
     * @param string $id
     * @return array|mixed|\Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        if (!Auth::guard('api')->check() || Auth::guard('api')->user()->hasRole('user')) {
            return [
                'status' => false,
                'msg' => 'This action is unauthorized',
                'code' => 422
            ];
        }
        $agency = Cache::remember('agency' . $id, 600, function () use ($id) {
            return Agency::find($id);
        });

        if (!$agency) {
            return $this->error('Agency Not Found', 404);
        }
        return $this->success('agency', new AgencyResource($agency), 200);
    }

    /**
     * Agency isolate by user
     * @param string $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $agency = Cache::remember('agency' . $id, 600, function () use ($id) {
            return Agency::where('id', $id)->where('user_id', Auth::guard('api')->id())->first();
        });
        if (!$agency) {
            return $this->error('Agency Not Found', 404);
        }

        $response = $this->agencyService->isolate($agency);
        return $response['status']
            ? $this->success('msg', 'Agency Isolated Successfully', 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Get list of agencies forward to AI
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function agenciesAI()
    {
        $agencies = Cache::remember('agencies', 1200, function () {
            return Agency::all();
        });
        return $this->success('agencies', AgencyResource::collection($agencies), 200);
    }

    /**
     * Get listing of the agencies related to user.
     * @param \App\Http\Requests\Agency\FilterForUserRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function indexForUser(FilterForUserRequest $request)
    {
        $response = $this->agencyService->getListForUser($request->validated());
        return $response['status']
            ? $this->success('data', $response['agencies'], 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Display the specified agency related to user
     * @param mixed $id
     * @return array|mixed|\Illuminate\Http\JsonResponse
     */
    public function showForUser($id)
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user()->hasRole('user')) {
            return [
                'status' => false,
                'msg' => 'This action is unauthorized',
                'code' => 422
            ];
        }
        $agency = Cache::remember('agencyForUser' . $id, 600, function () use ($id) {
            return Agency::where('id', $id)->where('user_id', Auth::guard('api')->id())->first();
        });

        if (!$agency) {
            return $this->error('Agency Not Found', 404);
        }
        return $this->success('agency', new AgencyResource($agency), 200);
    }

    /**
     * Get listing of the agencies related to lawyer.
     * @param \App\Http\Requests\Agency\FilterForLawyerRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function indexForLawyer(FilterForLawyerRequest $request)
    {
        $response = $this->agencyService->getListForLawyer($request->validated());
        return $response['status']
            ? $this->success('data', $response['agencies'], 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Display the specified agency related to lawyer
     * @param mixed $id
     * @return array|mixed|\Illuminate\Http\JsonResponse
     */
    public function showForLawyer($id)
    {
        if (!Auth::guard('lawyer')->check()) {
            return [
                'status' => false,
                'msg' => 'This action is unauthorized',
                'code' => 422
            ];
        }
        $agency = Cache::remember('agencyForLawyer' . $id, 600, function () use ($id) {
            return Agency::where('id', $id)->where('lawyer_id', Auth::guard('lawyer')->id())->first();
        });

        if (!$agency) {
            return $this->error('Agency Not Found', 404);
        }
        return $this->success('agency', new AgencyResource($agency), 200);
    }

    /**
     * Get listing of the agencies related to representative.
     * @param \App\Http\Requests\Agency\FilterForRepresentativeRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function indexForRepresentative(FilterForRepresentativeRequest $request)
    {
        $response = $this->agencyService->getListForRepresentative($request->validated());
        return $response['status']
            ? $this->success('data', $response['agencies'], 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Display the specified agency related to representative.
     * @param mixed $id
     * @return array|mixed|\Illuminate\Http\JsonResponse
     */
    public function showForRepresentative($id)
    {
        if (!Auth::guard('representative')->check()) {
            return [
                'status' => false,
                'msg' => 'This action is unauthorized',
                'code' => 422
            ];
        }
        $agency = Cache::remember('agencyForRepresentative' . $id, 600, function () use ($id) {
            return Agency::where('id', $id)->where('representative_id', Auth::guard('representative')->id())->first();
        });

        if (!$agency) {
            return $this->error('Agency Not Found', 404);
        }
        return $this->success('agency', new AgencyResource($agency), 200);
    }
}
