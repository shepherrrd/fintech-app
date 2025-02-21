<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Wallet') }}
        </h2>
    </x-slot>
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold">My Wallet</h1>
    <div class="mt-4">
        <p class="text-lg">Balance: ${{ $wallet->balance ?? '0.00' }} {{ $wallet->currency ?? 'USD' }}</p>
    </div>
    <div class="mt-4">
        <a href="{{ route('wallet.add-funds.form') }}"
           class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
           Add Funds
        </a>
    </div>
</div>
</x-app-layout>
