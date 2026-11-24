<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800">{{ __('Manage Users') }}</h2>
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">Add user</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            @if ($pendingUsers->isNotEmpty())
                <div class="bg-amber-50 border border-amber-200 shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-3 border-b border-amber-200">
                        <h3 class="font-semibold text-amber-900">Pending signups ({{ $pendingUsers->count() }})</h3>
                        <p class="text-sm text-amber-800 mt-0.5">Approve to grant access, or reject to keep them blocked.</p>
                    </div>

                    <div class="md:hidden divide-y divide-amber-100">
                        @foreach ($pendingUsers as $user)
                            <div class="p-4 space-y-3">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                    <p class="text-sm text-gray-600 break-all">{{ $user->email }}</p>
                                    <p class="text-sm text-gray-600 mt-1">{{ $user->department ?? '—' }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Signed up {{ $user->created_at->format('d M Y H:i') }}</p>
                                </div>
                                <form method="POST" action="{{ route('admin.users.approve', $user) }}" class="flex flex-wrap items-end gap-3">
                                    @csrf @method('PATCH')
                                    <div>
                                        <x-input-label for="role_mobile_{{ $user->id }}" value="Role" />
                                        <select id="role_mobile_{{ $user->id }}" name="role" class="mt-1 border-gray-300 rounded-md shadow-sm text-sm" required>
                                            <option value="user" selected>Staff</option>
                                            <option value="admin">Admin</option>
                                            <option value="super_user">Super User</option>
                                        </select>
                                    </div>
                                    <label class="flex items-center gap-1.5 text-xs text-gray-600">
                                        <input type="checkbox" name="assignable_privileges[]" value="{{ \App\Support\Privileges::MANAGE_WALKIN_FEEDBACK }}" class="rounded border-gray-300 text-indigo-600">
                                        Service desk
                                    </label>
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                        Approve
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.reject', $user) }}"
                                      onsubmit="return confirm('Reject signup for {{ $user->name }}?')">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-white border border-red-300 rounded-md font-semibold text-xs text-red-700 uppercase tracking-widest hover:bg-red-50">
                                        Reject
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>

                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-amber-100">
                            <thead class="bg-amber-100/60">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-amber-900 uppercase">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-amber-900 uppercase">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-amber-900 uppercase">Department</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-amber-900 uppercase">Signed up</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-amber-900 uppercase">Assign role</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-amber-100 bg-white">
                                @foreach ($pendingUsers as $user)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $user->name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $user->email }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $user->department ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">{{ $user->created_at->format('d M Y H:i') }}</td>
                                        <td class="px-4 py-3" colspan="2">
                                            <div class="flex flex-wrap items-center justify-end gap-3">
                                                <form method="POST" action="{{ route('admin.users.approve', $user) }}" class="flex items-center gap-2">
                                                    @csrf @method('PATCH')
                                                    <select name="role" class="border-gray-300 rounded-md shadow-sm text-sm" required>
                                                        <option value="user" selected>Staff</option>
                                                        <option value="admin">Admin</option>
                                                        <option value="super_user">Super User</option>
                                                    </select>
                                                    <label class="flex items-center gap-1.5 text-xs text-gray-600 mr-2">
                                                        <input type="checkbox" name="assignable_privileges[]" value="{{ \App\Support\Privileges::MANAGE_WALKIN_FEEDBACK }}" class="rounded border-gray-300 text-indigo-600">
                                                        Service desk
                                                    </label>
                                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                                        Approve
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.users.reject', $user) }}"
                                                      onsubmit="return confirm('Reject signup for {{ $user->name }}?')">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-white border border-red-300 rounded-md font-semibold text-xs text-red-700 uppercase tracking-widest hover:bg-red-50">
                                                        Reject
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Approved users</h3>
                </div>

                <div class="md:hidden divide-y divide-gray-100">
                    @forelse ($users as $user)
                        <div class="p-4 space-y-3 {{ $user->id === auth()->id() ? 'bg-indigo-50' : (!$user->isActive() ? 'bg-gray-50 opacity-80' : '') }}">
                            <div>
                                <p class="font-medium text-gray-900">
                                    {{ $user->name }}
                                    @if ($user->id === auth()->id())
                                        <span class="ml-1 text-xs text-indigo-500">(you)</span>
                                    @endif
                                </p>
                                <p class="text-sm text-gray-600 break-all">{{ $user->email }}</p>
                                <p class="text-sm text-gray-600 mt-1">{{ $user->department ?? '—' }}</p>
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium capitalize
                                        {{ $user->isSuperUser() ? 'bg-brand-soft-100 text-brand-soft-800' : ($user->role === 'admin' ? 'bg-brand-turquoise-100 text-brand-turquoise-800' : 'bg-brand-emerald-100 text-brand-emerald-800') }}">
                                        {{ str_replace('_', ' ', $user->role) }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $user->isActive() ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $user->accountStatusLabel() }}
                                    </span>
                                    <span class="text-xs text-gray-500">Joined {{ $user->created_at->format('d M Y') }}</span>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-3 text-sm">
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-800">Edit</a>
                                <button type="button"
                                    onclick="document.getElementById('reset-modal-{{ $user->id }}').classList.remove('hidden')"
                                    class="text-gray-700 hover:text-gray-900">Reset password</button>
                                @if ($user->id !== auth()->id())
                                    @if ($user->isActive())
                                        <form method="POST" action="{{ route('admin.users.deactivate', $user) }}"
                                            onsubmit="return confirm('Deactivate {{ $user->name }}? They will no longer be able to sign in.')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-red-600 hover:text-red-800">Deactivate</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.users.reactivate', $user) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-green-600 hover:text-green-800">Reactivate</button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500">No approved users found.</div>
                    @endforelse
                </div>

                <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($users as $user)
                            <tr class="{{ $user->id === auth()->id() ? 'bg-indigo-50' : (!$user->isActive() ? 'bg-gray-50 opacity-80' : '') }}">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    {{ $user->name }}
                                    @if ($user->id === auth()->id())
                                        <span class="ml-1 text-xs text-indigo-500">(you)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $user->email }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $user->department ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm capitalize">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $user->isSuperUser() ? 'bg-brand-soft-100 text-brand-soft-800' : ($user->role === 'admin' ? 'bg-brand-turquoise-100 text-brand-turquoise-800' : 'bg-brand-emerald-100 text-brand-emerald-800') }}">
                                        {{ str_replace('_', ' ', $user->role) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $user->isActive() ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $user->accountStatusLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $user->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div x-data="{ open: false }" class="relative inline-block text-left">
                                        <button @click="open = !open" class="text-gray-400 hover:text-gray-600 p-1 rounded">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zm0 8a2 2 0 110-4 2 2 0 010 4zm0-4a2 2 0 110-4 2 2 0 010 4z"/>
                                            </svg>
                                        </button>
                                        <div x-show="open" @click.outside="open = false"
                                            class="absolute right-0 mt-1 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-10 text-sm"
                                            style="display:none">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Edit details</a>
                                            <button onclick="document.getElementById('reset-modal-{{ $user->id }}').classList.remove('hidden')"
                                                class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-50">Reset password</button>
                                            @if ($user->id !== auth()->id())
                                                @if ($user->isActive())
                                                    <form method="POST" action="{{ route('admin.users.deactivate', $user) }}"
                                                        onsubmit="return confirm('Deactivate {{ $user->name }}? They will no longer be able to sign in.')">
                                                        @csrf @method('PATCH')
                                                        <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50">Deactivate</button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('admin.users.reactivate', $user) }}">
                                                        @csrf @method('PATCH')
                                                        <button type="submit" class="w-full text-left px-4 py-2 text-green-600 hover:bg-green-50">Reactivate</button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <div id="reset-modal-{{ $user->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
                                <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Reset password — {{ $user->name }}</h3>
                                    <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="space-y-4">
                                        @csrf @method('PATCH')
                                        <div>
                                            <x-input-label for="password_{{ $user->id }}" value="New password" />
                                            <x-text-input id="password_{{ $user->id }}" name="password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                                        </div>
                                        <div>
                                            <x-input-label for="password_confirmation_{{ $user->id }}" value="Confirm password" />
                                            <x-text-input id="password_confirmation_{{ $user->id }}" name="password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                                        </div>
                                        <div class="flex gap-3 pt-1">
                                            <x-primary-button>Set password</x-primary-button>
                                            <button type="button"
                                                onclick="document.getElementById('reset-modal-{{ $user->id }}').classList.add('hidden')"
                                                class="text-sm text-gray-600 hover:text-gray-900">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">No approved users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
