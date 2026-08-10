<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Welcome to ReserVio') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @php
                $user = Auth::user();
                $canBookBoardroom = $user->canBookBoardroom();
                $canBookVehicles = $user->canBookVehicles();
                $moduleCount = (int) $canBookBoardroom + (int) $canBookVehicles;
            @endphp

            @if ($moduleCount > 0)
                <p class="text-gray-600 mb-6 sm:mb-8 text-center">Choose what you would like to book</p>

                <div @class([
                    'grid gap-4 sm:gap-6',
                    'md:grid-cols-2' => $moduleCount > 1,
                    'max-w-md mx-auto' => $moduleCount === 1,
                ])>
                    @if ($canBookBoardroom)
                        <x-booking-module-card
                            :href="route('boardroom.dashboard')"
                            title="Book Boardroom"
                            description="Reserve meeting rooms and boardrooms for your team."
                            variant="red"
                        >
                            <x-slot:icon>
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </x-slot:icon>
                        </x-booking-module-card>
                    @endif

                    @if ($canBookVehicles)
                        <x-booking-module-card
                            :href="route('vehicles.dashboard')"
                            title="Pool Vehicle"
                            description="Book pool vehicles for official trips and errands."
                            variant="turquoise"
                        >
                            <x-slot:icon>
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 4h8m-4 4H8M5 7h.01M5 11h.01M5 15h.01M17 17H7l-1 4h12l-1-4z"/>
                                </svg>
                            </x-slot:icon>
                        </x-booking-module-card>
                    @endif
                </div>
            @else
                <div class="rounded-lg border border-gray-200 bg-white p-6 text-center text-sm text-gray-600">
                    You do not have access to any booking modules yet. Contact a super user if you need Boardroom or Pool Vehicle access.
                </div>
            @endif

            @php
                $showAdminTools = $user->canAccessAdminDashboard()
                    || $user->canViewAnalytics()
                    || $user->canManageVehicles()
                    || $user->canManageUsers()
                    || $user->canManageRolePrivileges()
                    || $user->canViewAuditLog();
            @endphp

            @if ($showAdminTools)
                <div class="mt-8 sm:mt-10 rounded-lg border border-brand-orange-200 bg-brand-orange-50 p-4 sm:p-6">
                    <h4 class="font-semibold text-brand-orange-900">Admin tools</h4>
                    <p class="text-sm text-brand-orange-800 mt-1">Shortcuts based on your role privileges.</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        @if ($user->canViewAnalytics())
                            <a href="{{ route('admin.analytics.index') }}"
                               class="inline-flex items-center px-4 py-2 bg-brand-orange text-white text-sm font-medium rounded-md hover:bg-brand-orange-700">
                                Analytics
                            </a>
                        @endif
                        @if ($user->canManageVehicles())
                            <a href="{{ route('admin.vehicles.index') }}"
                               class="inline-flex items-center px-4 py-2 bg-brand-orange text-white text-sm font-medium rounded-md hover:bg-brand-orange-700">
                                Manage vehicles
                            </a>
                        @endif
                        @if ($user->canManageUsers())
                            <a href="{{ route('admin.users.index') }}"
                               class="inline-flex items-center px-4 py-2 bg-brand-orange text-white text-sm font-medium rounded-md hover:bg-brand-orange-700">
                                Manage users
                            </a>
                        @endif
                        @if ($user->canManageRolePrivileges())
                            <a href="{{ route('admin.role-privileges.edit') }}"
                               class="inline-flex items-center px-4 py-2 bg-brand-orange text-white text-sm font-medium rounded-md hover:bg-brand-orange-700">
                                Role privileges
                            </a>
                        @endif
                        @if ($user->canViewAuditLog())
                            <a href="{{ route('admin.audit-logs.index') }}"
                               class="inline-flex items-center px-4 py-2 border border-brand-turquoise-300 text-brand-turquoise-900 text-sm font-medium rounded-md hover:bg-brand-turquoise-50">
                                Audit log
                            </a>
                        @endif
                        @if ($user->canAccessAdminDashboard() && $user->canBookBoardroom())
                            <a href="{{ route('boardroom.bookings.admin') }}"
                               class="inline-flex items-center px-4 py-2 border border-brand-emerald-300 text-brand-emerald-900 text-sm font-medium rounded-md hover:bg-brand-emerald-50">
                                Boardroom bookings
                            </a>
                        @endif
                        @if ($user->canAccessAdminDashboard() && $user->canBookVehicles())
                            <a href="{{ route('vehicles.bookings.admin') }}"
                               class="inline-flex items-center px-4 py-2 border border-brand-turquoise-300 text-brand-turquoise-900 text-sm font-medium rounded-md hover:bg-brand-turquoise-50">
                                Vehicle bookings
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
