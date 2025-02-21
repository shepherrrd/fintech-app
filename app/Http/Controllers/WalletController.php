<?php

namespace App\Http\Controllers;

use App\Events\NewNotification;
use App\Events\NewTransactionEvent;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Notifications\DepositSuccessfulNotification;
use Faker\Core\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Guid\Guid;

use function Illuminate\Log\log;

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

    public function initializePaystackPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $amount = $request->input('amount');
        $amountInKobo = $amount * 100;
        $reference = 'PS_' . uniqid();
        log('Paystack payment initialized.', ['amount' => $amount, 'reference' => $reference]);
        $transaction = Transaction::create([
            'sender_id'    => null,
            'receiver_id'  => Auth::id(),
            'amount'       => $amount,
            'currency'     => 'NGN',
            'type'         => 'FUND',
            'description'  => 'Paystack payment - Pending',
            'reference'    => $reference,
            'status'       => 'pending',
        ]);

        return response()->json([
            'success'       => true,
            'reference'     => $reference,
            'amountInKobo'  => $amountInKobo,
        ]);
    }

    public function verifyPaystackPaymentInline(Request $request)
    {
        $request->validate([
            'reference' => 'required|string',
        ]);

        $reference = $request->input('reference');

        $transaction = Transaction::where('reference', $reference)
            ->where('status', 'pending')
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found or already processed.',
            ]);
        }
        $verifyUrl = config('services.paystack.payment_url') . '/transaction/verify/' . $reference;
        $response = Http::withToken(config('services.paystack.secret_key'))->get($verifyUrl);

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Verification request failed.',
            ]);
        }

        $data = $response->json();

        if (isset($data['status']) && $data['status'] === true && $data['data']['status'] === 'success') {
            $verifiedAmount = $data['data']['amount'] / 100;

            if ((float)$verifiedAmount !== (float)$transaction->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verified amount does not match transaction amount.',
                ]);
            }

            $wallet = Wallet::firstOrCreate(
                ['user_id' => Auth::id()],
                ['balance' => 0, 'currency' => 'NGN']
            );
            $wallet->balance += $verifiedAmount;
            $wallet->save();

            $transaction->status = 'credited';
            $transaction->description = 'Paystack payment - Credited';
            $transaction->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment verified and wallet credited.',
            ]);
        } else {
            $transaction->status = 'failed';
            $transaction->description = 'Paystack payment - Failed verification';
            $transaction->save();

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed.',
            ]);
        }
    }
}

