<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Notifications') }}
        </h2>
    </x-slot>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Notifications</h1>
        <div class="mb-4">
            You have <span id="notificationCount" class="font-semibold">{{ auth()->user()->unreadNotifications->count() }}</span> new notifications.
        </div>
        <ul id="notificationList" class="list-disc pl-5 space-y-4">
            @foreach(auth()->user()->notifications as $notification)
                <li class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="text-gray-900 dark:text-gray-100">
                            {{ $notification->data['message'] }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <small>{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/min/moment.min.js"></script>
        <script>
            let currentUserId = {{ auth()->id() }};
            document.addEventListener('DOMContentLoaded', function() {
                if(window.Echo){
                    console.log('Echo found');
                    window.Echo.private('notifications.' + currentUserId)
                        .listen('.new-notification', (e) => {
                            console.log('New notification event:', e);
                            let countEl = document.getElementById('notificationCount');
                            let currentCount = parseInt(countEl.innerText) || 0;
                            countEl.innerText = currentCount + 1;
                            let listEl = document.getElementById('notificationList');
                            let li = document.createElement('li');
                            li.classList.add('bg-white', 'dark:bg-gray-800', 'shadow', 'rounded-lg', 'p-4', 'flex', 'items-center', 'justify-between');
                            li.innerHTML = `<div class="text-gray-900 dark:text-gray-100">${e.notification.message}</div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400"><small>${moment().fromNow()}</small></div>`;
                            listEl.prepend(li);
                        });
                } else {
                    console.log('Echo not found');
                }
            });
        </script>
    @endpush
</x-app-layout>
