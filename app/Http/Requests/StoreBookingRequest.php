<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'room_id' => ['required', 'exists:rooms,id'],
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date', 'after:now', 'before:'.now()->addMonths(6)->endOfDay()->toDateTimeString()],
            'ends_at' => ['required', 'date', 'after:starts_at', 'before:'.now()->addMonths(6)->endOfDay()->toDateTimeString()],
        ];
    }

    public function messages(): array
    {
        return [
            'starts_at.after' => 'The start time must be in the future.',
            'starts_at.before' => 'Bookings can only be made up to 6 months ahead.',
            'ends_at.after' => 'The end time must be after the start time.',
            'ends_at.before' => 'Bookings can only be made up to 6 months ahead.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $startsAt = Carbon::parse($this->input('starts_at'));
            $endsAt = Carbon::parse($this->input('ends_at'));

            if (Booking::hasConflict((int) $this->input('room_id'), $startsAt, $endsAt)) {
                $validator->errors()->add('starts_at', 'This room is already booked for the selected time.');
            }
        });
    }
}
