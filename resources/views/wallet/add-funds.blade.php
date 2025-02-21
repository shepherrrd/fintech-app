<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add Funds') }}
        </h2>
    </x-slot>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Add Funds</h1>
        <div class="mb-4">
            <label for="amount" class="block mb-2">Amount (NGN)</label>
            <input type="number" id="amount" min="1" step="0.01"
                   class="border border-gray-300 rounded px-4 py-2 w-full" placeholder="Enter amount" required>
        </div>
        <div class="mt-4">
            <button id="payButton" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                Pay with Paystack
            </button>
        </div>
    </div>

    @push('scripts')
        <script src="https://js.paystack.co/v1/inline.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('payButton').addEventListener('click', function(e) {
                    e.preventDefault();
                    let amount = document.getElementById('amount').value;
                    if (!amount || amount <= 0) {
                        Toastify({
                            text: "Please enter a valid amount",
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#f56565"
                        }).showToast();
                        return;
                    }
                    let amountInKobo = amount * 100;
                    let reference = 'PS_' + Math.floor((Math.random() * 1000000000) + 1);

                    let handler = PaystackPop.setup({
                        key: '{{ config("services.paystack.public_key") }}',
                        email: '{{ auth()->user()->email }}',
                        amount: amountInKobo,
                        currency: 'NGN',
                        ref: reference,
                        callback: function(response) {
                            verifyPayment(response.reference);
                        },
                        onClose: function(){
                            Toastify({
                                text: "Payment window closed.",
                                duration: 3000,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "#f56565"
                            }).showToast();
                        }
                    });
                    handler.openIframe();
                });

                function verifyPayment(reference) {
                    fetch("{{ route('wallet.paystack.verify.inline') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ reference: reference })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Toastify({
                                text: "Payment successful! Your wallet has been topped up.",
                                duration: 3000,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "#48bb78"
                            }).showToast();
                            window.location.href = "{{ route('wallet.index') }}";
                        } else {
                            Toastify({
                                text: "Payment verification failed: " + data.message,
                                duration: 3000,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "#f56565"
                            }).showToast();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Toastify({
                            text: "An error occurred while verifying payment.",
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#f56565"
                        }).showToast();
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
