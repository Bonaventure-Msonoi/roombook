<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pool Vehicle Calendar') }}
            </h2>
            <p class="text-sm text-gray-500">Book today or up to {{ $maxBookingMonths }} months ahead — click, drag, or pick a date</p>
        </div>
    </x-slot>

    <div class="py-8" x-data="vehicleCalendar()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-x-auto shadow-sm sm:rounded-lg p-4 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
                    <div class="flex flex-wrap gap-3 sm:gap-4">
                        @foreach ($vehicles as $vehicle)
                            <div class="flex items-center gap-2 text-sm text-gray-700">
                                <span class="inline-block w-3 h-3 rounded-full" style="background-color: {{ $vehicle->color }}"></span>
                                <span>{{ $vehicle->displayName() }}</span>
                                <span class="text-gray-400">({{ $vehicle->category }})</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2">
                            <label for="jump-date" class="text-sm text-gray-600">Go to date</label>
                            <input id="jump-date" type="date"
                                   x-model="jumpDate"
                                   @change="goToDate()"
                                   :min="todayDate"
                                   :max="maxBookingDate"
                                   class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                        </div>

                        <div class="inline-flex w-full sm:w-auto rounded-lg border border-gray-200 p-1 bg-gray-50">
                            <button type="button"
                                    @click="setView('week')"
                                    :class="activeView === 'week' ? 'bg-white shadow text-indigo-700' : 'text-gray-600 hover:text-gray-900'"
                                    class="flex-1 sm:flex-none px-3 py-1.5 text-sm font-medium rounded-md transition">
                                Week overview
                            </button>
                            <button type="button"
                                    @click="setView('fleet')"
                                    :class="activeView === 'fleet' ? 'bg-white shadow text-indigo-700' : 'text-gray-600 hover:text-gray-900'"
                                    class="flex-1 sm:flex-none px-3 py-1.5 text-sm font-medium rounded-md transition">
                                Book by vehicle
                            </button>
                        </div>
                    </div>
                </div>

                <div x-show="activeView === 'week'" x-cloak>
                    <div class="calendar-scroll -mx-4 px-4 sm:mx-0 sm:px-0">
                        <div id="week-calendar" class="min-w-[320px]"></div>
                    </div>
                </div>

                <div x-show="activeView === 'fleet'" x-cloak>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-4">
                        <button type="button" @click="shiftVehicleDay(-1)" class="px-3 py-1.5 text-sm border rounded-md text-gray-600 hover:bg-gray-50">Previous day</button>
                        <p class="text-sm font-medium text-gray-800 text-center" x-text="vehicleDayLabel"></p>
                        <button type="button" @click="shiftVehicleDay(1)" class="px-3 py-1.5 text-sm border rounded-md text-gray-600 hover:bg-gray-50">Next day</button>
                    </div>
                    <div class="grid gap-4 lg:grid-cols-3">
                        @foreach ($vehicles as $vehicle)
                            <div>
                                <div class="mb-2 flex items-center gap-2">
                                    <span class="inline-block w-3 h-3 rounded-full" style="background-color: {{ $vehicle->color }}"></span>
                                    <h3 class="text-sm font-semibold text-gray-900">{{ $vehicle->displayName() }}</h3>
                                </div>
                                <div id="vehicle-calendar-{{ $vehicle->id }}" class="vehicle-day-calendar" data-vehicle-id="{{ $vehicle->id }}"></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <x-modal name="book-vehicle" focusable>
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900">Book a vehicle</h2>
                <p class="mt-1 text-sm text-gray-500" x-text="modalTimeLabel"></p>

                <form method="POST" action="{{ route('vehicles.bookings.store') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="modal_title" :value="__('Meeting title / purpose')" />
                        <x-text-input id="modal_title" name="title" type="text" class="mt-1 block w-full"
                                      value="{{ old('title') }}" required />
                    </div>

                    <div>
                        <x-input-label for="modal_vehicle_id" :value="__('Vehicle')" />
                        <select id="modal_vehicle_id" name="vehicle_id" x-model="selectedVehicleId"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                            <template x-for="vehicle in vehicleAvailability" :key="vehicle.id">
                                <option :value="vehicle.id" :disabled="!vehicle.available"
                                        x-text="vehicle.available ? vehicle.name + ' (' + vehicle.category + ')' : vehicle.name + ' — unavailable'">
                                </option>
                            </template>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Unavailable vehicles are greyed out for this time slot.</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="modal_starts_at" :value="__('Start time')" />
                            <input id="modal_starts_at" name="starts_at" type="datetime-local"
                                   x-model="startsAt"
                                   @change="refreshAvailability()"
                                   :min="minDateTime"
                                   :max="maxDateTime"
                                   class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                   required />
                        </div>
                        <div>
                            <x-input-label for="modal_ends_at" :value="__('End time')" />
                            <input id="modal_ends_at" name="ends_at" type="datetime-local"
                                   x-model="endsAt"
                                   @change="refreshAvailability()"
                                   :min="minDateTime"
                                   :max="maxDateTime"
                                   class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                   required />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button"
                                @click="$dispatch('close-modal', 'book-vehicle')"
                                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">
                            Cancel
                        </button>
                        <x-primary-button>Book vehicle</x-primary-button>
                    </div>
                </form>
            </div>
        </x-modal>
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
        <style>
            [x-cloak] { display: none !important; }
            .calendar-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            #week-calendar, .vehicle-day-calendar { min-height: 420px; }
            .vehicle-day-calendar { min-height: 380px; }
            @media (min-width: 640px) {
                #week-calendar, .vehicle-day-calendar { min-height: 650px; }
                .vehicle-day-calendar { min-height: 520px; }
            }
            .fc-event { cursor: pointer; }
            .fc .fc-highlight { background: rgba(99, 102, 241, 0.2); }
            @media (max-width: 639px) {
                .fc .fc-toolbar { flex-wrap: wrap; gap: 0.5rem; }
                .fc .fc-toolbar-title { font-size: 1rem; }
                .fc .fc-button { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
        <script>
            const VEHICLES = @json($vehiclesJson);

            function toLocalInput(date) {
                const pad = (n) => String(n).padStart(2, '0');
                return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()) +
                    'T' + pad(date.getHours()) + ':' + pad(date.getMinutes());
            }

            function formatLabel(start, end) {
                const opts = { weekday: 'short', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' };
                return start.toLocaleString(undefined, opts) + ' – ' + end.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' });
            }

            function vehicleCalendar() {
                return {
                    activeView: 'week',
                    weekCalendar: null,
                    roomCalendars: [],
                    roomDay: new Date(),
                    jumpDate: new Date().toISOString().slice(0, 10),
                    todayDate: new Date().toISOString().slice(0, 10),
                    maxBookingDate: @json($maxBookingDate),
                    maxDateTime: @json($maxBookingDate) + 'T20:00',
                    minDateTime: toLocalInput(new Date()),
                    startsAt: '',
                    endsAt: '',
                    selectedVehicleId: '',
                    vehicleAvailability: VEHICLES.map(v => ({ ...v, available: true })),
                    modalTimeLabel: '',

                    get vehicleDayLabel() {
                        return this.vehicleDay.toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
                    },

                    init() {
                        @if (old('starts_at') && old('ends_at'))
                            this.startsAt = @json(old('starts_at'));
                            this.endsAt = @json(old('ends_at'));
                            this.selectedVehicleId = @json((string) old('vehicle_id', ''));
                            this.modalTimeLabel = 'Adjust your booking details';
                            this.$nextTick(() => {
                                this.refreshAvailability().then(() => this.$dispatch('open-modal', 'book-vehicle'));
                            });
                        @endif

                        this.$nextTick(() => {
                            this.initWeekCalendar();
                            this.initVehicleCalendars();
                        });
                    },

                    setView(view) {
                        this.activeView = view;
                        this.$nextTick(() => {
                            if (view === 'week' && this.weekCalendar) {
                                this.weekCalendar.updateSize();
                            }
                            if (view === 'fleet') {
                                this.vehicleCalendars.forEach(c => c.updateSize());
                            }
                        });
                    },

                    shiftVehicleDay(days) {
                        this.vehicleDay.setDate(this.vehicleDay.getDate() + days);
                        this.vehicleDay = new Date(this.vehicleDay);
                        this.jumpDate = this.vehicleDay.toISOString().slice(0, 10);
                        this.vehicleCalendars.forEach(c => c.gotoDate(this.vehicleDay));
                    },

                    goToDate() {
                        if (!this.jumpDate) return;

                        const date = new Date(this.jumpDate + 'T12:00:00');
                        this.vehicleDay = date;

                        if (this.weekCalendar) {
                            this.weekCalendar.gotoDate(date);
                        }

                        this.vehicleCalendars.forEach(c => c.gotoDate(date));
                    },

                    dateFromClick(info) {
                        if (info.dateStr) {
                            const [year, month, day] = info.dateStr.split('-').map(Number);
                            return new Date(year, month - 1, day);
                        }

                        return new Date(info.date);
                    },

                    async openBookingModal({ start, end, vehicleId = null }) {
                        const now = new Date();
                        const max = new Date(this.maxBookingDate + 'T23:59:59');

                        if (start < now) {
                            alert('You cannot book a time in the past.');
                            return;
                        }

                        if (start > max) {
                            alert('Bookings can only be made up to 6 months ahead.');
                            return;
                        }

                        this.startsAt = toLocalInput(start);
                        this.endsAt = toLocalInput(end);
                        this.modalTimeLabel = formatLabel(start, end);
                        await this.refreshAvailability();

                        if (vehicleId) {
                            this.selectedVehicleId = String(vehicleId);
                        } else {
                            const firstAvailable = this.vehicleAvailability.find(v => v.available);
                            this.selectedVehicleId = firstAvailable ? String(firstAvailable.id) : String(VEHICLES[0]?.id || '');
                        }

                        this.$dispatch('open-modal', 'book-vehicle');
                    },

                    async refreshAvailability() {
                        if (!this.startsAt || !this.endsAt) return;

                        const url = new URL('{{ route('vehicles.bookings.availability') }}', window.location.origin);
                        url.searchParams.set('starts_at', this.startsAt);
                        url.searchParams.set('ends_at', this.endsAt);

                        const response = await fetch(url);
                        this.vehicleAvailability = await response.json();

                        const current = this.vehicleAvailability.find(v => String(v.id) === this.selectedVehicleId);
                        if (!current || !current.available) {
                            const firstAvailable = this.vehicleAvailability.find(v => v.available);
                            if (firstAvailable) {
                                this.selectedVehicleId = String(firstAvailable.id);
                            }
                        }
                    },

                    isMobile() {
                        return window.innerWidth < 768;
                    },

                    weekCalendarToolbar() {
                        if (this.isMobile()) {
                            return {
                                left: 'prev,next',
                                center: 'title',
                                right: 'timeGridDay,dayGridMonth',
                            };
                        }

                        return {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay',
                        };
                    },

                    sharedCalendarOptions(extra = {}) {
                        return {
                            slotMinTime: '07:00:00',
                            slotMaxTime: '20:00:00',
                            slotDuration: '00:30:00',
                            snapDuration: '00:15:00',
                            allDaySlot: false,
                            nowIndicator: true,
                            height: 'auto',
                            selectable: true,
                            selectMirror: true,
                            unselectAuto: true,
                            selectOverlap: false,
                            selectConstraint: { startTime: '07:00', endTime: '20:00' },
                            selectAllow: (info) => {
                                const now = new Date();
                                const max = new Date(this.maxBookingDate + 'T23:59:59');
                                return info.start >= now && info.start <= max;
                            },
                            ...extra,
                        };
                    },

                    initWeekCalendar() {
                        const el = document.getElementById('week-calendar');
                        this.weekCalendar = new FullCalendar.Calendar(el, this.sharedCalendarOptions({
                            initialView: this.isMobile() ? 'timeGridDay' : 'timeGridWeek',
                            headerToolbar: this.weekCalendarToolbar(),
                            events: '{{ route('vehicles.calendar.events') }}',
                            dateClick: (info) => {
                                if (info.view.type === 'dayGridMonth') {
                                    const day = this.dateFromClick(info);
                                    const start = new Date(day.getFullYear(), day.getMonth(), day.getDate(), 9, 0, 0);
                                    const end = new Date(day.getFullYear(), day.getMonth(), day.getDate(), 9, 30, 0);
                                    this.openBookingModal({ start, end });
                                    return;
                                }
                                const start = info.date;
                                const end = new Date(start.getTime() + 30 * 60 * 1000);
                                this.openBookingModal({ start, end });
                            },
                            select: (info) => {
                                this.openBookingModal({ start: info.start, end: info.end });
                            },
                            eventClick: (info) => this.handleEventClick(info),
                        }));
                        this.weekCalendar.render();
                    },

                    initVehicleCalendars() {
                        document.querySelectorAll('.vehicle-day-calendar').forEach((el) => {
                            const vehicleId = parseInt(el.dataset.vehicleId, 10);
                            const calendar = new FullCalendar.Calendar(el, this.sharedCalendarOptions({
                                initialView: 'timeGridDay',
                                initialDate: this.vehicleDay,
                                headerToolbar: false,
                                events: '{{ route('vehicles.calendar.events') }}?vehicle_id=' + vehicleId,
                                dateClick: (info) => {
                                    const start = info.date;
                                    const end = new Date(start.getTime() + 30 * 60 * 1000);
                                    this.openBookingModal({ start, end, vehicleId });
                                },
                                select: (info) => {
                                    this.openBookingModal({ start: info.start, end: info.end, vehicleId });
                                },
                                eventClick: (info) => this.handleEventClick(info),
                            }));
                            calendar.render();
                            this.vehicleCalendars.push(calendar);
                        });
                    },

                    handleEventClick(info) {
                        const props = info.event.extendedProps;
                        const message = props.vehicle + '\n' + info.event.title + '\nBooked by: ' + props.bookedBy;

                        if (props.canCancel && confirm(message + '\n\nCancel this booking?')) {
                            const token = document.querySelector('meta[name="csrf-token"]').content;
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '/vehicles/bookings/' + info.event.id;
                            form.innerHTML =
                                '<input type="hidden" name="_token" value="' + token + '">' +
                                '<input type="hidden" name="_method" value="DELETE">';
                            document.body.appendChild(form);
                            form.submit();
                        } else {
                            alert(message);
                        }
                    },
                };
            }
        </script>
    @endpush
</x-app-layout>
