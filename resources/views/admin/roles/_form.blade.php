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
    <input type="text" name="name" value="{{ old('name', $role->name ?? '') }}" class="w-full border rounded p-2">
</div>

<div class="mb-4">
    <label class="block">Permisos</label>
    <div class="grid grid-cols-2 gap-2">
        @foreach ($permissions as $permission)
            <label class="inline-flex items-center">
                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                    @if (in_array($permission->name, $rolePermissions ?? [])) checked @endif>
                <span class="ml-1">{{ $permission->name }}</span>
            </label>
        @endforeach
    </div>
</div>