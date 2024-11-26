<?php

namespace App\Http\Services;

use App\Models\Specialization;
use Exception;

class SpecializationService
{
    /**
     * Store a newly created specialization in storage.
     * @param array $data
     * @return array
     */
    public function storeSpecialization(array $data)
    {
        try {
            Specialization::create($data);
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
     * Update the specified specialization in storage.
     * @param array $data
     * @param \App\Models\Specialization $specialization
     * @return array
     */
    public function updateSpecialization(array $data, Specialization $specialization)
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

            $specialization->update($filteredData);
            return ['status' => true];

        } catch (Exception $e) {
            return [
                'status' => false,
                'msg' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

}
