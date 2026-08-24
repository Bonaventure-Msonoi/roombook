<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use App\Support\ExcelWorkbook;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    private const BUSINESS_HOURS_PER_DAY = 9;

    public function index(Request $request): View
    {
        [$from, $to] = $this->resolveRange($request);

        return view('admin.analytics.index', $this->analyticsPayload($from, $to, forDisplay: true));
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->resolveRange($request);
        $data = $this->analyticsPayload($from, $to, forDisplay: false);
        $workbook = $this->buildWorkbook($data);
        $filename = sprintf(
            'reservio-vehicle-usage-%s-to-%s.xlsx',
            $from->format('Y-m-d_Hi'),
            $to->format('Y-m-d_Hi')
        );

        return response()->streamDownload(
            function () use ($workbook) {
                echo $workbook->toXlsx();
            },
            $filename,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveRange(Request $request): array
    {
        $defaults = [
            'from' => now()->startOfMonth()->format('Y-m-d\TH:i'),
            'to' => now()->endOfMonth()->format('Y-m-d\TH:i'),
        ];

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $fromInput = $validated['from'] ?? $defaults['from'];
        $toInput = $validated['to'] ?? $defaults['to'];

        $from = Carbon::parse($fromInput)->startOfMinute();
        $to = Carbon::parse($toInput)->endOfMinute();

        if ($to->lt($from)) {
            throw ValidationException::withMessages([
                'to' => 'The end of the range must be on or after the start.',
            ]);
        }

        if ($from->diffInDays($to) > 366) {
            throw ValidationException::withMessages([
                'to' => 'Please choose a range of 12 months or less.',
            ]);
        }

        return [$from, $to];
    }

    /**
     * @return array<string, mixed>
     */
    private function analyticsPayload(Carbon $from, Carbon $to, bool $forDisplay): array
    {
        $now = now();
        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();

        $pendingLimit = $forDisplay ? 8 : null;
        $listLimit = $forDisplay ? 5 : null;
        $auditLimit = $forDisplay ? 10 : 200;
        $upcomingLimit = $forDisplay ? 5 : 50;

        $pendingQuery = User::pendingApproval()->orderByDesc('created_at');
        $pendingCount = User::pendingApproval()->count();
        $pendingApprovals = $pendingLimit
            ? $pendingQuery->limit($pendingLimit)->get()
            : $pendingQuery->get();

        $boardroomToday = $this->confirmedCount(Booking::class, $todayStart, $todayEnd);
        $boardroomWeek = $this->confirmedCount(Booking::class, $weekStart, $weekEnd);
        $boardroomPeriod = $this->confirmedCount(Booking::class, $from, $to);
        $boardroomCancelledPeriod = $this->cancelledCount(Booking::class, $from, $to);

        $vehicleToday = $this->confirmedCount(VehicleBooking::class, $todayStart, $todayEnd);
        $vehicleWeek = $this->confirmedCount(VehicleBooking::class, $weekStart, $weekEnd);
        $vehiclePeriod = $this->confirmedCount(VehicleBooking::class, $from, $to);
        $vehicleCancelledPeriod = $this->cancelledCount(VehicleBooking::class, $from, $to);

        $roomCount = max(Room::count(), 0);
        $activeVehicleCount = max(Vehicle::active()->count(), 0);

        $boardroomUtilization = $roomCount > 0
            ? $this->utilizationPercent(Booking::class, $from, $to, $roomCount)
            : 0.0;

        $vehicleUtilization = $activeVehicleCount > 0
            ? $this->utilizationPercent(VehicleBooking::class, $from, $to, $activeVehicleCount)
            : 0.0;

        $topRooms = $this->topResources(
            $this->resourceCounts(Booking::class, 'room_id', $from, $to, $listLimit),
            Room::class
        );

        $topVehicles = $this->topResources(
            $this->resourceCounts(VehicleBooking::class, 'vehicle_id', $from, $to, $listLimit),
            Vehicle::class
        );

        $bookingsByDepartment = $this->bookingsByDepartment($from, $to);
        $peakDays = $this->peakDays($from, $to);
        $peakMax = max(1, (int) $peakDays->max('count'));

        $recentAudit = AuditLog::with('user')
            ->whereIn('action', [
                'user.approved',
                'user.rejected',
                'user.deactivated',
                'user.reactivated',
                'booking.cancelled',
                'booking.updated',
                'vehicle_booking.cancelled',
                'vehicle_booking.updated',
                'role_privileges.updated',
            ])
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->limit($auditLimit)
            ->get();

        $upcomingBoardroom = Booking::with(['room', 'user'])
            ->confirmed()
            ->where('starts_at', '>=', $now)
            ->where('starts_at', '<=', $now->copy()->addDays(7))
            ->orderBy('starts_at')
            ->limit($upcomingLimit)
            ->get();

        $upcomingVehicles = VehicleBooking::with(['vehicle', 'user'])
            ->confirmed()
            ->where('starts_at', '>=', $now)
            ->where('starts_at', '<=', $now->copy()->addDays(7))
            ->orderBy('starts_at')
            ->limit($upcomingLimit)
            ->get();

        $periodBoardroomBookings = Booking::with(['room', 'user'])
            ->confirmed()
            ->where('starts_at', '<', $to)
            ->where('ends_at', '>', $from)
            ->orderBy('starts_at')
            ->get();

        $periodVehicleBookings = VehicleBooking::with(['vehicle', 'user'])
            ->whereBetween('starts_at', [$from, $to])
            ->orderBy('starts_at')
            ->get();

        $periodLabel = $from->format('d M Y H:i').' – '.$to->format('d M Y H:i');

        return [
            'periodLabel' => $periodLabel,
            'fromValue' => $from->format('Y-m-d\TH:i'),
            'toValue' => $to->format('Y-m-d\TH:i'),
            'from' => $from,
            'to' => $to,
            'generatedAt' => $now->toDateTimeString(),
            'pendingCount' => $pendingCount,
            'pendingApprovals' => $pendingApprovals,
            'stats' => [
                'boardroom_today' => $boardroomToday,
                'boardroom_week' => $boardroomWeek,
                'boardroom_period' => $boardroomPeriod,
                'boardroom_cancelled_period' => $boardroomCancelledPeriod,
                'vehicle_today' => $vehicleToday,
                'vehicle_week' => $vehicleWeek,
                'vehicle_period' => $vehiclePeriod,
                'vehicle_cancelled_period' => $vehicleCancelledPeriod,
                'boardroom_utilization' => $boardroomUtilization,
                'vehicle_utilization' => $vehicleUtilization,
                'active_users' => User::active()->count(),
                'room_count' => $roomCount,
                'active_vehicle_count' => $activeVehicleCount,
            ],
            'topRooms' => $topRooms,
            'topVehicles' => $topVehicles,
            'bookingsByDepartment' => $bookingsByDepartment,
            'peakDays' => $peakDays,
            'peakMax' => $peakMax,
            'recentAudit' => $recentAudit,
            'upcomingBoardroom' => $upcomingBoardroom,
            'upcomingVehicles' => $upcomingVehicles,
            'periodBoardroomBookings' => $periodBoardroomBookings,
            'periodVehicleBookings' => $periodVehicleBookings,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function buildWorkbook(array $data): ExcelWorkbook
    {
        $workbook = new ExcelWorkbook;

        $usageRows = $data['periodVehicleBookings']->map(fn (VehicleBooking $booking) => [
            $booking->user?->name ?? '',
            $booking->user?->department ?? 'Unassigned',
            $booking->title ?? '',
            $booking->vehicle?->displayName() ?? '',
            $booking->destination ?? '',
            $booking->starts_at?->format('Y-m-d H:i') ?? '',
            $booking->ends_at?->format('Y-m-d H:i') ?? '',
            $booking->status ?? '',
        ])->all();

        $workbook->addSheet('Vehicle Usage', array_merge(
            [[
                'User name',
                'Department',
                'Trip purpose',
                'Vehicle used',
                'Destination',
                'Starts at',
                'Ends at',
                'Status',
            ]],
            $usageRows ?: [['No vehicle usage in this period', '', '', '', '', '', '', '']]
        ));

        $workbook->addSheet('Export Info', [
            ['Field', 'Value'],
            ['Period start', $data['from']->toDateTimeString()],
            ['Period end', $data['to']->toDateTimeString()],
            ['Period label', $data['periodLabel']],
            ['Generated at', $data['generatedAt']],
            ['Trips in period', count($usageRows)],
        ]);

        return $workbook;
    }

    private function resourceCounts(
        string $model,
        string $foreignKey,
        Carbon $start,
        Carbon $end,
        ?int $limit
    ): Collection {
        $query = $model::query()
            ->confirmed()
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->selectRaw("{$foreignKey} as resource_id, count(*) as bookings_count")
            ->groupBy($foreignKey)
            ->orderByDesc('bookings_count');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    private function confirmedCount(string $model, Carbon $start, Carbon $end): int
    {
        return $model::query()
            ->confirmed()
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->count();
    }

    private function cancelledCount(string $model, Carbon $start, Carbon $end): int
    {
        return $model::query()
            ->where('status', 'cancelled')
            ->whereBetween('updated_at', [$start, $end])
            ->count();
    }

    private function utilizationPercent(string $model, Carbon $start, Carbon $end, int $resourceCount): float
    {
        $bookings = $model::query()
            ->confirmed()
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->get(['starts_at', 'ends_at']);

        $bookedMinutes = $bookings->sum(function ($booking) use ($start, $end) {
            $from = $booking->starts_at->greaterThan($start) ? $booking->starts_at : $start;
            $to = $booking->ends_at->lessThan($end) ? $booking->ends_at : $end;

            return max(0, $from->diffInMinutes($to));
        });

        $availableMinutes = $this->weekdayCount($start, $end)
            * self::BUSINESS_HOURS_PER_DAY
            * 60
            * $resourceCount;

        if ($availableMinutes <= 0) {
            return 0.0;
        }

        return round(min(100, ($bookedMinutes / $availableMinutes) * 100), 1);
    }

    private function weekdayCount(Carbon $start, Carbon $end): int
    {
        $count = 0;
        $cursor = $start->copy()->startOfDay();
        $last = $end->copy()->startOfDay();

        while ($cursor->lte($last)) {
            if ($cursor->isWeekday()) {
                $count++;
            }
            $cursor->addDay();
        }

        return $count;
    }

    /**
     * @param  Collection<int, object>  $rows
     * @return Collection<int, array{label: string, count: int}>
     */
    private function topResources(Collection $rows, string $modelClass): Collection
    {
        $ids = $rows->pluck('resource_id')->filter()->all();
        $resources = $modelClass::query()->whereIn('id', $ids)->get()->keyBy('id');

        return $rows->map(function ($row) use ($resources, $modelClass) {
            $resource = $resources->get($row->resource_id);

            if (! $resource) {
                $label = 'Unknown';
            } elseif ($modelClass === Vehicle::class) {
                $label = $resource->displayName();
            } else {
                $label = $resource->name;
            }

            return [
                'label' => $label,
                'count' => (int) $row->bookings_count,
            ];
        });
    }

    /**
     * @return Collection<int, array{department: string, count: int}>
     */
    private function bookingsByDepartment(Carbon $start, Carbon $end): Collection
    {
        $boardroom = Booking::query()
            ->confirmed()
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->join('users', 'users.id', '=', 'bookings.user_id')
            ->selectRaw("COALESCE(users.department, 'Unassigned') as department, count(*) as bookings_count")
            ->groupBy('department')
            ->pluck('bookings_count', 'department');

        $vehicles = VehicleBooking::query()
            ->confirmed()
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->join('users', 'users.id', '=', 'vehicle_bookings.user_id')
            ->selectRaw("COALESCE(users.department, 'Unassigned') as department, count(*) as bookings_count")
            ->groupBy('department')
            ->pluck('bookings_count', 'department');

        return collect([...User::DEPARTMENTS, 'Unassigned'])
            ->map(fn (string $department) => [
                'department' => $department,
                'count' => (int) $boardroom->get($department, 0) + (int) $vehicles->get($department, 0),
            ])
            ->filter(fn (array $row) => $row['count'] > 0)
            ->sortByDesc('count')
            ->values();
    }

    /**
     * @return Collection<int, array{day: string, count: int}>
     */
    private function peakDays(Carbon $start, Carbon $end): Collection
    {
        $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $counts = array_fill(0, 7, 0);

        $starts = Booking::query()
            ->confirmed()
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->pluck('starts_at')
            ->concat(
                VehicleBooking::query()
                    ->confirmed()
                    ->where('starts_at', '<', $end)
                    ->where('ends_at', '>', $start)
                    ->pluck('starts_at')
            );

        foreach ($starts as $startsAt) {
            $index = $startsAt->dayOfWeekIso - 1;
            $counts[$index]++;
        }

        return collect($dayNames)->map(fn (string $day, int $index) => [
            'day' => $day,
            'count' => $counts[$index],
        ]);
    }
}
