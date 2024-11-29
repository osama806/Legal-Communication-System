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

    /**
     * Formal response to return tokens
     * @param string $access_token
     * @param string $refresh_token
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function tokenResponse(string $access_token, string $refresh_token, string $role)
    {
        return response()->json([
            'isSuccess' => true,
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'role' => $role
        ], 200);
    }
}
