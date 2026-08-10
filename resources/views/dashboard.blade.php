<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Room Calendar') }}
            </h2>
            <p class="text-sm text-gray-500">Book today or up to {{ $maxBookingMonths }} months ahead — click, drag, or pick a date</p>
        </div>
    </x-slot>

    <div class="py-8" x-data="roomCalendar()">
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
                    <div class="flex flex-wrap gap-4">
                        @foreach ($rooms as $room)
                            <div class="flex items-center gap-2 text-sm text-gray-700">
                                <span class="inline-block w-3 h-3 rounded-full" style="background-color: {{ $room->color }}"></span>
                                <span>{{ $room->name }}</span>
                                <span class="text-gray-400">({{ $room->capacity }} seats)</span>
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

                        <div class="inline-flex rounded-lg border border-gray-200 p-1 bg-gray-50">
                            <button type="button"
                                    @click="setView('week')"
                                    :class="activeView === 'week' ? 'bg-white shadow text-indigo-700' : 'text-gray-600 hover:text-gray-900'"
                                    class="px-3 py-1.5 text-sm font-medium rounded-md transition">
                                Week overview
                            </button>
                            <button type="button"
                                    @click="setView('rooms')"
                                    :class="activeView === 'rooms' ? 'bg-white shadow text-indigo-700' : 'text-gray-600 hover:text-gray-900'"
                                    class="px-3 py-1.5 text-sm font-medium rounded-md transition">
                                Book by room
                            </button>
                        </div>
                    </div>
                </div>

                <div x-show="activeView === 'week'" x-cloak>
                    <div id="week-calendar"></div>
                </div>

                <div x-show="activeView === 'rooms'" x-cloak>
                    <div class="flex items-center justify-between mb-4">
                        <button type="button" @click="shiftRoomDay(-1)" class="px-3 py-1 text-sm border rounded-md text-gray-600 hover:bg-gray-50">Previous day</button>
                        <p class="text-sm font-medium text-gray-800" x-text="roomDayLabel"></p>
                        <button type="button" @click="shiftRoomDay(1)" class="px-3 py-1 text-sm border rounded-md text-gray-600 hover:bg-gray-50">Next day</button>
                    </div>
                    <div class="grid gap-4 lg:grid-cols-3">
                        @foreach ($rooms as $room)
                            <div>
                                <div class="mb-2 flex items-center gap-2">
                                    <span class="inline-block w-3 h-3 rounded-full" style="background-color: {{ $room->color }}"></span>
                                    <h3 class="text-sm font-semibold text-gray-900">{{ $room->name }}</h3>
                                </div>
                                <div id="room-calendar-{{ $room->id }}" class="room-day-calendar" data-room-id="{{ $room->id }}" data-room-name="{{ $room->name }}"></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <x-modal name="book-room" focusable>
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900">Book a room</h2>
                <p class="mt-1 text-sm text-gray-500" x-text="modalTimeLabel"></p>

                <form method="POST" action="{{ route('bookings.store') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="modal_title" :value="__('Meeting title / purpose')" />
                        <x-text-input id="modal_title" name="title" type="text" class="mt-1 block w-full"
                                      value="{{ old('title') }}" required />
                    </div>

                    <div>
                        <x-input-label for="modal_room_id" :value="__('Room')" />
                        <select id="modal_room_id" name="room_id" x-model="selectedRoomId"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                            <template x-for="room in roomAvailability" :key="room.id">
                                <option :value="room.id" :disabled="!room.available"
                                        x-text="room.available ? room.name + ' (' + room.capacity + ' seats)' : room.name + ' — unavailable'">
                                </option>
                            </template>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Unavailable rooms are greyed out for this time slot.</p>
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
                                @click="$dispatch('close-modal', 'book-room')"
                                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">
                            Cancel
                        </button>
                        <x-primary-button>Book room</x-primary-button>
                    </div>
                </form>
            </div>
        </x-modal>
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
        <style>
            [x-cloak] { display: none !important; }
            #week-calendar, .room-day-calendar { min-height: 650px; }
            .room-day-calendar { min-height: 520px; }
            .fc-event { cursor: pointer; }
            .fc .fc-highlight { background: rgba(99, 102, 241, 0.2); }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
        <script>
            const ROOMS = @json($roomsJson);

            function toLocalInput(date) {
                const pad = (n) => String(n).padStart(2, '0');
                return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()) +
                    'T' + pad(date.getHours()) + ':' + pad(date.getMinutes());
            }

            function formatLabel(start, end) {
                const opts = { weekday: 'short', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' };
                return start.toLocaleString(undefined, opts) + ' – ' + end.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' });
            }

            function roomCalendar() {
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
                    selectedRoomId: '',
                    roomAvailability: ROOMS.map(r => ({ ...r, available: true })),
                    modalTimeLabel: '',

                    get roomDayLabel() {
                        return this.roomDay.toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
                    },

                    init() {
                        @if (old('starts_at') && old('ends_at'))
                            this.startsAt = @json(old('starts_at'));
                            this.endsAt = @json(old('ends_at'));
                            this.selectedRoomId = @json((string) old('room_id', ''));
                            this.modalTimeLabel = 'Adjust your booking details';
                            this.$nextTick(() => {
                                this.refreshAvailability().then(() => this.$dispatch('open-modal', 'book-room'));
                            });
                        @endif

                        this.$nextTick(() => {
                            this.initWeekCalendar();
                            this.initRoomCalendars();
                        });
                    },

                    setView(view) {
                        this.activeView = view;
                        this.$nextTick(() => {
                            if (view === 'week' && this.weekCalendar) {
                                this.weekCalendar.updateSize();
                            }
                            if (view === 'rooms') {
                                this.roomCalendars.forEach(c => c.updateSize());
                            }
                        });
                    },

                    shiftRoomDay(days) {
                        this.roomDay.setDate(this.roomDay.getDate() + days);
                        this.roomDay = new Date(this.roomDay);
                        this.jumpDate = this.roomDay.toISOString().slice(0, 10);
                        this.roomCalendars.forEach(c => c.gotoDate(this.roomDay));
                    },

                    goToDate() {
                        if (!this.jumpDate) return;

                        const date = new Date(this.jumpDate + 'T12:00:00');
                        this.roomDay = date;

                        if (this.weekCalendar) {
                            this.weekCalendar.gotoDate(date);
                        }

                        this.roomCalendars.forEach(c => c.gotoDate(date));
                    },

                    dateFromClick(info) {
                        if (info.dateStr) {
                            const [year, month, day] = info.dateStr.split('-').map(Number);
                            return new Date(year, month - 1, day);
                        }

                        return new Date(info.date);
                    },

                    async openBookingModal({ start, end, roomId = null }) {
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

                        if (roomId) {
                            this.selectedRoomId = String(roomId);
                        } else {
                            const firstAvailable = this.roomAvailability.find(r => r.available);
                            this.selectedRoomId = firstAvailable ? String(firstAvailable.id) : String(ROOMS[0].id);
                        }

                        this.$dispatch('open-modal', 'book-room');
                    },

                    async refreshAvailability() {
                        if (!this.startsAt || !this.endsAt) return;

                        const url = new URL('{{ route('bookings.availability') }}', window.location.origin);
                        url.searchParams.set('starts_at', this.startsAt);
                        url.searchParams.set('ends_at', this.endsAt);

                        const response = await fetch(url);
                        this.roomAvailability = await response.json();

                        const current = this.roomAvailability.find(r => String(r.id) === this.selectedRoomId);
                        if (!current || !current.available) {
                            const firstAvailable = this.roomAvailability.find(r => r.available);
                            if (firstAvailable) {
                                this.selectedRoomId = String(firstAvailable.id);
                            }
                        }
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
                            initialView: 'timeGridWeek',
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,timeGridDay'
                            },
                            events: '{{ route('calendar.events') }}',
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

                    initRoomCalendars() {
                        document.querySelectorAll('.room-day-calendar').forEach((el) => {
                            const roomId = parseInt(el.dataset.roomId, 10);
                            const calendar = new FullCalendar.Calendar(el, this.sharedCalendarOptions({
                                initialView: 'timeGridDay',
                                initialDate: this.roomDay,
                                headerToolbar: false,
                                events: '{{ route('calendar.events') }}?room_id=' + roomId,
                                dateClick: (info) => {
                                    const start = info.date;
                                    const end = new Date(start.getTime() + 30 * 60 * 1000);
                                    this.openBookingModal({ start, end, roomId });
                                },
                                select: (info) => {
                                    this.openBookingModal({ start: info.start, end: info.end, roomId });
                                },
                                eventClick: (info) => this.handleEventClick(info),
                            }));
                            calendar.render();
                            this.roomCalendars.push(calendar);
                        });
                    },

                    handleEventClick(info) {
                        const props = info.event.extendedProps;
                        const message = props.room + '\n' + info.event.title + '\nBooked by: ' + props.bookedBy;

                        if (props.canCancel && confirm(message + '\n\nCancel this booking?')) {
                            const token = document.querySelector('meta[name="csrf-token"]').content;
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '/bookings/' + info.event.id;
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
