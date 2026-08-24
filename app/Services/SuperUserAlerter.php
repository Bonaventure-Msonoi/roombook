<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class SuperUserAlerter
{
    public static function notify(object $notification): void
    {
        try {
            Notification::send(
                User::query()->superUsers()->active()->get(),
                $notification
            );
        } catch (Throwable $e) {
            Log::warning('Failed to notify super users.', [
                'notification' => $notification::class,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
