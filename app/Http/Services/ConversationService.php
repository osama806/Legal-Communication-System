<?php

namespace App\Http\Services;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ConversationService
{
    /**
     * Create new conversation by lawyer
     * @param array $data
     * @return array
     */
    public function startByLawyer(array $data)
    {
        try {
            $user = User::find($data['user_id']);
            if (!$user->hasRole('user')) {
                return [
                    'status' => false,
                    'msg' => 'User Not Found!',
                    'code' => 404
                ];
            }

            Conversation::firstOrCreate([
                'user_id' => $data['user_id'],
                'lawyer_id' => Auth::guard('lawyer')->id(),
            ]);

            return ['status' => true];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'msg' => $e->getMessage(),
                'code' => 500
            ];
        }
    }
}
