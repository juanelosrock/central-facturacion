<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Empresas</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @if (session('success'))
                    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
                @endif

                @can('companies.create')
                    <a href="{{ route('admin.companies.create') }}"
                       class="inline-block mb-4 px-4 py-2 bg-blue-600 text-white rounded">
                        Nueva empresa
                    </a>
                @endcan

                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">NIT</th>
                            <th class="px-4 py-2 text-left">Razón social</th>
                            <th class="px-4 py-2 text-left">Email</th>
                            <th class="px-4 py-2 text-left">Progreso</th>
                            <th class="px-4 py-2 text-left">Última sync</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($companies as $company)
                            <tr class="border-b">
                                <td class="px-4 py-2">{{ $company->identification_number }}-{{ $company->dv }}</td>
                                <td class="px-4 py-2">{{ $company->business_name }}</td>
                                <td class="px-4 py-2">{{ $company->email }}</td>
                                <td class="px-4 py-2">
                                    <x-company-progress-badges :company="$company" />
                                </td>
                                <td class="px-4 py-2 text-sm">
                                    {{ $company->last_synced_at?->diffForHumans() ?? '—' }}
                                </td>
                                <td class="px-4 py-2 text-right">
                                    @can('companies.sync')
                                        <form action="{{ route('admin.companies.sync', $company) }}" method="POST" class="inline">
                                            @csrf
                                            <button class="text-green-600">Sincronizar</button>
                                        </form>
                                    @endcan
                                    @can('companies.edit')
                                        <a href="{{ route('admin.companies.edit', $company) }}" class="text-blue-600 ml-2">Editar</a>
                                    @endcan
                                    @can('companies.delete')
                                        <form action="{{ route('admin.companies.destroy', $company) }}" method="POST" class="inline">
                                            @csrf @method('DELETE')
                                            <button class="text-red-600 ml-2" onclick="return confirm('¿Eliminar localmente?')">Eliminar</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4">{{ $companies->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>