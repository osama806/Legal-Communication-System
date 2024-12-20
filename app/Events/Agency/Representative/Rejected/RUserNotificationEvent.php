<?php

namespace App\Events\Agency\Representative\Rejected;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RUserNotificationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $agency;
    /**
     * Create a new event instance.
     */
    public function __construct(Model $agency)
    {
        $this->agency = $agency;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user_notifications_' . $this->agency->id)
        ];
    }

    public function broadcastWith()
    {
        return [
            'notification' => [
                'message' => "لديك رسالة من مندوب الوكالات: " . $this->agency->representative->name,
                'content' => 'عذراً؛ لا أستطيع تسجيل الوكالة ذات الرقم ' . $this->agency->id . " ضمن قيود السجلات.",
                'agency_details' => [
                    'user_name' => $this->agency->user->name,
                    'lawyer_name' => $this->agency->lawyer->name,
                    'type' => $this->agency->type,
                ]
            ]
        ];
    }
}
