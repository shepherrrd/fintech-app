<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $transactionsSent = Transaction::where('sender_id', $user->id)->latest()->get();
        $transactionsReceived = Transaction::where('receiver_id', $user->id)->latest()->get();

        return view('transactions.index', compact('transactionsSent', 'transactionsReceived'));
    }
}

