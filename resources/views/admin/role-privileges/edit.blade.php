<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ __('Role Privileges') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-900">
                Super User always has every privilege and cannot be restricted.
                Use this page to decide what <strong>Staff</strong> and <strong>Admin</strong> can do.
            </div>

            <form method="POST" action="{{ route('admin.role-privileges.update') }}" class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                @csrf
                @method('PUT')

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Privilege</th>
                                @foreach ($roles as $role)
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                        {{ $labels[$role] ?? $role }}
                                    </th>
                                @endforeach
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Super User</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($privileges as $key => $label)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-800">
                                        <div class="font-medium">{{ $label }}</div>
                                        <div class="text-xs text-gray-500 font-mono">{{ $key }}</div>
                                    </td>
                                    @foreach ($roles as $role)
                                        <td class="px-4 py-3 text-center">
                                            <input
                                                type="checkbox"
                                                name="privileges[{{ $role }}][]"
                                                value="{{ $key }}"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                @checked(in_array($key, $assigned[$role] ?? [], true))
                                            >
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox" checked disabled class="rounded border-gray-300 text-indigo-600 opacity-60">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-4 border-t flex gap-3">
                    <x-primary-button>Save privileges</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
