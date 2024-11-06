<?php

namespace App\Traits;

trait ResponseTrait
{
    public function getResponse(string $key, $val, int $code)
    {
        return response()->json([
            'isSuccess' => $code < 300 && $code >= 200 ? true : false,
            $key => $val
        ], $code);
    }
}
