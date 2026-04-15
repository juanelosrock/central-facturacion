<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Configuración del API</h2>
    </x-slot>

    <div class="py-12 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow rounded">
            @if (session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf @method('PUT')

                <div class="mb-4">
                    <label class="block font-semibold">URL base del API</label>
                    <input type="url" name="qimera_api_url"
                           value="{{ old('qimera_api_url', $settings['qimera_api_url']) }}"
                           class="w-full border rounded p-2"
                           placeholder="https://factura.grupoqimera.co/api">
                    <p class="text-sm text-gray-500 mt-1">Sin slash final. Ejemplo: https://factura.grupoqimera.co/api</p>
                </div>

                <div class="mb-4">
					<label class="block font-semibold">Bearer Token (opcional)</label>
					<input type="text" name="qimera_api_token"
						   value="{{ old('qimera_api_token', $settings['qimera_api_token']) }}"
						   class="w-full border rameworkounded p-2 font-mono text-sm">
					<p class="text-sm text-gray-500 mt-1">
						Solo necesario para endpoints autenticados. El endpoint de creación de empresa no lo requiere.
					</p>
				</div>

                @error('qimera_api_url') <div class="text-red-600">{{ $message }}</div> @enderror
                @error('qimera_api_token') <div class="text-red-600">{{ $message }}</div> @enderror

                <button class="px-4 py-2 bg-blue-600 text-white rounded">Guardar</button>
            </form>
        </div>
    </div>
</x-app-layout>