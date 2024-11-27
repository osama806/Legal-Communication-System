<?php

namespace App\Traits;

trait PaginateResourceTrait
{
    /**
     * Format paginated data for API response
     *
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $data
     * @param string $resourceClass
     * @param string $key
     * @return array
     */
    public function formatPagination($data, $resourceClass, $key)
    {
        return [
            "current-page" => $data->currentPage(),
            $key => $resourceClass::collection($data),
            "next-page" => $data->nextPageUrl(),
            "previous-page" => $data->previousPageUrl(),
            "total-{$key}" => $data->total(),
            "{$key}-per-page" => $data->perPage(),
        ];
    }
}
