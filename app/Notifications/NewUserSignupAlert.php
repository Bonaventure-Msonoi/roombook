<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserSignupAlert extends Notification
{
    use Queueable;

    public function __construct(
        private readonly User $user
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New user signup pending approval')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A new user has signed up and is waiting for approval.')
            ->line('Name: '.$this->user->name)
            ->line('Email: '.$this->user->email)
            ->line('Department: '.$this->user->department)
            ->action('Review users', url(route('admin.users.index', absolute: false)));
    }
}
