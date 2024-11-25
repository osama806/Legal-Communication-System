<?php

namespace App\Http\Controllers;

use App\Http\Requests\Agency\StoreAgencyRequest;
use App\Http\Resources\AgencyResource;
use App\Models\Agency;
use App\Models\Lawyer;
use App\Services\AgencyService;
use App\Traits\ResponseTrait;
use Auth;
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
     * Display a listing of the agencies.
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $agencies = Agency::all();
        return $this->getResponse('agencies', AgencyResource::collection($agencies), 200);
    }

    /**
     * Agency request to lawyer by user
     * @param \App\Http\Requests\Agency\StoreAgencyRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(StoreAgencyRequest $request)
    {
        $response = $this->agencyService->createAgency($request->validated());
        $lawyer = Lawyer::find($request['lawyer_id']);
        return $response['status']
            ? $this->getResponse('msg', 'Send request to lawyer ' . $lawyer->name, 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Agency isolate by user
     * @param string $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $agency = Agency::where('id', $id)->where('user_id', Auth::guard('api')->id())->first();
        if (!$agency) {
            return $this->getResponse('error', 'Agency Not Found', 404);
        }

        $response = $this->agencyService->isolate($agency);
        return $response['status']
            ? $this->getResponse('msg', 'Agency Isolated Successfully', 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }
}
