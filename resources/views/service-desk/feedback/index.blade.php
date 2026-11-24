<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4 min-w-0">
                <div class="shrink-0 rounded-lg bg-white px-3 py-2 border border-gray-200 shadow-sm">
                    <x-prudential-logo class="max-h-8 w-auto max-w-[180px]" />
                </div>
                <div class="min-w-0">
                    <h2 class="font-semibold text-lg text-gray-800 truncate">{{ __('Customer feedback') }}</h2>
                    <p class="text-sm text-gray-500">View walk-ins and export to Excel</p>
                </div>
            </div>
            <a href="{{ route('walk-in.feedback.create') }}" target="_blank" rel="noopener"
               class="inline-flex shrink-0 items-center text-sm font-medium text-gray-700 hover:text-gray-900 border border-gray-300 rounded-md px-3 py-2 bg-white">
                Open kiosk ↗
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <form method="GET" action="{{ route('service-desk.feedback.index') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-4 flex flex-wrap items-end gap-4">
                <div>
                    <x-input-label for="from" value="From" />
                    <x-text-input id="from" name="from" type="datetime-local" class="mt-1" :value="$from" />
                </div>
                <div>
                    <x-input-label for="to" value="To" />
                    <x-text-input id="to" name="to" type="datetime-local" class="mt-1" :value="$to" />
                </div>
                <x-primary-button type="submit">Apply</x-primary-button>
                <a href="{{ route('service-desk.feedback.export', request()->only(['from', 'to'])) }}"
                   class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-md hover:bg-emerald-700">
                    Export Excel
                </a>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Walk-in</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recommend</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satisfaction</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Improvements</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($feedback as $row)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $row->visited_at->format('d M Y H:i') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        <div>{{ $row->client_name ?: '—' }}</div>
                                        @if ($row->client_email)
                                            <div class="text-xs text-gray-500">{{ $row->client_email }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row->recommend_score }}/10</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $row->satisfactionLabel() }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 max-w-xs">{{ Str::limit($row->rating_reason, 120) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 max-w-xs">{{ $row->improvement_suggestions ? Str::limit($row->improvement_suggestions, 120) : '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No feedback in this date range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($feedback->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100">{{ $feedback->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>