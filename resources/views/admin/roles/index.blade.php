<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Roles</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @if (session('success'))
                    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @can('roles.create')
                    <a href="{{ route('admin.roles.create') }}"
                       class="inline-block mb-4 px-4 py-2 bg-blue-600 text-white rounded">
                        Nuevo rol
                    </a>
                @endcan

                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">Nombre</th>
                            <th class="px-4 py-2 text-left">Permisos</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr class="border-b">
                                <td class="px-4 py-2">{{ $role->name }}</td>
                                <td class="px-4 py-2 text-sm">{{ $role->permissions->pluck('name')->join(', ') }}</td>
                                <td class="px-4 py-2 text-right">
                                    @can('roles.edit')
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="text-blue-600">Editar</a>
                                    @endcan
                                    @can('roles.delete')
                                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="inline">
                                            @csrf @method('DELETE')
                                            <button class="text-red-600 ml-2" onclick="return confirm('¿Eliminar?')">
                                                Eliminar
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4">{{ $roles->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>