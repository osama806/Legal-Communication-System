<?php

namespace App\Http\Controllers;

use App\Http\Requests\Exception\StoreExceptionRequest;
use App\Http\Requests\Exception\UpdateExceptionRequest;
use App\Http\Resources\ExceptionResource;
use App\Http\Services\ExceptionService;
use App\Models\Exception;
use App\Traits\ResponseTrait;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ExceptionController extends Controller
{
    use ResponseTrait;
    protected $exceptionService;
    public function __construct(ExceptionService $exceptionService)
    {
        $this->exceptionService = $exceptionService;
    }

    /**
     * Display a listing of the exceptions.
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (!Auth::guard('api')->check() || Auth::guard('api')->user()->hasRole('user')) {
            throw new HttpResponseException($this->error('This action is unauthorized', 422));
        }
        $exceptions = Cache::remember('exceptions', 1200, function () {
            return Exception::all();
        });

        return $this->success('exceptions', ExceptionResource::collection($exceptions), 200);
    }

    /**
     * Store a newly created exception in storage by admin.
     * @param \App\Http\Requests\Exception\StoreExceptionRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function store(StoreExceptionRequest $request)
    {
        $response = $this->exceptionService->storeException($request->validated());
        return $response['status']
            ? $this->success('msg', 'Created Exception Successfully', 201)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Display the specified exception.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if (!Auth::guard('api')->check() || Auth::guard('api')->user()->hasRole('user')) {
            throw new HttpResponseException($this->error('This action is unauthorized', 422));
        }

        $exception = Cache::remember('exception_' . $id, 600, function () use ($id) {
            return Exception::find($id);
        });

        if (!$exception) {
            return $this->error('Exception Not Found!', 404);
        }

        return $this->success('exception', new ExceptionResource($exception), 200);
    }

    /**
     * Update the specified exception in storage by employee.
     * @param \App\Http\Requests\Exception\UpdateExceptionRequest $request
     * @param mixed $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(UpdateExceptionRequest $request, $id)
    {
        $exception = Cache::remember('exception_' . $id, 600, function () use ($id) {
            return Exception::find($id);
        });

        if (!$exception) {
            return $this->error('Exception Not Found!', 404);
        }

        $response = $this->exceptionService->updateException($request->validated(), $exception);
        return $response['status']
            ? $this->success('msg', 'Updated Exception Successfully', 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Remove the specified exception from storage by employee.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user()->hasRole('employee')) {
            throw new HttpResponseException($this->error('This action is unauthorized', 422));
        }

        $exception = Cache::remember('exception_' . $id, 600, function () use ($id) {
            return Exception::find($id);
        });

        if (!$exception) {
            return $this->error('Exception Not Found!', 404);
        }

        $exception->delete();
        return $this->success('msg', 'Deleted Exception Successfully', 200);
    }
}
