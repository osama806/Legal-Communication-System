<?php

namespace App\Traits;

trait ResponseTrait
{
    /**
     * Formal response
     * @param string $key
     * @param mixed $val
     * @param int $code
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getResponse(string $key, $val, int $code)
    {
        return response()->json([
            'isSuccess' => $code < 300 && $code >= 200 ? true : false,
            $key => $val,
        ], $code);
    }
}
