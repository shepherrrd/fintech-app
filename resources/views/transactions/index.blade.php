<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Transaction History') }}
        </h2>
    </x-slot>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold">Transaction History</h1>

        <div class="mt-6">
            <h2 class="text-xl font-semibold">Sent Transactions</h2>
            <table class="min-w-full mt-2 border-collapse">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border">ID</th>
                        <th class="px-4 py-2 border">Receiver</th>
                        <th class="px-4 py-2 border">Amount</th>
                        <th class="px-4 py-2 border">Type</th>
                        <th class="px-4 py-2 border">Date</th>
                    </tr>
                </thead>
                <tbody id="sentTransactions">
                    @foreach($transactionsSent as $tx)
                    <tr>
                        <td class="px-4 py-2 border">{{ $tx->id }}</td>
                        <td class="px-4 py-2 border">
                            {{ optional($tx->receiver)->name ?? 'External' }}
                        </td>
                        <td class="px-4 py-2 border">${{ $tx->amount }} {{ $tx->currency }}</td>
                        <td class="px-4 py-2 border">{{ $tx->type }}</td>
                        <td class="px-4 py-2 border">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if ($transactionsSent->isEmpty())
            <p class="mt-6 text-gray-600 dark:text-gray-400">No transactions sent yet.</p>
            @endif
        </div>

        <div class="mt-6">
            <h2 class="text-xl font-semibold">Received Transactions</h2>
            <table class="min-w-full mt-2 border-collapse">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border">ID</th>
                        <th class="px-4 py-2 border">Sender</th>
                        <th class="px-4 py-2 border">Amount</th>
                        <th class="px-4 py-2 border">Type</th>
                        <th class="px-4 py-2 border">Date</th>
                    </tr>
                </thead>
                <tbody id="receivedTransactions">
                    @foreach($transactionsReceived as $tx)
                    <tr>
                        <td class="px-4 py-2 border">{{ $tx->id }}</td>
                        <td class="px-4 py-2 border">
                            {{ optional($tx->sender)->name ?? 'External' }}
                        </td>
                        <td class="px-4 py-2 border">${{ $tx->amount }} {{ $tx->currency }}</td>
                        <td class="px-4 py-2 border">{{ $tx->type }}</td>
                        <td class="px-4 py-2 border">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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
                    let newRow = `<tr>
                        <td class="px-4 py-2 border">${tx.id}</td>
                        <td class="px-4 py-2 border">`;
                    if(tx.sender_id == currentUserId) {
                        newRow += tx.receiver ? tx.receiver.name : 'External';
                    } else if(tx.receiver_id == currentUserId) {
                        newRow += tx.sender ? tx.sender.name : 'External';
                    }
                    newRow += `</td>
                        <td class="px-4 py-2 border">$${tx.amount} ${tx.currency}</td>
                        <td class="px-4 py-2 border">${tx.type}</td>
                        <td class="px-4 py-2 border">${tx.created_at}</td>
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
