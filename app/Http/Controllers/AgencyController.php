<?php

namespace App\Http\Controllers;

use App\Http\Requests\Agency\FilterForAllRequest;
use App\Http\Requests\Agency\FilterRequest;
use App\Http\Requests\Agency\ShowOneRequest;
use App\Http\Requests\Agency\StoreAgencyRequest;
use App\Http\Resources\AgencyResource;
use App\Models\Agency;
use App\Http\Services\AgencyService;
use App\Traits\ResponseTrait;
use Auth;
use Cache;

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
        $agency = Cache::remember('agency_' . $id, 600, function () use ($id) {
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
        $agency = Cache::remember('agency_' . $id, 600, function () use ($id) {
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
     * Get listing of the agencies related to user, lawyer & representative.
     * @param \App\Http\Requests\Agency\FilterForAllRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getList(FilterForAllRequest $request)
    {
        $response = $this->agencyService->getListForAll($request->validated());
        return $response['status']
            ? $this->success('data', $response['agencies'], 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Display the specified agency related to user, lawyer & representative.
     * @param \App\Http\Requests\Agency\ShowOneRequest $request
     * @param mixed $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function showOne(ShowOneRequest $request, $id)
    {
        $response = $this->agencyService->dispayOne($request->validated(), $id);
        return $response['status']
            ? $this->success('agency', new AgencyResource($response['agency']), 200)
            : $this->error($response['msg'], $response['code']);
    }
}
