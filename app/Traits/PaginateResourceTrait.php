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
            "next-page" => $this->getPageNumber($data->nextPageUrl()), // رقم الصفحة التالية
            "previous-page" => $this->getPageNumber($data->previousPageUrl()), // رقم الصفحة السابقة
            "total-{$key}" => $data->total(),
            "{$key}-per-page" => $data->perPage(),
        ];
    }

    /**
     * Extract the page number from the URL.
     *
     * @param string|null $url
     * @return int|null
     */
    private function getPageNumber($url)
    {
        if (!$url) {
            return null; // إذا لم يكن هناك رابط
        }

        // استخراج رقم الصفحة من الرابط باستخدام Query Parameters
        parse_str(parse_url($url, PHP_URL_QUERY), $queryParams);
        return isset($queryParams['page']) ? (int) $queryParams['page'] : null;
    }
}
