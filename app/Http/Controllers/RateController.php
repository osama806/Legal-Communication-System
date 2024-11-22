<?php

namespace App\Http\Controllers;

use App\Http\Requests\Rate\StoreRatingRequest;
use App\Http\Requests\Rate\UpdateRatingRequest;
use App\Http\Resources\RateResource;
use App\Models\Lawyer;
use App\Models\Rate;
use App\Services\RateService;
use App\Traits\ResponseTrait;
use Auth;
use Illuminate\Http\Request;

class RateController extends Controller
{
    use ResponseTrait;

    protected $rateService;
    public function __construct(RateService $rateService)
    {
        $this->rateService = $rateService;
    }

    /**
     * Display a listing of the rates
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (!Auth::guard('api')->check() && !Auth::guard('api')->user()->hasRole('admin')) {
            return $this->getResponse('error', 'This action is unauthorized', 422);
        }

        $rates = Rate::all();
        return $this->getResponse('rates', RateResource::collection($rates), 200);
    }

    /**
     * Store a newly created rating in storage.
     * @param \App\Http\Requests\Rate\StoreRatingRequest $request
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(StoreRatingRequest $request, $id)
    {
        $lawyer = Lawyer::find($id);
        if (!$lawyer) {
            return $this->getResponse("error", "Lawyer Not Found!", 404);
        }

        $response = $this->rateService->createRate($request->validated(), $lawyer);
        return $response['status']
            ? $this->getResponse('msg', 'Create Rating To Lawyer ' . "'" . $lawyer->name . "'" . ' Successfully', 201)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }

    /**
     * Display the specified rate.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if (!Auth::guard('api')->check() && !Auth::guard('api')->user()->hasRole('admin')) {
            return $this->getResponse('error', 'This action is unauthorized', 422);
        }

        $rate = Rate::find($id);
        if (!$rate) {
            return $this->getResponse('error', 'Rate Not Found!', 404);
        }

        return $this->getResponse('rate', new RateResource($rate), 200);
    }

    /**
     * Update the specified rate in storage.
     * @param \App\Http\Requests\Rate\UpdateRatingRequest $request
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateRatingRequest $request, $id)
    {
        $rate = Rate::find($id);
        if (!$rate) {
            return $this->getResponse('error', 'Rate Not Found!', 404);
        }

        $response = $this->rateService->updateRate($request->validated(), $rate);
        return $response['status']
            ? $this->getResponse('msg', 'Updated Rating Successfully', 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }

    /**
     * Remove the specified rate from storage.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (!Auth::guard('api')->check() && !Auth::guard('api')->user()->hasRole('admin')) {
            return $this->getResponse('error', 'This action is unauthorized', 422);
        }

        $rate = Rate::find($id);
        if (!$rate) {
            return $this->getResponse('error', 'Rate Not Found!', 404);
        }

        $rate->delete();
        return $this->getResponse('msg', 'Deleted Rate Successfully', 200);
    }

    /**
     * Get list of rates forward to AI
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function ratesAI()
    {
        $rates = Rate::all();
        return $this->getResponse('rates', RateResource::collection($rates), 200);
    }
}
