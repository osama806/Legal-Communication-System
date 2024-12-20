<?php

namespace App\Events\Agency\Lawyer;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RejectNotificationEvent implements ShouldBroadcast
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
                'message' => "لديك رسالة من المحامي: " . $this->agency->lawyer->name,
                'content'   =>  'عذراً؛ لا أستطيع قبول طلب التوكيل ذات الرقم: ' . $this->agency->id
            ]
        ];
    }
}
