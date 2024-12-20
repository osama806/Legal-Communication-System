<?php

namespace App\Events\Agency\User;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequestNotificationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $agency;
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
            new PrivateChannel('lawyer_notifications_' . $this->agency->id)
        ];
    }

    public function broadcastWith()
    {
        return [
            'notification' => [
                'message' => "لديك طلب توكيل جديد من قبل المستخدم: " . $this->agency->user->name,
                'cause' => $this->agency->cause
            ]
        ];
    }
}
