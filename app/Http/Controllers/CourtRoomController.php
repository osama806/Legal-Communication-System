<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourtRoom\StoreRequest;
use App\Http\Requests\CourtRoom\UpdateRequest;
use App\Http\Resources\CourtRoomResource;
use App\Http\Services\CourtRoomService;
use App\Models\Court_room;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Cache;

class CourtRoomController extends Controller
{
    use ResponseTrait;
    protected $courtRoomService;
    public function __construct(CourtRoomService $courtRoomService)
    {
        $this->courtRoomService = $courtRoomService;
    }

    /**
     * Display a listing of the court rooms by admin, employee, lawyer & representative.
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function index()
    {
        $response = $this->courtRoomService->fetch();
        return $response['status']
            ? $this->success('court_rooms', CourtRoomResource::collection($response['court_rooms']), 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Store a newly created court room in storage by employee.
     * @param \App\Http\Requests\CourtRoom\StoreRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        $response = $this->courtRoomService->create($request->validated());
        return $response['status']
            ? $this->success('msg', 'Created Court Room Successfully', 201)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Display the specified court room by admin, employee, lawyer & representative.
     * @param mixed $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function show($id)
    {
        $response = $this->courtRoomService->display($id);
        return $response['status']
            ? $this->success('court_room', new CourtRoomResource($response['court_room']), 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Update the specified court room in storage by employee.
     * @param \App\Http\Requests\CourtRoom\UpdateRequest $request
     * @param mixed $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        $response = $this->courtRoomService->edit($request->validated(), $id);
        return $response['status']
            ? $this->success('msg', 'Updated Court Room Successfully', 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Remove the specified court room from storage by employee.
     * @param mixed $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $court_room = Cache::remember('court_room_' . $id, 600, function () use ($id) {
            return Court_room::find($id);
        });

        if (!$court_room) {
            return $this->error('Court Room Not Found!', 404);
        }
        $court_room->delete();
        return $this->success('msg', 'Deleted Court Room Successfully', 200);
    }
}
