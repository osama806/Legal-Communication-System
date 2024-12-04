<?php

namespace App\Http\Controllers;

use App\Http\Requests\Rate\IndexFilterRequest;
use App\Http\Requests\Rate\StoreRatingRequest;
use App\Http\Requests\Rate\UpdateRatingRequest;
use App\Http\Resources\RateResource;
use App\Models\Lawyer;
use App\Models\Rate;
use App\Http\Services\RateService;
use App\Traits\ResponseTrait;
use Auth;
use Cache;

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
     * @param \App\Http\Requests\Rate\IndexFilterRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index(IndexFilterRequest $request)
    {
        $response = $this->rateService->getList($request->validated());
        return $response['status']
            ? $this->success('data', $response['rates'], 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Store a newly created rating in storage by user.
     * @param \App\Http\Requests\Rate\StoreRatingRequest $request
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(StoreRatingRequest $request, $id)
    {
        $lawyer = Cache::remember('lawyer' . $id, 600, function () use ($id) {
            return Lawyer::find($id);
        });
        if (!$lawyer) {
            return $this->success("error", "Lawyer Not Found!", 404);
        }
        $response = $this->rateService->createRate($request->validated(), $lawyer);

        return $response['status']
            ? $this->success('msg', 'Created Rating Successfully', 201)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Display the specified rate.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $response = $this->rateService->showRate($id);
        return $response['status']
            ? $this->success('rate', new RateResource($response['rate']), 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Update the specified rate in storage by user.
     * @param \App\Http\Requests\Rate\UpdateRatingRequest $request
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateRatingRequest $request, $id)
    {
        $rate = Cache::remember('rate' . $id, 600, function () use ($id) {
            return Rate::find($id);
        });
        if (!$rate) {
            return $this->error('Rate Not Found!', 404);
        }
        $response = $this->rateService->updateRate($request->validated(), $rate);

        return $response['status']
            ? $this->success('msg', 'Updated Rating Successfully', 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Remove the specified rate from storage.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $response = $this->rateService->deleteRate($id);
        return $response['status']
            ? $this->success('msg', 'Deleted Rate Successfully', 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Get list of rates forward to AI
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function ratesAI()
    {
        $rates = Cache::remember('rates', 1200, function () {
            return Rate::all();
        });
        return $this->success('rates', RateResource::collection($rates), 200);
    }
}
