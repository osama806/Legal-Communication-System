<?php

namespace App\Http\Controllers;

use App\Http\Requests\Conversation\StoreByLawyerRequest;
use App\Http\Requests\Conversation\StoreByUserRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Http\Services\ConversationService;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    use ResponseTrait;
    protected $conversationService;
    public function __construct(ConversationService $conversationService)
    {
        $this->conversationService = $conversationService;
    }

    /**
     * Get list of conversations related to user
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getUserConversations()
    {
        $user = Auth::guard('api')->user();
        $conversations = Conversation::where('user_id', $user->id)
            ->with(['user', 'lawyer'])
            ->get();

        return $this->success('conversations', ConversationResource::collection($conversations), 200);
    }

    /**
     * Get list of conversations related to lawyer
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getLawyerConversations()
    {
        $lawyer = Auth::guard('lawyer')->user();
        $conversations = Conversation::where('lawyer_id', $lawyer->id)
            ->with(['user', 'lawyer'])
            ->get();

        return $this->success('conversations', ConversationResource::collection($conversations), 200);
    }

    /**
     * Create new conversation by user
     * @param \App\Http\Requests\Conversation\StoreByUserRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function storeByUser(StoreByUserRequest $request)
    {
        try {
            $validatedData = $request->validated();
            Conversation::firstOrCreate([
                'user_id' => Auth::guard('api')->id(),
                'lawyer_id' => $validatedData['lawyer_id'],
            ]);

            return $this->success('msg', 'Created Conversation Successfully', 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Create new conversation by lawyer
     * @param \App\Http\Requests\Conversation\StoreByLawyerRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function storeByLawyer(StoreByLawyerRequest $request)
    {
        $response = $this->conversationService->startByLawyer($request->validated());
        return $response['status']
            ? $this->success('msg', 'Created New Conversation Successfully', 201)
            : $this->error($response['msg'], $response['code']);
    }
}
