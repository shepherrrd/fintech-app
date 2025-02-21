<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class DepositSuccessfulNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $amount;
    public $message;

    /**
     * Create a new notification instance.
     *
     * @param  float  $amount
     */
    public function __construct($amount)
    {
        $this->amount = $amount;
        $this->message = "Your deposit of NGN {$amount} was successful!";
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // Persist in the database and broadcast in real time.
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification for the database.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->message,
            'amount'  => $this->amount,
            'type'    => 'deposit_success',
            'time'    => now()->toDateTimeString(),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => $this->message,
            'amount'  => $this->amount,
            'type'    => 'deposit_success',
            'time'    => now()->toDateTimeString(),
        ]);
    }
}
