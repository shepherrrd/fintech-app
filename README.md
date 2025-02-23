# Fintech Application

A mini Fintech application built with Laravel 11, Tailwind CSS, and Laravel Echo that includes:

- **User Authentication:** Custom auth system (without email verification).
- **Wallet Management:** Add funds via Paystack (inline integration with real-time verification).
- **User-to-User Transfers:** Send funds and view transaction history.
- **Real-Time Updates:** Receive broadcasted events for transactions, wallet updates and notifications using Reverb (a Pusher‑compatible WebSocket service).
- **Persistent Notifications:** Store notifications in the database and update them in real time.
- **Multi-Wallet System:** Manage multiple wallets per user, each with different currencies.
- **Currency Conversion:** Convert funds between wallets using real-time exchange rates.

---

## Table of Contents

- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Environment Configuration](#environment-configuration)
- [Database Setup](#database-setup)
- [Building Assets](#building-assets)
- [Running the Application](#running-the-application)
- [Real-Time Broadcasting](#real-time-broadcasting)
- [Additional Commands](#additional-commands)
- [Troubleshooting](#troubleshooting)
- [License](#license)

---

## Prerequisites

- **PHP:** Version 8.2 or higher (if needed, override platform in composer.json)
- **Composer:** Dependency manager for PHP
- **Node.js & npm:** For building front-end assets (Vite is used)
- **Database:** MySQL, PostgreSQL, or your preferred supported database
- **WebSocket Service:** Reverb (or any Pusher‑compatible service)

---

## Installation

1. **Clone the Repository**

    ```bash
    git clone https://github.com/shepherrrd/fintech-app.git
    cd fintech-app
    ```

2. **Install Composer Dependencies**

    ```bash
    composer install
    ```

3. **Install Node Dependencies**

    ```bash
    npm install
    ```

4. **Copy Environment File**

    Copy `.env.example` to `.env`:

    ```bash
    cp .env.example .env
    ```

5. **Generate Application Key**

    ```bash
    php artisan key:generate
    ```

## Environment Configuration

Edit your `.env` file to configure your database, Paystack, broadcasting, and queue settings.

### Example .env Configuration

```env
APP_NAME="Fintech Application"
APP_ENV=local
APP_KEY=base64:YourGeneratedKeyHere
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fintech_app
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=pusher
CACHE_DRIVER=file
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Paystack Credentials
PAYSTACK_PUBLIC_KEY=your_paystack_public_key
PAYSTACK_SECRET_KEY=your_paystack_secret_key
PAYSTACK_PAYMENT_URL=https://api.paystack.co
MERCHANT_EMAIL=your_merchant_email

# Reverb (Pusher-Compatible) Credentials
REVERB_APP_ID=your_reverb_app_id
REVERB_APP_KEY=your_reverb_app_key
REVERB_APP_SECRET=your_reverb_app_secret
REVERB_APP_CLUSTER=your_reverb_app_cluster

# Vite Environment Variables (for Laravel Echo)
VITE_REVERB_APP_KEY=${REVERB_APP_KEY}
VITE_REVERB_APP_CLUSTER=${REVERB_APP_CLUSTER}
VITE_REVERB_HOST=your-reverb-host.com
VITE_REVERB_APP_PORT=6001
```


## Database Setup

1. **Run Migrations**

    Create necessary tables (including users, wallets, transactions, and notifications):

    ```bash
    php artisan migrate
    ```


3. **Notifications Table**

    Laravel includes a migration for notifications. If not already present, generate and run:

    ```bash
    php artisan notifications:table
    php artisan migrate
    ```

## Building Assets

Use Vite to compile your CSS and JavaScript:

### For Development (with hot-reload):

```bash
npm run dev
```

### For Production Build:

```bash
npm run build
```

## Running the Application

1. **Start the Laravel Development Server**

    ```bash
    php artisan serve
    ```

    The application will be available at [http://localhost:8000](http://localhost:8000).

2. **Start the Queue Worker**

    To process queued jobs (such as notification broadcasting):

    ```bash
    php artisan queue:work
    ```

3. **Start the WebSocket Service**

    If you’re using Reverb as a hosted service, ensure your credentials in the `.env` file are correct.
    If using a local WebSocket server (e.g., laravel-websockets), run:

    ```bash
    php artisan reverb:start
    ```

    Ensure your front-end Echo configuration points to the correct host and port.

## Real-Time Broadcasting

The application uses Laravel Echo to subscribe to private channels for real-time updates:

### Transactions:

Real-time updates for sent and received transactions are broadcast on channels like `private('transactions.{userId}')`.

### Notifications:

Persistent notifications are stored in the database and also broadcast via private channels `private('notifications.{userId}')`.

### Front-End Example (JavaScript)

Your `resources/js/bootstrap.js` should initialize Echo as follows:

```js
import Pusher from 'pusher-js';
import Echo from 'laravel-echo';

window.Pusher = Pusher;

window.Echo = new Echo({
     broadcaster: 'pusher',
     key: import.meta.env.VITE_REVERB_APP_KEY,
     cluster: import.meta.env.VITE_REVERB_APP_CLUSTER,
     wsHost: import.meta.env.VITE_REVERB_HOST,
     wsPort: import.meta.env.VITE_REVERB_APP_PORT,
     forceTLS: false,
     disableStats: true,
});
```

Make sure to rebuild your assets after making changes:

```bash
npm run dev
```

## Additional Commands

### Running Tests:

If your project includes tests, run:

```bash
php artisan test
```

### Restarting the Queue Worker:

After code changes affecting queued jobs, restart the worker:

```bash
php artisan queue:restart
```

### Clearing Cache:

If configuration changes are not reflected, clear caches:

```bash
php artisan config:clear
php artisan cache:clear
```

## Troubleshooting

### WebSocket Issues:

- Verify that your `.env` values for Reverb are correct.
- Check the browser console and network tab for WebSocket connection status.
- Ensure your WebSocket service is running and accessible.

### Queue Worker:

- Ensure the queue worker is running to process notifications (`php artisan queue:work`).

### Asset Compilation:

- If changes do not appear, try running `npm run build` or `npm run dev` again.

### Event Not Received:

- Verify your channel authorization in `routes/channels.php`:

  ```php
  Broadcast::channel('notifications.{userId}', function ($user, $userId) {
        return (int) $user->id === (int) $userId;
  });

  Broadcast::channel('transactions.{userId}', function ($user, $userId) {
        return (int) $user->id === (int) $userId;
  });
  ```

- Confirm that events are fired in your controllers (use logging to check).
- Check your Echo subscription code and the event names.


## Navigation

Use the dropdown menu at the top of the page to navigate through different sections of the application.
