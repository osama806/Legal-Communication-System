<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\StoreSpecializationRequest;
use App\Http\Requests\Employee\UpdateSpecializationRequest;
use App\Http\Resources\SpecializationResource;
use App\Models\Specialization;
use App\Http\Services\SpecializationService;
use App\Traits\ResponseTrait;
use Auth;
use Cache;
use Illuminate\Http\Exceptions\HttpResponseException;

class SpecializationController extends Controller
{
    use ResponseTrait;
    protected $specializationService;
    public function __construct(SpecializationService $specialization)
    {
        $this->specializationService = $specialization;
    }

    /**
     * Display a listing of the specializations.
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (!Auth::guard('api')->check() || Auth::guard('api')->user()->hasRole('user')) {
            throw new HttpResponseException($this->error('This action is unauthorized', 422));
        }
        $specializations = Cache::remember('specializations', 1200, function () {
            return Specialization::all();
        });

        return $this->success('specializations', SpecializationResource::collection($specializations), 200);
    }

    /**
     * Store a newly created specialization in storage by admin.
     * @param \App\Http\Requests\Admin\StoreSpecializationRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(StoreSpecializationRequest $request)
    {
        $response = $this->specializationService->storeSpecialization($request->validated());
        return $response['status']
            ? $this->success('msg', 'Created Specialization Successfully', 201)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Display the specified specialization.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if (!Auth::guard('api')->check() || Auth::guard('api')->user()->hasRole('user')) {
            throw new HttpResponseException($this->error('This action is unauthorized', 422));
        }

        $specialization = Cache::remember('specialization_' . $id, 600, function () use ($id) {
            return Specialization::find($id);
        });

        if (!$specialization) {
            return $this->error('Specialization Not Found!', 404);
        }

        return $this->success('specialization', new SpecializationResource($specialization), 200);
    }

    /**
     * Update the specified specialization in storage by employee.
     * @param \App\Http\Requests\Employee\UpdateSpecializationRequest $request
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateSpecializationRequest $request, $id)
    {
        $specialization = Cache::remember('specialization_' . $id, 600, function () use ($id) {
            return Specialization::find($id);
        });

        if (!$specialization) {
            return $this->error('Specialization Not Found!', 404);
        }

        $response = $this->specializationService->updateSpecialization($request->validated(), $specialization);
        return $response['status']
            ? $this->success('msg', 'Updated Specialization Successfully', 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Remove the specified specialization from storage by employee.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user()->hasRole('employee')) {
            throw new HttpResponseException($this->error('This action is unauthorized', 422));
        }

        $specialization = Cache::remember('specialization_' . $id, 600, function () use ($id) {
            return Specialization::find($id);
        });

        if (!$specialization) {
            return $this->error('Specialization Not Found!', 404);
        }

        $specialization->delete();
        return $this->success('msg', 'Deleted Specialization Successfully', 200);
    }
}
