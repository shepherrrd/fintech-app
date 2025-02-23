<?php

namespace App\Http\Controllers;

use App\Events\NewNotification;
use App\Events\NewTransactionEvent;
use App\Events\WalletUpdatedEvent;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\CustomNotification;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as FacadesLog;

use function Illuminate\Log\log;

class TransferController extends Controller
{
    public function showTransferForm()
    {
        $users = User::where('id', '!=', Auth::id())->with('wallets')->get();
        log('Transfer form displayed.', ['user' => $users]);
        return view('transfer.index', compact('users'));
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|min:1'
        ]);

        $sender = Auth::user();
        $receiver = User::findOrFail($request->input('receiver_id'));
        $wallet = Wallet::findOrFail($request->input('wallet_id'));

        if ($sender->wallet->balance < $request->input('amount')) {
            return back()->withErrors(['Insufficient balance!']);
        }

        $sender->wallet->balance -= $request->input('amount');
        $sender->wallet->save();

        $wallet->balance += $request->input('amount');
        $wallet->save();

        $transaction = Transaction::create([
            'sender_id'   => $sender->id,
            'receiver_id' => $receiver->id,
            'amount'      => $request->input('amount'),
            'currency'    => $sender->wallet->currency,
            'type'        => 'TRANSFER',
            'description' => 'User-to-user transfer',
            'reference'   => 'TRF_' . uniqid(),
            'status'      => 'completed',
        ]);

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

        event(new NewNotification($senderNotification, $sender->id));
        event(new NewNotification($receiverNotification, $receiver->id));

        event(new NewTransactionEvent($transaction, $sender->id));
        event(new NewTransactionEvent($transaction, $receiver->id));

        event(new WalletUpdatedEvent(
            $sender->wallet->id,
            $sender->wallet->balance,
            $sender->wallet->currency,
            Auth::id()
        ));

        event(new WalletUpdatedEvent(
            $wallet->id,
            $wallet->balance,
            $wallet->currency,
            $receiver->id
        ));

        return redirect()->route('transactions.index')->with('success', 'Transfer successful!');
    }
}

