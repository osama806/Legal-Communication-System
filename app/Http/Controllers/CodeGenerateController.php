<?php

namespace App\Http\Controllers;

use App\Http\Requests\Code\CodeRequest;
use App\Http\Requests\Code\VerifyRequest;
use App\Http\Services\CodeGenerateService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class CodeGenerateController extends Controller
{
    use ResponseTrait;

    protected $codeGenerateService;
    public function __construct(CodeGenerateService $codeGenerateService)
    {
        $this->codeGenerateService = $codeGenerateService;
    }

    /**
     * Store a newly created code in storage.
     * @param \App\Http\Requests\Code\CodeRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function store(CodeRequest $request)
    {
        $response = $this->codeGenerateService->generate($request->validated());
        return $response['status']
            ? $this->success('msg', 'Send Code Successfully', 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Verify Code
     * @param \App\Http\Requests\Code\VerifyRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function verifyCode(VerifyRequest $request)
    {
        $response = $this->codeGenerateService->checkCode($request->validated());
        return $response['status']
            ? $this->success('msg', 'Code Verified Successfully', 200)
            : $this->error($response['msg'], $response['code']);
    }
}
