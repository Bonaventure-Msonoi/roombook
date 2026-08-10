<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            {{ $user->exists ? __('Edit User') : __('Add User') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <form method="POST"
                    action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}"
                    class="space-y-5">
                    @csrf
                    @if ($user->exists) @method('PUT') @endif

                    <div>
                        <x-input-label for="name" value="Full name" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full"
                            :value="old('name', $user->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Email address" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                            :value="old('email', $user->email)" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="role" value="Role" />
                        <select id="role" name="role"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required>
                            @foreach ($roles as $value => $label)
                                <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-1" />
                    </div>

                    @if (! $user->exists)
                        <div>
                            <x-input-label for="password" value="Password" />
                            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                                required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="password_confirmation" value="Confirm password" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                                class="mt-1 block w-full" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                        </div>
                    @endif

                    <div class="flex gap-4 pt-2">
                        <x-primary-button>{{ $user->exists ? 'Update user' : 'Create user' }}</x-primary-button>
                        <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-900 self-center">Cancel</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
