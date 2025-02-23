<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Transfer Funds') }}
        </h2>
    </x-slot>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold">Transfer Funds</h1>
        <form action="{{ route('transfer.send') }}" method="POST" class="mt-4">
            @csrf
            <div class="mb-4">
                <label for="email_search" class="block mb-2">Search Receiver by Email</label>
                <input type="text" id="email_search" class="border border-gray-300 rounded px-4 py-2 w-full" placeholder="Enter email to search">
            </div>
            <div class="mb-4">
                <label for="receiver_id" class="block mb-2">Select Receiver</label>
                <select name="receiver_id" id="receiver_id" class="border border-gray-300 rounded px-4 py-2 w-full">
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" data-email="{{ $user->email }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
                @error('receiver_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="wallet_id" class="block mb-2">Select Wallet</label>
                <select name="wallet_id" id="wallet_id" class="border border-gray-300 rounded px-4 py-2 w-full">
                    @foreach($user->wallets as $wallet)
                    <option value="{{ $wallet->id }}">{{ $wallet->currency }}</option>
                    @endforeach
                </select>
                @error('wallet_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="amount" class="block mb-2">Amount</label>
                <input type="number" name="amount" id="amount" min="1" step="0.01" class="border border-gray-300 rounded px-4 py-2 w-full" required>
                @error('amount')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Transfer
            </button>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const emailSearchInput = document.getElementById('email_search');
            const receiverSelect = document.getElementById('receiver_id');
            const walletSelect = document.getElementById('wallet_id');

            emailSearchInput.addEventListener('input', function() {
                const searchValue = emailSearchInput.value.toLowerCase();
                for (const option of receiverSelect.options) {
                    const email = option.getAttribute('data-email').toLowerCase();
                    option.style.display = email.includes(searchValue) ? 'block' : 'none';
                }
            });

            receiverSelect.addEventListener('change', async function() {
                const userId = receiverSelect.value;
                if (userId) {
                    const response = await fetch(`/api/users/${userId}/wallets`);
                    const wallets = await response.json();
                    walletSelect.innerHTML = '';
                    wallets.forEach(wallet => {
                        const option = document.createElement('option');
                        option.value = wallet.id;
                        option.textContent = `${wallet.currency}`;
                        walletSelect.appendChild(option);
                    });
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
