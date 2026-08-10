<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function availability(Request $request): JsonResponse
    {
        $request->validate([
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        $startsAt = Carbon::parse($request->input('starts_at'));
        $endsAt = Carbon::parse($request->input('ends_at'));

        $rooms = Room::orderBy('name')->get()->map(fn (Room $room) => [
            'id' => $room->id,
            'name' => $room->name,
            'capacity' => $room->capacity,
            'color' => $room->color,
            'available' => ! Booking::hasConflict($room->id, $startsAt, $endsAt),
        ]);

        return response()->json($rooms);
    }

    public function create(Request $request): View
    {
        return view('bookings.form', [
            'booking' => new Booking(),
            'rooms' => Room::orderBy('name')->get(),
            'users' => $this->selectableUsers($request->user()),
            'canBookOnBehalf' => $request->user()->canBookOnBehalf(),
            'canReassign' => false,
            'selectedRoomId' => $request->integer('room'),
            'selectedDate' => $request->input('date'),
            'selectedStartsAt' => $request->input('starts_at'),
            'selectedEndsAt' => $request->input('ends_at'),
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $ownerId = $request->bookingOwnerId();

        $booking = Booking::create([
            'room_id' => $request->integer('room_id'),
            'user_id' => $ownerId,
            'title' => $request->input('title'),
            'starts_at' => $request->input('starts_at'),
            'ends_at' => $request->input('ends_at'),
            'status' => 'confirmed',
        ]);

        $booking->load(['room', 'user']);
        $onBehalf = $ownerId !== $request->user()->id;

        AuditLogger::log(
            $onBehalf ? 'booking.created_on_behalf' : 'booking.created',
            $onBehalf
                ? "Room booking created on behalf of {$booking->user->name}: {$booking->title}"
                : "Room booking created: {$booking->title}",
            $booking,
            [
                'room' => $booking->room?->name,
                'owner' => $booking->user?->email,
                'starts_at' => (string) $booking->starts_at,
                'ends_at' => (string) $booking->ends_at,
                'reason' => $request->input('reason'),
            ]
        );

        return redirect()
            ->route('boardroom.dashboard')
            ->with('status', $onBehalf ? 'Room booked on behalf of '.$booking->user->name.'.' : 'Room booked successfully.');
    }

    public function edit(Booking $booking): View
    {
        $user = auth()->user();

        if ($booking->user_id !== $user->id && ! $user->canManageAnyBooking()) {
            abort(403);
        }

        if ($booking->status !== 'confirmed' || $booking->ends_at->isPast()) {
            return redirect()
                ->route('boardroom.bookings.admin')
                ->with('error', 'Only upcoming confirmed bookings can be edited.');
        }

        return view('bookings.form', [
            'booking' => $booking,
            'rooms' => Room::orderBy('name')->get(),
            'users' => $this->selectableUsers($user),
            'canBookOnBehalf' => false,
            'canReassign' => $user->canManageAnyBooking(),
            'selectedRoomId' => $booking->room_id,
            'selectedDate' => null,
            'selectedStartsAt' => $booking->starts_at->format('Y-m-d\TH:i'),
            'selectedEndsAt' => $booking->ends_at->format('Y-m-d\TH:i'),
        ]);
    }

    public function update(UpdateBookingRequest $request, Booking $booking): RedirectResponse
    {
        $before = [
            'room_id' => $booking->room_id,
            'user_id' => $booking->user_id,
            'title' => $booking->title,
            'starts_at' => (string) $booking->starts_at,
            'ends_at' => (string) $booking->ends_at,
        ];

        $data = [
            'room_id' => $request->integer('room_id'),
            'title' => $request->input('title'),
            'starts_at' => $request->input('starts_at'),
            'ends_at' => $request->input('ends_at'),
        ];

        if ($request->user()->canManageAnyBooking()) {
            $data['user_id'] = $request->integer('user_id');
        }

        $booking->update($data);
        $booking->load(['room', 'user']);

        AuditLogger::log(
            'booking.updated',
            "Room booking updated: {$booking->title}",
            $booking,
            [
                'before' => $before,
                'after' => [
                    'room_id' => $booking->room_id,
                    'user_id' => $booking->user_id,
                    'title' => $booking->title,
                    'starts_at' => (string) $booking->starts_at,
                    'ends_at' => (string) $booking->ends_at,
                    'room' => $booking->room?->name,
                    'owner' => $booking->user?->email,
                ],
                'reason' => $request->input('reason'),
            ]
        );

        $redirect = $request->user()->canAccessAdminDashboard()
            ? route('boardroom.bookings.admin')
            : route('boardroom.bookings.mine');

        return redirect($redirect)->with('status', 'Booking updated successfully.');
    }

    public function mine(): View
    {
        $bookings = Booking::with('room')
            ->where('user_id', auth()->id())
            ->confirmed()
            ->where('ends_at', '>=', now()->subDay())
            ->orderBy('starts_at')
            ->get();

        return view('bookings.mine', compact('bookings'));
    }

    public function adminIndex(): View
    {
        $bookings = Booking::with(['room', 'user'])
            ->confirmed()
            ->where('ends_at', '>=', now()->subDay())
            ->orderBy('starts_at')
            ->get();

        return view('bookings.admin', compact('bookings'));
    }

    public function destroy(Request $request, Booking $booking): RedirectResponse
    {
        $user = auth()->user();

        if ($booking->user_id !== $user->id && ! $user->canCancelAnyBooking()) {
            abort(403);
        }

        $booking->load(['room', 'user']);
        $booking->update(['status' => 'cancelled']);

        AuditLogger::log(
            'booking.cancelled',
            "Room booking cancelled: {$booking->title}",
            $booking,
            [
                'room' => $booking->room?->name,
                'owner_id' => $booking->user_id,
                'owner' => $booking->user?->email,
                'reason' => $request->input('reason'),
            ]
        );

        return back()->with('status', 'Booking cancelled.');
    }

    private function selectableUsers(User $actor)
    {
        if (! $actor->canBookOnBehalf() && ! $actor->canManageAnyBooking()) {
            return collect();
        }

        return User::active()->orderBy('name')->get(['id', 'name', 'email', 'role']);
    }
}
