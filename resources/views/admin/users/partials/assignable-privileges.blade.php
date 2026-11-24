@php
    use App\Support\Privileges;
    $assignable = Privileges::assignableToUsers();
    $selected = old('assignable_privileges', isset($user) && $user->exists
        ? $user->assignablePrivilegeKeys()->all()
        : []);
@endphp

@if ($assignable !== [])
    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-2">
        <p class="text-sm font-medium text-gray-900">Additional access (on top of base role)</p>
        <p class="text-xs text-gray-500">Stack extra duties without changing the user&apos;s primary role — e.g. staff + service desk.</p>
        @foreach ($assignable as $key => $label)
            <label class="flex items-start gap-2 text-sm text-gray-700">
                <input type="checkbox"
                       name="assignable_privileges[]"
                       value="{{ $key }}"
                       @checked(in_array($key, $selected, true))
                       class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>
@endif