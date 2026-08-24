<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">{{ __('Analytics') }}</h2>
                <p class="text-sm text-gray-500 mt-1">Selected period: {{ $periodLabel }}</p>
            </div>
            @if (Auth::user()->canManageUsers() && $pendingCount > 0)
                <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-brand-orange hover:text-brand-orange-700">
                    {{ $pendingCount }} pending signup{{ $pendingCount === 1 ? '' : 's' }} →
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm sm:rounded-lg p-4 sm:p-5">
                <form method="GET" action="{{ route('admin.analytics.index') }}" class="grid gap-4 lg:grid-cols-[1fr_1fr_auto_auto] lg:items-end">
                    <div>
                        <x-input-label for="from" value="From" />
                        <x-text-input id="from" name="from" type="datetime-local" class="mt-1 block w-full"
                            :value="old('from', $fromValue)" required />
                        <x-input-error :messages="$errors->get('from')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="to" value="To" />
                        <x-text-input id="to" name="to" type="datetime-local" class="mt-1 block w-full"
                            :value="old('to', $toValue)" required />
                        <x-input-error :messages="$errors->get('to')" class="mt-1" />
                    </div>
                    <button type="submit"
                        class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-700">
                        Apply range
                    </button>
                    <button type="submit"
                        formaction="{{ route('admin.analytics.export') }}"
                        class="inline-flex items-center justify-center px-4 py-2 bg-brand-orange text-white text-sm font-medium rounded-md hover:bg-brand-orange-700">
                        Export vehicle usage
                    </button>
                </form>
                <p class="mt-3 text-xs text-gray-500">
                    Choose a from/to range, then export. The file lists each trip: user name, department, trip purpose, vehicle used, destination, and times. Default range is this month, including upcoming trips.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-5 border-l-4 border-brand-red">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Boardroom in period</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['boardroom_period'] }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ $stats['boardroom_today'] }} today · {{ $stats['boardroom_week'] }} this week</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5 border-l-4 border-brand-turquoise">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Vehicles in period</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['vehicle_period'] }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ $stats['vehicle_today'] }} today · {{ $stats['vehicle_week'] }} this week</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5 border-l-4 border-brand-emerald">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Utilization (period)</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['boardroom_utilization'] }}% <span class="text-lg text-gray-400">/</span> {{ $stats['vehicle_utilization'] }}%</p>
                    <p class="mt-1 text-sm text-gray-500">Rooms / active vehicles · weekdays 8–5</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5 border-l-4 border-brand-orange">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Attention</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $pendingCount }}</p>
                    <p class="mt-1 text-sm text-gray-500">
                        Pending approvals · {{ $stats['boardroom_cancelled_period'] + $stats['vehicle_cancelled_period'] }} cancellations in period
                    </p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900">Pending signups</h3>
                        @if (Auth::user()->canManageUsers())
                            <a href="{{ route('admin.users.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Manage</a>
                        @endif
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($pendingApprovals as $user)
                            <div class="px-4 py-3 flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $user->department ?? 'No department' }} · {{ $user->email }}</p>
                                </div>
                                <p class="text-xs text-gray-400 whitespace-nowrap">{{ $user->created_at->diffForHumans() }}</p>
                            </div>
                        @empty
                            <p class="px-4 py-8 text-sm text-gray-500 text-center">No pending signups.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900">Bookings by department</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Confirmed boardroom + vehicle bookings in selected period</p>
                    </div>
                    <div class="p-4 space-y-3">
                        @php $deptMax = max(1, $bookingsByDepartment->max('count') ?? 0); @endphp
                        @forelse ($bookingsByDepartment as $row)
                            <div>
                                <div class="flex items-center justify-between text-sm mb-1">
                                    <span class="text-gray-700">{{ $row['department'] }}</span>
                                    <span class="font-medium text-gray-900">{{ $row['count'] }}</span>
                                </div>
                                <div class="h-2 rounded bg-gray-100 overflow-hidden">
                                    <div class="h-full bg-brand-turquoise" style="width: {{ round(($row['count'] / $deptMax) * 100) }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="py-6 text-sm text-gray-500 text-center">No bookings in this period.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900">Top rooms</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($topRooms as $row)
                            <div class="px-4 py-3 flex justify-between text-sm">
                                <span class="text-gray-700">{{ $row['label'] }}</span>
                                <span class="font-medium text-gray-900">{{ $row['count'] }}</span>
                            </div>
                        @empty
                            <p class="px-4 py-8 text-sm text-gray-500 text-center">No room bookings in this period.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900">Top vehicles</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($topVehicles as $row)
                            <div class="px-4 py-3 flex justify-between text-sm">
                                <span class="text-gray-700">{{ $row['label'] }}</span>
                                <span class="font-medium text-gray-900">{{ $row['count'] }}</span>
                            </div>
                        @empty
                            <p class="px-4 py-8 text-sm text-gray-500 text-center">No vehicle bookings in this period.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900">Peak days</h3>
                        <p class="text-xs text-gray-500 mt-0.5">All bookings in selected period</p>
                    </div>
                    <div class="p-4 flex items-end justify-between gap-2 h-40">
                        @foreach ($peakDays as $row)
                            <div class="flex-1 flex flex-col items-center justify-end gap-1 h-full">
                                <span class="text-xs text-gray-500">{{ $row['count'] }}</span>
                                <div class="w-full rounded-t bg-brand-red/80" style="height: {{ max(4, round(($row['count'] / $peakMax) * 100)) }}%"></div>
                                <span class="text-xs text-gray-600">{{ $row['day'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900">Upcoming (7 days)</h3>
                        <div class="flex gap-3 text-sm">
                            @if (Auth::user()->canBookBoardroom() && Auth::user()->canAccessAdminDashboard())
                                <a href="{{ route('boardroom.bookings.admin') }}" class="text-indigo-600 hover:text-indigo-800">Boardroom</a>
                            @endif
                            @if (Auth::user()->canBookVehicles() && Auth::user()->canAccessAdminDashboard())
                                <a href="{{ route('vehicles.bookings.admin') }}" class="text-indigo-600 hover:text-indigo-800">Vehicles</a>
                            @endif
                        </div>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @php
                            $upcoming = $upcomingBoardroom->map(fn ($b) => [
                                'when' => $b->starts_at,
                                'title' => $b->title,
                                'meta' => ($b->room?->name ?? 'Room').' · '.($b->user?->name ?? 'Unknown'),
                                'type' => 'Boardroom',
                            ])->concat($upcomingVehicles->map(fn ($b) => [
                                'when' => $b->starts_at,
                                'title' => $b->title,
                                'meta' => ($b->vehicle?->displayName() ?? 'Vehicle').' · '.($b->user?->name ?? 'Unknown'),
                                'type' => 'Vehicle',
                            ]))->sortBy('when')->take(8);
                        @endphp
                        @forelse ($upcoming as $item)
                            <div class="px-4 py-3">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-medium text-gray-900">{{ $item['title'] }}</p>
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ $item['type'] === 'Boardroom' ? 'bg-brand-soft-100 text-brand-soft-800' : 'bg-brand-turquoise-100 text-brand-turquoise-800' }}">
                                        {{ $item['type'] }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ $item['when']->format('D d M · H:i') }} · {{ $item['meta'] }}</p>
                            </div>
                        @empty
                            <p class="px-4 py-8 text-sm text-gray-500 text-center">Nothing booked in the next 7 days.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900">Admin actions in period</h3>
                        @if (Auth::user()->canViewAuditLog())
                            <a href="{{ route('admin.audit-logs.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Audit log</a>
                        @endif
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($recentAudit as $log)
                            <div class="px-4 py-3">
                                <p class="text-sm text-gray-900">{{ $log->description }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $log->user?->name ?? 'System' }} · {{ $log->created_at->diffForHumans() }}
                                    <span class="text-gray-400">· {{ $log->action }}</span>
                                </p>
                            </div>
                        @empty
                            <p class="px-4 py-8 text-sm text-gray-500 text-center">No high-impact actions in this period.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <p class="text-xs text-gray-400 text-center">
                {{ $stats['active_users'] }} active users · {{ $stats['room_count'] }} rooms · {{ $stats['active_vehicle_count'] }} active vehicles
            </p>
        </div>
    </div>
</x-app-layout>
