<?php

namespace App\Http\Services;

use App\Models\Court_room;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CourtRoomService
{
    /**
     * Display a listing of the court rooms by admin, employee, lawyer & representative.
     * @return array
     */
    public function fetch()
    {
        if (Auth::guard('api')->check() && Auth::guard('api')->user()->hasRole('user')) {
            return [
                'status' => false,
                'msg' => 'This is action unauthorized',
                'code' => 422
            ];
        }
        $court_rooms = Cache::remember('court_rooms', 1200, function () {
            return Court_room::all();
        });

        return [
            'status' => true,
            'court_rooms' => $court_rooms
        ];
    }

    /**
     * Store a newly created court room in storage by employee.
     * @param array $data
     * @return array
     */
    public function create(array $data)
    {
        try {
            Court_room::create($data);

            Cache::forget('court_rooms');
            return ['status' => true];
        } catch (\Exception $exception) {
            return [
                'status' => false,
                'msg' => $exception->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Display the specified court room by admin, employee, lawyer & representative.
     * @param mixed $id
     * @return array
     */
    public function display($id)
    {
        if (Auth::guard('api')->check() && Auth::guard('api')->user()->hasRole('user')) {
            return [
                'status' => false,
                'msg' => 'This is action unauthorized',
                'code' => 422
            ];
        }
        $court_room = Cache::remember('court_room_' . $id, 600, function () use ($id) {
            return Court_room::find($id);
        });

        if (!$court_room) {
            return [
                'status' => false,
                'msg' => 'Court Room Not Found!',
                'code' => 404
            ];
        }
        return [
            'status' => true,
            'court_room' => $court_room
        ];
    }

    /**
     * Update the specified court room in storage by employee.
     * @param array $data
     * @param mixed $id
     * @return array
     */
    public function edit(array $data, $id)
    {
        $court_room = Cache::remember('court_room_' . $id, 600, function () use ($id) {
            return Court_room::find($id);
        });

        try {
            if (!$court_room) {
                return [
                    'status' => false,
                    'msg' => 'Court Room Not Found!',
                    'code' => 404
                ];
            }
            if (count($data) <= 0) {
                return [
                    'status' => false,
                    'msg' => 'Not Found Any Data!',
                    'code' => 404
                ];
            }

            $court_room->update($data);
            Cache::forget('court_rooms');
            Cache::forget('court_room_' . $id);
            return ['status' => true];
        } catch (\Exception $exception) {
            return [
                'status' => false,
                'msg' => $exception->getMessage(),
                'code' => 500
            ];
        }
    }
}
