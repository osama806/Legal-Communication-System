<?php

namespace App\Http\Controllers;

use App\Http\Requests\Authorization\StoreAuthorizationRequest;
use App\Http\Requests\Authorization\UpdateAuthorizationRequest;
use App\Http\Resources\AuthorizationResource;
use App\Http\Services\AuthorizationService;
use App\Models\Authorization;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AuthorizationController extends Controller
{
    use ResponseTrait;
    protected $authorizationService;
    public function __construct(AuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    /**
     * Display a listing of the authorizations.
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (!Auth::guard('api')->check() || Auth::guard('api')->user()->hasRole('user')) {
            return $this->error('This action is unauthorized', 422);
        }
        $authorizations = Cache::remember('authorizations', 1200, function () {
            return Authorization::all();
        });

        return $this->success('authorizations', AuthorizationResource::collection($authorizations), 200);
    }

    /**
     * Store a newly created authorization in storage by admin.
     * @param \App\Http\Requests\Authorization\StoreAuthorizationRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function store(StoreAuthorizationRequest $request)
    {
        $response = $this->authorizationService->storeAuthorization($request->validated());
        return $response['status']
            ? $this->success('msg', 'Created Authorization Successfully', 201)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Display the specified authorization.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if (!Auth::guard('api')->check() || Auth::guard('api')->user()->hasRole('user')) {
            return $this->error('This action is unauthorized', 422);
        }

        $authorization = Cache::remember('authorization_' . $id, 600, function () use ($id) {
            return Authorization::find($id);
        });

        if (!$authorization) {
            return $this->error('Authorization Not Found!', 404);
        }

        return $this->success('authorization', new AuthorizationResource($authorization), 200);
    }

    /**
     * Update the specified authorization in storage by employee.
     * @param \App\Http\Requests\Authorization\UpdateAuthorizationRequest $request
     * @param mixed $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(UpdateAuthorizationRequest $request, $id)
    {
        $authorization = Cache::remember('authorization_' . $id, 600, function () use ($id) {
            return Authorization::find($id);
        });

        if (!$authorization) {
            return $this->error('Authorization Not Found!', 404);
        }

        $response = $this->authorizationService->updateAuthorization($request->validated(), $authorization);
        return $response['status']
            ? $this->success('msg', 'Updated Authorization Successfully', 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Remove the specified authorization from storage by employee.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user()->hasRole('employee')) {
            return $this->error('This action is unauthorized', 422);
        }

        $authorization = Cache::remember('authorization_' . $id, 600, function () use ($id) {
            return Authorization::find($id);
        });

        if (!$authorization) {
            return $this->error('Authorization Not Found!', 404);
        }

        $authorization->delete();
        return $this->success('msg', 'Deleted Authorization Successfully', 200);
    }
}
