<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Wallets') }}
        </h2>
    </x-slot>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">My Wallets</h1>
        @foreach($wallets as $wallet)
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-4 wallet-container" data-wallet-id="{{ $wallet->id }}">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-12 w-12 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM12 2a10 10 0 100 20 10 10 0 000-20z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                            Balance
                        </div>
                        <!-- Balance element with data attributes -->
                        <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100 wallet-balance"
                             data-balance="{{ $wallet->balance ?? '0.00' }}"
                             data-currency="{{ $wallet->currency ?? 'NGN' }}">
                            {{ $wallet->balance ?? '0.00' }} {{ $wallet->currency ?? 'NGN' }}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="mt-6">
            <a href="{{ route('wallet.add-funds.form') }}"
               class="inline-flex items-center bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Funds
            </a>
        </div>
    </div>

    @push('scripts')
        <script>
            let currentUserId = {{ auth()->id() }};
            console.log('Current user ID:', currentUserId);

            document.addEventListener('DOMContentLoaded', function() {
                if(window.Echo){
                    console.log('Echo is available.');
                    window.Echo.private('wallets.' + currentUserId)
                        .listen('.wallet-update', (e) => {
                            console.log('Wallet update received:', e);
                            let walletContainer = document.querySelector(`.wallet-container[data-wallet-id="${e.wallet_id}"]`);
                            if (walletContainer) {
                                let balanceEl = walletContainer.querySelector('.wallet-balance');
                                balanceEl.textContent = parseFloat(e.balance).toFixed(2) + ' ' + e.currency;
                                balanceEl.setAttribute('data-balance', e.balance);
                                balanceEl.setAttribute('data-currency', e.currency);
                            }
                        });
                } else {
                    console.error('Echo is not available.');
                }
            });
        </script>
    @endpush
</x-app-layout>
