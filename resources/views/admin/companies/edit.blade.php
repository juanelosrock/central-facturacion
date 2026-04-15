<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Editar empresa — {{ $company->business_name }}
        </h2>
    </x-slot>

    <div class="py-12 max-w-4xl mx-auto sm:px-6 lg:px-8">

        {{-- Indicador de progreso --}}
        <div class="mb-6 bg-white p-6 shadow rounded">
            <h3 class="font-semibold mb-4 text-gray-700">Progreso de configuración</h3>
            <x-company-progress :company="$company" size="lg" />
        </div>

        {{-- Mensajes flash --}}
        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded font-semibold">
                ❌ {{ session('error') }}
            </div>
        @endif

        {{-- Debug del último request al API --}}
        @if (session('api_debug'))
            @php $debug = session('api_debug'); @endphp
            <div class="mb-6 bg-yellow-50 border-2 border-yellow-400 p-6 shadow rounded">
                <h3 class="font-bold text-lg mb-3">🔍 Debug del último request al API</h3>

                <div class="mb-3">
                    <span class="font-semibold">Status HTTP:</span>
                    <span class="px-2 py-1 rounded font-mono {{ $debug['status'] >= 200 && $debug['status'] < 300 ? 'bg-green-200' : 'bg-red-300' }}">
                        {{ $debug['status'] }}
                    </span>
                </div>

                <div class="mb-3">
                    <span class="font-semibold">URL:</span>
                    <code class="text-sm break-all">{{ $debug['method'] }} {{ $debug['url'] }}</code>
                </div>

                <div class="mb-3">
                    <p class="font-semibold mb-1">Headers enviados:</p>
                    <pre class="bg-gray-900 text-green-200 p-3 rounded text-xs overflow-auto">{{ json_encode($debug['headers'], JSON_PRETTY_PRINT) }}</pre>
                </div>

                <div class="mb-3">
                    <p class="font-semibold mb-1">📤 JSON enviado:</p>
                    <pre class="bg-gray-900 text-blue-200 p-3 rounded text-xs overflow-auto max-h-96">{{ $debug['payload_json'] }}</pre>
                </div>

                <div class="mb-3">
                    <p class="font-semibold mb-1">📥 Respuesta del API:</p>
                    <pre class="bg-gray-900 text-yellow-200 p-3 rounded text-xs overflow-auto max-h-96">{{ is_array($debug['response_body']) ? json_encode($debug['response_body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $debug['response_body'] }}</pre>
                </div>

                @if (!empty($debug['curl_errno']))
                    <div class="mb-3">
                        <p class="font-semibold mb-1 text-red-700">⚠️ Error de cURL:</p>
                        <pre class="bg-red-900 text-red-100 p-3 rounded text-xs">{{ $debug['curl_error'] }} (errno {{ $debug['curl_errno'] }})</pre>
                    </div>
                @endif

                <div class="mb-3">
                    <p class="font-semibold mb-1">🐚 cURL equivalente:</p>
                    <pre class="bg-gray-900 text-white p-3 rounded text-xs overflow-auto max-h-96">{{ $debug['curl'] }}</pre>
                </div>
            </div>
        @endif

        {{-- Formulario principal --}}
        <div class="bg-white p-6 shadow rounded">
            <form method="POST" action="{{ route('admin.companies.update', $company) }}">
                @csrf @method('PUT')
                @include('admin.companies._form')
                <button class="px-4 py-2 bg-blue-600 text-white rounded">Actualizar y enviar al API</button>
            </form>
        </div>

        {{-- Última respuesta guardada --}}
        @if ($company->api_response)
            <div class="bg-white p-6 shadow rounded mt-6">
                <h3 class="font-semibold mb-2">Última respuesta exitosa del paso 1</h3>
                <p class="text-sm text-gray-600 mb-2">
                    Sincronizado {{ $company->last_synced_at?->diffForHumans() }}
                </p>
                <pre class="bg-gray-100 p-3 rounded text-xs overflow-auto max-h-96">{{ json_encode($company->api_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @endif

    </div>
</x-app-layout>