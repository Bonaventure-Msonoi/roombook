<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\VehicleBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VehicleBookingCreatedAlert extends Notification
{
    use Queueable;

    public function __construct(
        private readonly VehicleBooking $booking,
        private readonly User $actor
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $owner = $this->booking->user;
        $vehicle = $this->booking->vehicle;
        $bookedFor = $owner && $owner->is($this->actor)
            ? $owner->name
            : $owner?->name.' (booked by '.$this->actor->name.')';

        return (new MailMessage)
            ->subject('New vehicle booking created')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A vehicle booking has been created.')
            ->line('Booked for: '.$bookedFor)
            ->line('Department: '.($owner?->department ?? 'N/A'))
            ->line('Trip purpose: '.$this->booking->title)
            ->line('Vehicle: '.($vehicle?->displayName() ?? 'N/A'))
            ->line('Destination: '.$this->booking->destination)
            ->line('Start time: '.$this->booking->starts_at?->format('Y-m-d H:i'))
            ->line('End time: '.$this->booking->ends_at?->format('Y-m-d H:i'))
            ->action('View vehicle bookings', url(route('vehicles.bookings.admin', absolute: false)));
    }
}
