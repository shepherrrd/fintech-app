<?php

namespace App\Http\Controllers;

use App\Events\NewNotification;
use App\Events\NewTransactionEvent;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Notifications\DepositSuccessfulNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class WalletController extends Controller
{
    public function index()
    {
        $wallet = Auth::user()->wallet;
        return view('wallet.index', compact('wallet'));
    }
    public function showAddFundsForm()
    {
        return view('wallet.add-funds');
    }

    public function addFunds(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        $user = Auth::user();
        $wallet = $user->wallet;

        $wallet->balance += $request->input('amount');
        $wallet->save();

        Transaction::create([
            'sender_id' => null,
            'receiver_id' => $user->id,
            'amount' => $request->input('amount'),
            'currency' => $wallet->currency,
            'type' => 'FUND',
            'description' => 'Funds added to wallet',
        ]);

        return redirect()->route('wallet.index')->with('success', 'Funds added successfully!');
    }

    public function verifyPaystackPaymentInline(Request $request)
    {
        $request->validate([
            'reference' => 'required|string'
        ]);

        $reference = $request->input('reference');
        $url = config('services.paystack.payment_url') . '/transaction/verify/' . $reference;
        $response = Http::withToken(config('services.paystack.secret_key'))->get($url);

        if ($response->successful() && $response->json('status') === true) {
            $data = $response->json('data');

            if ($data['status'] === 'success') {
                $amount = $data['amount'] / 100;

                $user = auth()->user();
                $wallet = $user->wallet;
                $wallet->balance += $amount;
                $wallet->save();
                $transaction = Transaction::create([
                    'sender_id'   => null,
                    'receiver_id' => $user->id,
                    'amount'      => $amount,
                    'currency'    => 'NGN',
                    'type'        => 'FUND',
                    'description' => 'Paystack payment - Verified inline',
                ]);

                $notificationData = [
                    'message' => "Your deposit of NGN {$amount} was successful!",
                    'type'    => 'deposit_success',
                    'time'    => now()->toDateTimeString(),
                ];
                $user->notify(new DepositSuccessfulNotification($amount));
                event(new NewTransactionEvent($transaction, $user->id));

                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false, 'message' => 'Payment was not successful.']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Payment verification failed.']);
        }
    }
}

