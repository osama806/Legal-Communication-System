<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RepresentativeToLawyerNotification extends Notification
{
    use Queueable;
    protected $agency;

    /**
     * Create a new notification instance.
     */
    public function __construct($agency)
    {
        $this->agency = $agency;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'agency_number' => $this->agency->id,
            'from' => 'Representative ' . $this->agency->representative->name,
            'to' => 'Lawyer ' . $this->agency->lawyer->name,
            'message' => "Agency status is " . $this->agency->status,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
