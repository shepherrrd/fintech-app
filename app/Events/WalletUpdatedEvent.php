<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class WalletUpdatedEvent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    /**
     * The wallet id.
     *
     * @var int
     */
    public $wallet_id;

    /**
     * The new wallet balance.
     *
     * @var float
     */
    public $balance;

    /**
     * The wallet currency.
     *
     * @var string
     */
    public $currency;

    /**
     * The user id for whom this event is broadcast.
     *
     * @var int
     */
    public $user_id;

    /**
     * Create a new event instance.
     *
     * @param int $wallet_id
     * @param float $balance
     * @param string $currency
     * @param int $user_id
     */
    public function __construct($wallet_id, $balance, $currency, $user_id)
    {
        $this->wallet_id = $wallet_id;
        $this->balance = $balance;
        $this->currency = $currency;
        $this->user_id = $user_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return PrivateChannel
     */
    public function broadcastOn()
    {
        // Broadcast to a private channel specific to the user.
        return new PrivateChannel('wallets.' . $this->user_id);
    }

    /**
     * (Optional) Customize the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'wallet-update';
    }
}
