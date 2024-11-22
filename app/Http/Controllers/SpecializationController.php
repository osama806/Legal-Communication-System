<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\StoreSpecializationRequest;
use App\Http\Requests\Employee\UpdateSpecializationRequest;
use App\Http\Resources\SpecializationResource;
use App\Models\Specialization;
use App\Services\SpecializationService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

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
        $specializations = Specialization::all();
        return $this->getResponse('specializations', SpecializationResource::collection($specializations), 200);
    }

    /**
     * Store a newly created specialization in storage.
     * @param \App\Http\Requests\Admin\StoreSpecializationRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(StoreSpecializationRequest $request)
    {
        $response = $this->specializationService->storeSpecialization($request->validated());
        return $response['status']
            ? $this->getResponse('msg', 'Created Specialization Successfully', 201)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }

    /**
     * Display the specified specialization.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $specialization = Specialization::find($id);
        if (!$specialization) {
            return $this->getResponse('error', 'Specialization Not Found!', 404);
        }

        return $this->getResponse('specialization', new SpecializationResource($specialization), 200);
    }

    /**
     * Update the specified specialization in storage.
     * @param \App\Http\Requests\Employee\UpdateSpecializationRequest $request
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateSpecializationRequest $request, $id)
    {
        $specialization = Specialization::find($id);
        if (!$specialization) {
            return $this->getResponse('error', 'Specialization Not Found!', 404);
        }

        $response = $this->specializationService->updateSpecialization($request->validated(), $specialization);
        return $response['status']
            ? $this->getResponse('msg', 'Updated Specialization Successfully', 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }

    /**
     * Remove the specified specialization from storage.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $specialization = Specialization::find($id);
        if (!$specialization) {
            return $this->getResponse('error', 'Specialization Not Found!', 404);
        }

        $specialization->delete();
        return $this->getResponse('msg', 'Deleted Specialization Successfully', 200);
    }
}
