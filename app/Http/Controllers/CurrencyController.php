<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction;
use Illuminate\Container\Attributes\Log;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Auth;

use function Illuminate\Log\log;

class CurrencyController extends Controller
{
    /**
     * Display the currency conversion form.
     */
    public function showConversionForm()
    {
        return view('currency.index');
    }

    /**
     * Convert the currency using CurrencyLayer API, create a new transaction, and return the result.
     */
    public function convertCurrency(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'from_currency' => 'required|string|size:3',
            'to_currency' => 'required|string|size:3',
        ]);

        $amount = $request->input('amount');
        $from = strtoupper($request->input('from_currency'));
        $to = strtoupper($request->input('to_currency'));
        $apiKey = config('services.currencylayer.key');
        $url = config('services.currencylayer.url') . '/live';
        $response = Http::get($url, [
            'access_key' => $apiKey,
            'currencies' => $to,
            'source' => $from,
            'format' => 1,
        ]);

        $data = $response->json();

        if (!isset($data['success']) || !$data['success']) {
            log('Currency conversion failed.', ['response' => $data]);
            return back()->withErrors(['msg' => 'Currency conversion failed.']);
        }

        $quoteKey = $from . $to;
        if (!isset($data['quotes'][$quoteKey])) {
            return back()->withErrors(['msg' => 'Conversion rate not found.']);
        }
        $rate = $data['quotes'][$quoteKey];
        $convertedAmount = $amount * $rate;
        $transaction = Transaction::create([
            'sender_id' => Auth::id(),
            'receiver_id' => Auth::id(),
            'amount' => $convertedAmount,
            'currency' => $to,
            'type' => 'EXCHANGE',
            'description' => "Converted $amount $from to $convertedAmount $to at rate $rate",
        ]);

        return view('currency.index', compact('convertedAmount', 'rate', 'amount', 'from', 'to', 'transaction'));
    }
}
