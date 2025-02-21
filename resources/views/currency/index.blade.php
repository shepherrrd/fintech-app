<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Currency Conversion') }}
        </h2>
    </x-slot>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Currency Conversion</h1>

        @if ($errors->any())
            <div class="mb-4">
                <ul class="text-red-500">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('currency.convert') }}" method="POST" class="mb-6">
            @csrf
            <div class="mb-4">
                <label for="amount" class="block mb-2">Amount</label>
                <input type="number" name="amount" id="amount" step="0.01" min="1"
                       class="border rounded px-4 py-2 w-full" placeholder="Enter amount" required>
            </div>
            <div class="mb-4">
                <label for="from_currency" class="block mb-2">From Currency (e.g., USD)</label>
                <input type="text" name="from_currency" id="from_currency" maxlength="3"
                       class="border rounded px-4 py-2 w-full" placeholder="USD" required>
            </div>
            <div class="mb-4">
                <label for="to_currency" class="block mb-2">To Currency (e.g., NGN)</label>
                <input type="text" name="to_currency" id="to_currency" maxlength="3"
                       class="border rounded px-4 py-2 w-full" placeholder="NGN" required>
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Convert
            </button>
        </form>

        @isset($convertedAmount)
            <div class="bg-green-100 p-4 rounded">
                <p class="text-xl font-semibold">Conversion Result:</p>
                <p>{{ $amount }} {{ $from }} = {{ $convertedAmount }} {{ $to }}</p>
                <p>Exchange Rate: {{ $rate }}</p>
                <p class="mt-2 text-sm text-gray-600">Transaction ID: {{ $transaction->id }}</p>
            </div>
        @endisset
    </div>
</x-app-layout>
