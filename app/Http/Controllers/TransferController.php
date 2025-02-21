<?php

namespace App\Http\Controllers;

use App\Events\NewNotification;
use App\Events\NewTransactionEvent;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\CustomNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransferController extends Controller
{
    public function showTransferForm()
    {
        $users = User::where('id', '!=', Auth::id())->get();
        return view('transfer.index', compact('users'));
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1'
        ]);

        $sender = Auth::user();
        $receiver = User::findOrFail($request->input('receiver_id'));

        // Check if sender has enough balance
        if ($sender->wallet->balance < $request->input('amount')) {
            return back()->withErrors(['Insufficient balance!']);
        }

        // Deduct from sender
        $sender->wallet->balance -= $request->input('amount');
        $sender->wallet->save();

        // Credit to receiver
        $receiver->wallet->balance += $request->input('amount');
        $receiver->wallet->save();

        // Record transaction
        $transaction = Transaction::create([
            'sender_id'   => $sender->id,
            'receiver_id' => $receiver->id,
            'amount'      => $request->input('amount'),
            'currency'    => $sender->wallet->currency,
            'type'        => 'TRANSFER',
            'description' => 'User-to-user transfer',
        ]);

        // Notify sender
        $senderNotification = [
            'message' => "You transferred NGN {$transaction->amount} to {$receiver->name}",
            'type'    => 'transfer_out',
            'time'    => now()->toDateTimeString(),
        ];
        $sender->notify(new CustomNotification($senderNotification));

        // Notify receiver
        $receiverNotification = [
            'message' => "You received NGN {$transaction->amount} from {$sender->name}",
            'type'    => 'transfer_in',
            'time'    => now()->toDateTimeString(),
        ];
        $receiver->notify(new CustomNotification($receiverNotification));

        event(new NewNotification($senderNotification,$sender->id));
        event(new NewNotification($receiverNotification,$receiver->id));

        event(new NewTransactionEvent($transaction, $sender->id));
        event(new NewTransactionEvent($transaction, $receiver->id));

        return redirect()->route('transactions.index')->with('success', 'Transfer successful!');
    }
}

