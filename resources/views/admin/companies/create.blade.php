<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Nueva empresa</h2>
    </x-slot>

    <div class="py-12 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow rounded">
            <form method="POST" action="{{ route('admin.companies.store') }}">
                @csrf
                @include('admin.companies._form', ['company' => null])
                <button class="px-4 py-2 bg-blue-600 text-white rounded">Crear y enviar al API</button>
            </form>
        </div>
    </div>
</x-app-layout>