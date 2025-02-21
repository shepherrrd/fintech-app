<?php

use App\Events\NewNotification;
use App\Events\NewTransactionEvent;
use App\Http\Controllers\ProfileController;
use App\Models\Transaction;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\NotificationController;

Route::get('/', function () {
    return view('welcome');
});
require __DIR__.'/auth.php';


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});




Route::get('/', function () {
    return view('welcome');
});
Route::get('/test-broadcast', function () {
    $user = auth()->user();
    $transaction = new Transaction();
    $transaction->id = 1;
    $transaction->sender_id = $user->id;
    $transaction->receiver_id = $user->id;
    $transaction->amount = 100;
    $transaction->currency = 'NGN';
    $transaction->type = 'TEST';
    $transaction->created_at = now();

    event(new NewTransactionEvent($transaction, $user->id));
    return 'Event fired';
})->middleware('auth');

Route::get('/test-broadcast-notif', function () {
    $user = auth()->user();
    $senderNotification = [
        'message' => "You transferred NGN 800 to shepherd",
        'type'    => 'transfer_out',
        'time'    => now()->toDateTimeString(),
    ];

    event(new NewNotification($senderNotification,$user->id));

    return 'Event fired';
})->middleware('auth');
Route::middleware(['auth'])->group(function () {
    // Wallet
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notification.index');
    Route::get('/wallet/add-funds', [WalletController::class, 'showAddFundsForm'])->name('wallet.add-funds.form');
    Route::post('/wallet/add-funds', [WalletController::class, 'addFunds'])->name('wallet.add-funds');

    Route::get('/transfer', [TransferController::class, 'showTransferForm'])->name('transfer.form');
    Route::post('/transfer', [TransferController::class, 'transfer'])->name('transfer.send');

    Route::get('/currency/convert', [CurrencyController::class, 'showConversionForm'])
         ->name('currency.index');

    // Process the conversion.
    Route::post('/currency/convert', [CurrencyController::class, 'convertCurrency'])
         ->name('currency.convert');

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');

    // Paystack
    Route::post('/wallet/paystack/initialize', [WalletController::class, 'initializePaystackPayment'])->name('wallet.paystack.initialize');
    Route::post('/wallet/paystack/verify-inline', [WalletController::class, 'verifyPaystackPaymentInline'])->name('wallet.paystack.verify.inline');

});

