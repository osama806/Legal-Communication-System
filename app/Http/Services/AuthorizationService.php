<?php

namespace App\Http\Services;

use App\Models\Authorization;
use Illuminate\Support\Facades\Cache;

class AuthorizationService
{
    /**
     * Store a newly created authorization in storage.
     * @param array $data
     * @return array
     */
    public function storeAuthorization(array $data)
    {
        try {
            Authorization::create($data);

            Cache::forget("authorizations");
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
     * Update the specified authorization in storage.
     * @param array $data
     * @param \App\Models\Authorization $authorization
     * @return array
     */
    public function updateAuthorization(array $data, Authorization $authorization)
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

            $authorization->update($filteredData);
            Cache::forget('authorizations');
            Cache::forget('authorization_' . $authorization->id);
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
