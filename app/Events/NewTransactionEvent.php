<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewTransactionEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transaction;
    public $userId;

    /**
     * Create a new event instance.
     *
     * @param Transaction $transaction
     * @param int $userId  The target user (sender or receiver)
     */
    public function __construct(Transaction $transaction, int $userId)
    {
        $this->transaction = $transaction;
        $this->userId = $userId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return PrivateChannel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('transactions.' . $this->userId);
    }

    public function broadcastAs()
{
    return 'new-transaction';
}

}
