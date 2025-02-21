<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Transaction History') }}
        </h2>
    </x-slot>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Transaction History</h1>

        <div class="mt-6">
            <h2 class="text-xl font-semibold mb-2">Sent Transactions</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white dark:bg-gray-800 shadow rounded-lg">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-700">
                            <th class="px-6 py-3 border-b-2 border-gray-200 dark:border-gray-600 text-left leading-4 text-gray-600 dark:text-gray-300 tracking-wider">ID</th>
                            <th class="px-6 py-3 border-b-2 border-gray-200 dark:border-gray-600 text-left leading-4 text-gray-600 dark:text-gray-300 tracking-wider">Receiver</th>
                            <th class="px-6 py-3 border-b-2 border-gray-200 dark:border-gray-600 text-left leading-4 text-gray-600 dark:text-gray-300 tracking-wider">Amount</th>
                            <th class="px-6 py-3 border-b-2 border-gray-200 dark:border-gray-600 text-left leading-4 text-gray-600 dark:text-gray-300 tracking-wider">Type</th>
                            <th class="px-6 py-3 border-b-2 border-gray-200 dark:border-gray-600 text-left leading-4 text-gray-600 dark:text-gray-300 tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody id="sentTransactions" class="bg-white dark:bg-gray-800">
                        @foreach($transactionsSent as $tx)
                        <tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">{{ $tx->id }}</td>
                            <td class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                                {{ optional($tx->receiver)->name ?? 'External' }}
                            </td>
                            <td class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">${{ $tx->amount }} {{ $tx->currency }}</td>
                            <td class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">{{ $tx->type }}</td>
                            <td class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($transactionsSent->isEmpty())
            <p class="mt-6 text-gray-600 dark:text-gray-400">No transactions sent yet.</p>
            @endif
        </div>

        <div class="mt-6">
            <h2 class="text-xl font-semibold mb-2">Received Transactions</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white dark:bg-gray-800 shadow rounded-lg">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-700">
                            <th class="px-6 py-3 border-b-2 border-gray-200 dark:border-gray-600 text-left leading-4 text-gray-600 dark:text-gray-300 tracking-wider">ID</th>
                            <th class="px-6 py-3 border-b-2 border-gray-200 dark:border-gray-600 text-left leading-4 text-gray-600 dark:text-gray-300 tracking-wider">Sender</th>
                            <th class="px-6 py-3 border-b-2 border-gray-200 dark:border-gray-600 text-left leading-4 text-gray-600 dark:text-gray-300 tracking-wider">Amount</th>
                            <th class="px-6 py-3 border-b-2 border-gray-200 dark:border-gray-600 text-left leading-4 text-gray-600 dark:text-gray-300 tracking-wider">Type</th>
                            <th class="px-6 py-3 border-b-2 border-gray-200 dark:border-gray-600 text-left leading-4 text-gray-600 dark:text-gray-300 tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody id="receivedTransactions" class="bg-white dark:bg-gray-800">
                        @foreach($transactionsReceived as $tx)
                        <tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">{{ $tx->id }}</td>
                            <td class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                                {{ optional($tx->sender)->name ?? 'External' }}
                            </td>
                            <td class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">${{ $tx->amount }} {{ $tx->currency }}</td>
                            <td class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">{{ $tx->type }}</td>
                            <td class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($transactionsReceived->isEmpty())
            <p class="mt-6 text-gray-600 dark:text-gray-400">No transactions received yet.</p>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            let currentUserId = {{ auth()->id() }};
            console.log('Current user ID:', currentUserId);
            document.addEventListener('DOMContentLoaded', function() {
                if(window.Echo){
                    console.log('Echo found');
                    window.Echo.private('transactions.' + currentUserId)
                        .listen('.new-transaction', (e) => {
                            console.log('New transaction event received:', e);
                            let tx = e.transaction;
                            let newRow = `<tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">${tx.id}</td>
                                <td class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">`;
                            if(tx.sender_id == currentUserId) {
                                newRow += tx.receiver ? tx.receiver.name : 'External';
                            } else if(tx.receiver_id == currentUserId) {
                                newRow += tx.sender ? tx.sender.name : 'External';
                            }
                            newRow += `</td>
                                <td class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">$${tx.amount} ${tx.currency}</td>
                                <td class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">${tx.type}</td>
                                <td class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">${tx.created_at}</td>
                            </tr>`;
                            if(tx.sender_id == currentUserId) {
                                document.getElementById('sentTransactions').insertAdjacentHTML('afterbegin', newRow);
                            } else if(tx.receiver_id == currentUserId) {
                                document.getElementById('receivedTransactions').insertAdjacentHTML('afterbegin', newRow);
                            }
                        });
                }else{
                    console.log('Echo not found');
                }
            });
        </script>
    @endpush
</x-app-layout>
