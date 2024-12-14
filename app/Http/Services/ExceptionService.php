<?php

namespace App\Http\Services;

use App\Models\Exception;
use Illuminate\Support\Facades\Cache;

class ExceptionService
{
    /**
     * Store a newly created exception in storage.
     * @param array $data
     * @return array
     */
    public function storeException(array $data)
    {
        try {
            Exception::create($data);

            Cache::forget("exceptions");
            return [
                'status' => true,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'msg' => $e->getMessage(),
                'code' => 500,
            ];
        }
    }

    /**
     * Update the specified exception in storage.
     * @param array $data
     * @param \App\Models\Exception $exception
     * @return array
     */
    public function updateException(array $data, Exception $exception)
    {
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

            $exception->update($filteredData);
            Cache::forget('exceptions');
            Cache::forget('exception_' . $exception->id);
            return ['status' => true];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'msg' => $e->getMessage(),
                'code' => 500
            ];
        }
    }
}
