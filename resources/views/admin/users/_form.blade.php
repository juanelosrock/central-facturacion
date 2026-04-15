@if ($errors->any())
    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-4">
    <label class="block">Nombre</label>
    <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" class="w-full border rounded p-2">
</div>

<div class="mb-4">
    <label class="block">Email</label>
    <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="w-full border rounded p-2">
</div>

<div class="mb-4">
    <label class="block">Contraseña {{ isset($user) ? '(dejar vacío para no cambiar)' : '' }}</label>
    <input type="password" name="password" class="w-full border rounded p-2">
</div>

<div class="mb-4">
    <label class="block">Confirmar contraseña</label>
    <input type="password" name="password_confirmation" class="w-full border rounded p-2">
</div>

<div class="mb-4">
    <label class="block">Roles</label>
    @foreach ($roles as $role)
        <label class="inline-flex items-center mr-3">
            <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                @if (in_array($role->name, $userRoles ?? [])) checked @endif>
            <span class="ml-1">{{ $role->name }}</span>
        </label>
    @endforeach
</div>