<?php

namespace App\Http\Controllers;

use App\Http\Requests\Court\StoreRequest;
use App\Http\Requests\Court\UpdateRequest;
use App\Http\Resources\CourtResource;
use App\Http\Services\CourtService;
use App\Models\Court;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Cache;

class CourtController extends Controller
{
    use ResponseTrait;
    protected $courtService;
    public function __construct(CourtService $courtService)
    {
        $this->courtService = $courtService;
    }

    /**
     * Display a listing of the courts by admin, employee, lawyer & representative.
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function index()
    {
        $response = $this->courtService->fetch();
        return $response['status']
            ? $this->success('courts', CourtResource::collection($response['courts']), 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Store a newly created court in storage by employee.
     * @param \App\Http\Requests\Court\StoreRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        $response = $this->courtService->create($request->validated());
        return $response['status']
            ? $this->success('msg', 'Created Court Successfully', 201)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Display the specified court by admin, employee, lawyer & representative.
     * @param mixed $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function show($id)
    {
        $response = $this->courtService->display($id);
        return $response['status']
            ? $this->success('court', new CourtResource($response['court']), 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Update the specified court in storage by employee.
     * @param \App\Http\Requests\Court\UpdateRequest $request
     * @param mixed $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        $response = $this->courtService->edit($request->validated(), $id);
        return $response['status']
            ? $this->success('msg', 'Updated Court Successfully', 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Remove the specified court from storage by employee.
     * @param mixed $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $court = Cache::remember('court_' . $id, 600, function () use ($id) {
            return Court::find($id);
        });

        try {
            if (!$court) {
                return $this->error('Court Not Found!', 404);
            }

            $court->delete();
            return $this->success('msg', 'Deleted Court Successfully', 200);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }
}
