<?php

namespace App\Http\Requests;

use App\Models\VehicleBooking;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateVehicleBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var VehicleBooking $booking */
        $booking = $this->route('vehicleBooking');
        $user = $this->user();

        if (! $user || ! $booking) {
            return false;
        }

        return $booking->user_id === $user->id || $user->canManageAnyBooking();
    }

    public function rules(): array
    {
        $rules = [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var VehicleBooking $booking */
            $booking = $this->route('vehicleBooking');
            $startsAt = Carbon::parse($this->input('starts_at'));
            $endsAt = Carbon::parse($this->input('ends_at'));

            if (VehicleBooking::hasConflict((int) $this->input('vehicle_id'), $startsAt, $endsAt, $booking->id)) {
                $validator->errors()->add('starts_at', 'This vehicle is already booked for the selected time.');
            }
        });
    }
}
