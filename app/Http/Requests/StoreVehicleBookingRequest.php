<?php

namespace App\Http\Requests;

use App\Models\VehicleBooking;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreVehicleBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $rules = [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date', 'after:now', 'before:'.now()->addMonths(6)->endOfDay()->toDateTimeString()],
            'ends_at' => ['required', 'date', 'after:starts_at', 'before:'.now()->addMonths(6)->endOfDay()->toDateTimeString()],
            'reason' => ['nullable', 'string', 'max:500'],
        ];

        if ($this->user()?->canBookOnBehalf()) {
            $rules['user_id'] = ['nullable', 'exists:users,id'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ($this->filled('user_id') && ! $this->user()?->canBookOnBehalf()) {
                $validator->errors()->add('user_id', 'You cannot book on behalf of another user.');

                return;
            }

            $startsAt = Carbon::parse($this->input('starts_at'));
            $endsAt = Carbon::parse($this->input('ends_at'));

            if (VehicleBooking::hasConflict((int) $this->input('vehicle_id'), $startsAt, $endsAt)) {
                $validator->errors()->add('starts_at', 'This vehicle is already booked for the selected time.');
            }
        });
    }

    public function bookingOwnerId(): int
    {
        if ($this->user()?->canBookOnBehalf() && $this->filled('user_id')) {
            return (int) $this->input('user_id');
        }

        return (int) $this->user()->id;
    }
}
