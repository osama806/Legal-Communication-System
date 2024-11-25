<?php

namespace App\Services;

use App\Models\Lawyer;
use App\Models\Rate;
use Auth;
use Exception;

class RateService
{
    /**
     * Store a newly created rating in storage.
     * @param array $data
     * @param \App\Models\Lawyer $lawyer
     * @return array
     */
    public function createRate(array $data, Lawyer $lawyer)
    {
        try {
            $rate = Rate::where("user_id", Auth::guard('api')->id())->where('lawyer_id', $lawyer->id)->first();
            if ($rate) {
                return [
                    'status' => false,
                    'msg' => 'You Rated This Lawyer Already!',
                    'code' => 400,
                ];
            }

            Rate::create([
                "user_id" => Auth::guard('api')->id(),
                "lawyer_id" => $lawyer->id,
                "rating" => $data["rating"],
                "review" => $data["review"] ?? null,
            ]);

            return [
                'status' => true,
            ];

        } catch (Exception $e) {
            return [
                'status' => false,
                'msg' => $e->getMessage(),
                'code' => 500,
            ];
        }
    }

    /**
     * Display one rate
     * @param string $id
     * @return array
     */
    public function showRate(string $id)
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user()->hasRole('admin')) {
            return [
                'status' => false,
                'msg' => 'This action is unauthorized',
                'code' => 422
            ];
        }

        $rate = Rate::find($id);
        if (!$rate) {
            return [
                'status' => false,
                'msg' => 'Rate Not Found!',
                'code' => 404
            ];
        }

        return [
            'status' => true,
            'rate' => $rate,
        ];
    }

    /**
     * Update the specified rate in storage.
     * @param array $data
     * @param \App\Models\Rate $rate
     * @return array
     */
    public function updateRate(array $data, Rate $rate)
    {
        if ($rate->user_id != Auth::guard('api')->id()) {
            return [
                'status' => false,
                'msg' => 'This action is unauthorized',
                'code' => 422,
            ];
        }

        try {
            $filteredData = array_filter($data, function ($value) {
                return !is_null($value) && trim($value) !== '';
            });

            if (count($filteredData) < 1) {
                return [
                    'status' => false,
                    'msg' => 'Not Found Any Data to Update',
                    'code' => 404
                ];
            }
            $rate->update($filteredData);
            return ['status' => true];
        } catch (Exception $e) {
            return [
                'status' => false,
                'msg' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Remove rate
     * @param string $id
     * @return array
     */
    public function deleteRate(string $id)
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user()->hasRole('admin')) {
            return [
                'status' => false,
                'msg' => 'This action is unauthorized',
                'code' => 422
            ];
        }

        $rate = Rate::find($id);
        if (!$rate) {
            return [
                'status' => false,
                'msg' => 'Rate Not Found!',
                'code' => 404
            ];
        }

        $rate->delete();
        return ['status' => true];
    }
}
