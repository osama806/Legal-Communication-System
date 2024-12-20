<?php

namespace App\Http\Controllers;

use App\Http\Requests\Message\StoreByLawyerRequest;
use App\Http\Requests\Message\StoreByUserRequest;
use App\Http\Resources\MessageResource;
use App\Http\Services\MessageService;
use App\Models\Message;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    use ResponseTrait;
    protected $messageService;
    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    /**
     * Get list of messages by user or lawyer
     * @param mixed $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function index($id)
    {
        $response = $this->messageService->fetch($id);
        return $response['status']
            ? $this->success('messages', MessageResource::collection($response['messages']), 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Send message from user to lawyer
     * @param \App\Http\Requests\Message\StoreByUserRequest $request
     * @param mixed $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function storeByUser(StoreByUserRequest $request, $id)
    {
        $response = $this->messageService->sendUser($request->validated(), $id);
        return $response['status']
            ? $this->success('msg', 'Send Message Successfully', 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Send message from lawyer to user
     * @param \App\Http\Requests\Message\StoreByLawyerRequest $request
     * @param mixed $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function storeByLawyer(StoreByLawyerRequest $request, $id)
    {
        $response = $this->messageService->sendLawyer($request->validated(), $id);
        return $response['status']
            ? $this->success('msg', 'Send Message Successfully', 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Message $message)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Message $message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Message $message)
    {
        //
    }
}
