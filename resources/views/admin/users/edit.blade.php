<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Editar usuario</h2>
    </x-slot>

    <div class="py-12 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow rounded">
            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf @method('PUT')
                @include('admin.users._form')
                <button class="px-4 py-2 bg-blue-600 text-white rounded">Actualizar</button>
            </form>
        </div>
    </div>
</x-app-layout>