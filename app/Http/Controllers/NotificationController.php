<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        // Fetch all notifications for the user (persisted in the notifications table)
        $notifications = $user->notifications()->latest()->get();

        return view('notifications.index', compact('notifications'));
    }
}
