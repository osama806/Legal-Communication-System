<?php

namespace App\Http\Services;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Lawyer;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MessageService
{
    /**
     * Get list of messages by user or lawyer
     * @param string $id
     * @return array
     */
    public function fetch(string $id)
    {
        $auth = null;
        $messages = null;
        if (Auth::guard('api')->check() && Auth::guard('api')->user()->hasRole('user')) {
            $auth = Auth::guard('api')->id();
            $conversation = Conversation::where('id', $id)->where('user_id', $auth)->first();
            if (!$conversation) {
                return [
                    'status' => false,
                    'msg' => 'Conversation Not Found!',
                    'code' => 404
                ];
            }

            $messages = Message::where('conversation_id', $id)->where('messagable_type', 'App\\Models\\User')->where('messagable_id', $auth)->get();
            if (count($messages) < 1) {
                return [
                    'status' => false,
                    'msg' => 'Not Found Any Message!',
                    'code' => 404
                ];
            }

            return [
                'status' => true,
                'messages' => $messages
            ];
        }

        if (Auth::guard('lawyer')->check()) {
            $auth = Auth::guard('lawyer')->id();
            $conversation = Conversation::where('id', $id)->where('lawyer_id', $auth)->first();
            if (!$conversation) {
                return [
                    'status' => false,
                    'msg' => 'Conversation Not Found!',
                    'code' => 404
                ];
            }

            $messages = Message::where('conversation_id', $id)->where('messagable_type', 'App\\Models\\Lawyer')->where('messagable_id', $auth)->get();
            if (count($messages) < 1) {
                return [
                    'status' => false,
                    'msg' => 'Not Found Any Message!',
                    'code' => 404
                ];
            }

            return [
                'status' => true,
                'messages' => $messages
            ];
        }

        return [
            'status' => true,
            'messages' => $messages
        ];
    }

    /**
     * Send message from user to lawyer
     * @param array $data
     * @param mixed $id
     * @return array
     */
    public function sendUser(array $data, $id)
    {
        $user = User::find(Auth::guard('api')->id());
        $conversation = Conversation::where('id', $id)->where('user_id', $user->id)->where('lawyer_id', $data['lawyer_id'])->first();
        if (!$conversation) {
            return [
                'status' => false,
                'msg' => 'Conversation Not Found!',
                'code' => 404
            ];
        }

        try {
            $user->messages()->create([
                'conversation_id' => $id,
                'content' => $data['content'],
            ]);

            broadcast(new MessageSent($data['content'], $id))->toOthers();
            return ['status' => true,];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'msg' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Send message from lawyer to user
     * @param array $data
     * @param mixed $id
     * @return array
     */
    public function sendLawyer(array $data, $id)
    {
        $user = User::find($data['user_id']);
        if (!$user->hasRole('user')) {
            return [
                'status' => false,
                'msg' => 'User Not Found!',
                'code' => 404
            ];
        }

        $lawyer = Lawyer::find(Auth::guard('lawyer')->id());
        $conversation = Conversation::where('id', $id)->where('user_id', $user->id)->where('lawyer_id', $lawyer->id)->first();
        if (!$conversation) {
            return [
                'status' => false,
                'msg' => 'Conversation Not Found!',
                'code' => 404
            ];
        }

        try {
            $lawyer->messages()->create([
                'conversation_id' => $id,
                'content' => $data['content'],
            ]);

            broadcast(new MessageSent($data['content'], $id))->toOthers();
            return ['status' => true,];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'msg' => $e->getMessage(),
                'code' => 500
            ];
        }
    }
}
