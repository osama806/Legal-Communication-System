<?php

namespace App\Traits;

trait ResponseTrait
{
    /**
     * Formal success response
     * @param string $key
     * @param mixed $val
     * @param int $code
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function success(string $key, $value, int $code)
    {
        return response([
            'isSuccess' => true,
            $key => $value,
        ], $code);
    }

    /**
     * Formal error response
     * @param mixed $value
     * @param int $code
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function error($value, int $code)
    {
        return response([
            'isSuccess' => false,
            'error' => $value,
        ], $code);
    }


    /**
     * Formal response to return tokens
     * @param string $access_token
     * @param string $refresh_token
     * @param string $role
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function tokenResponse(string $access_token, string $refresh_token, string $role)
    {
        return response([
            'isSuccess' => true,
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'role' => $role
        ], 200);
    }
}
