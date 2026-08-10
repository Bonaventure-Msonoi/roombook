<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Booking $booking */
        $booking = $this->route('booking');
        $user = $this->user();

        if (! $user || ! $booking) {
            return false;
        }

        return $booking->user_id === $user->id || $user->canManageAnyBooking();
    }

    public function rules(): array
    {
        $rules = [
            'room_id' => ['required', 'exists:rooms,id'],
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date', 'before:'.now()->addMonths(6)->endOfDay()->toDateTimeString()],
            'ends_at' => ['required', 'date', 'after:starts_at', 'after:now', 'before:'.now()->addMonths(6)->endOfDay()->toDateTimeString()],
            'reason' => ['nullable', 'string', 'max:500'],
        ];

        if ($this->user()?->canManageAnyBooking()) {
            $rules['user_id'] = ['required', 'exists:users,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'ends_at.after' => 'The end time must be in the future and after the start time.',
            'ends_at.before' => 'Bookings can only be made up to 6 months ahead.',
            'starts_at.before' => 'Bookings can only be made up to 6 months ahead.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var Booking $booking */
            $booking = $this->route('booking');
            $startsAt = Carbon::parse($this->input('starts_at'));
            $endsAt = Carbon::parse($this->input('ends_at'));

            if (Booking::hasConflict((int) $this->input('room_id'), $startsAt, $endsAt, $booking->id)) {
                $validator->errors()->add('starts_at', 'This room is already booked for the selected time.');
            }
        });
    }
}
