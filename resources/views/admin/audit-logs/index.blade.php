<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ __('Audit Log') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <form method="GET" class="bg-white shadow-sm sm:rounded-lg p-4 flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <x-input-label for="q" value="Search" />
                    <x-text-input id="q" name="q" class="mt-1 block w-full" :value="request('q')" placeholder="User, action, description..." />
                </div>
                <div class="min-w-[180px]">
                    <x-input-label for="action" value="Action" />
                    <select id="action" name="action" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">All actions</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <x-primary-button>Filter</x-primary-button>
                    <a href="{{ route('admin.audit-logs.index') }}" class="text-sm text-gray-600 hover:text-gray-900 self-center">Clear</a>
                </div>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="md:hidden divide-y divide-gray-100">
                    @forelse ($logs as $log)
                        <div class="p-4 space-y-2">
                            <p class="text-xs text-gray-500">{{ $log->created_at?->format('d M Y H:i') }}</p>
                            @if ($log->user)
                                <p class="font-medium text-gray-900">{{ $log->user->name }}</p>
                                <p class="text-xs text-gray-500 break-all">{{ $log->user->email }}</p>
                            @else
                                <p class="text-gray-400">System / guest</p>
                            @endif
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                {{ $log->action }}
                            </span>
                            <p class="text-sm text-gray-700">{{ $log->description }}</p>
                            @if (! empty($log->properties))
                                <details class="text-xs text-gray-500">
                                    <summary class="cursor-pointer">Details</summary>
                                    <pre class="mt-1 whitespace-pre-wrap break-all">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </details>
                            @endif
                            @if ($log->ip_address)
                                <p class="text-xs text-gray-400">IP: {{ $log->ip_address }}</p>
                            @endif
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500">No audit events yet.</div>
                    @endforelse
                </div>

                <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">When</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                    {{ $log->created_at?->format('d M Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($log->user)
                                        <div class="font-medium text-gray-900">{{ $log->user->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $log->user->email }}</div>
                                    @else
                                        <span class="text-gray-400">System / guest</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $log->description }}
                                    @if (! empty($log->properties))
                                        <details class="mt-1 text-xs text-gray-500">
                                            <summary class="cursor-pointer">Details</summary>
                                            <pre class="mt-1 whitespace-pre-wrap break-all">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </details>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">{{ $log->ip_address }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">No audit events yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>

            <div>
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
