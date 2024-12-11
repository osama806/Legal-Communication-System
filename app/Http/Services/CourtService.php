<?php

namespace App\Http\Services;

use App\Models\Court;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CourtService
{
    /**
     * Display a listing of the courts by admin, employee, lawyer & representative.
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
        $courts = Cache::remember('courts', 1200, function () {
            return Court::all();
        });

        return [
            'status' => true,
            'courts' => $courts
        ];
    }

    /**
     * Store a newly created court in storage by employee.
     * @param array $data
     * @return array
     */
    public function create(array $data)
    {
        try {
            Court::create($data);

            Cache::forget('courts');
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
     * Display the specified court by admin, employee, lawyer & representative.
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
        $court = Cache::remember('court_' . $id, 600, function () use ($id) {
            return Court::find($id);
        });

        if (!$court) {
            return [
                'status' => false,
                'msg' => 'Court Not Found!',
                'code' => 404
            ];
        }
        return [
            'status' => true,
            'court' => $court
        ];
    }

    /**
     * Update the specified court in storage by employee.
     * @param array $data
     * @param mixed $id
     * @return array
     */
    public function edit(array $data, $id)
    {
        $court = Cache::remember('court_' . $id, 600, function () use ($id) {
            return Court::find($id);
        });

        try {
            if (!$court) {
                return [
                    'status' => false,
                    'msg' => 'Court Not Found!',
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

            $court->update($data);
            Cache::forget('courts');
            Cache::forget('court_' . $id);
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
