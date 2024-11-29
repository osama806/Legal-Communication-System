<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LawyerToRepresentativeNotification extends Notification
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
    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'agency_number' => $this->agency->id,
            'from' => 'Lawyer ' . $this->agency->lawyer->name,
            'to' => 'Representative ' . $this->agency->representative->name,
            'message' => "You have new agency request. Cause is " . $this->agency->cause,
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
