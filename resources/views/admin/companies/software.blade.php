<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Paso 2: Configurar software — {{ $company->business_name }}
        </h2>
    </x-slot>	
    <div class="py-12 max-w-4xl mx-auto sm:px-6 lg:px-8">
		<div class="mb-6 bg-white p-6 shadow rounded">
			<h3 class="font-semibold mb-4 text-gray-700">Progreso de configuración</h3>
			<x-company-progress :company="$company" size="lg" />
		</div>
        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded font-semibold">❌ {{ session('error') }}</div>
        @endif

        {{-- Resumen empresa --}}
        <div class="mb-6 bg-white p-6 shadow rounded">
            <h3 class="font-semibold mb-3">Empresa</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                <div><dt class="font-semibold inline">NIT:</dt> {{ $company->identification_number }}-{{ $company->dv }}</div>
                <div><dt class="font-semibold inline">Razón social:</dt> {{ $company->business_name }}</div>
                <div class="md:col-span-2">
                    <dt class="font-semibold inline">Token API:</dt>
                    @if ($company->api_token)
                        <code class="text-xs">{{ substr($company->api_token, 0, 20) }}...</code>
                        <span class="text-green-600 ml-2">✓ disponible</span>
                    @else
                        <span class="text-red-600">⚠ no generado — sincronizá primero el paso 1</span>
                    @endif
                </div>
            </dl>
        </div>

        {{-- Debug --}}
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

                <div class="mb-3">
                    <p class="font-semibold mb-1">🐚 cURL equivalente:</p>
                    <pre class="bg-gray-900 text-white p-3 rounded text-xs overflow-auto max-h-96">{{ $debug['curl'] }}</pre>
                </div>
            </div>
        @endif

        {{-- Formulario --}}
        <div class="bg-white p-6 shadow rounded">
            <form method="POST" action="{{ route('admin.companies.software.update', $company) }}">
                @csrf @method('PUT')

                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <h3 class="font-semibold text-lg mb-3 border-b pb-1">Datos del software DIAN</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold">Software ID (UUID)</label>
                        <input type="text" name="identifier"
                               value="{{ old('identifier', $software->identifier ?? '') }}"
                               class="w-full border rounded p-2 font-mono text-sm"
                               placeholder="7c19b1ae-f8c7-4eee-a52b-1b9a88454708">
                        <p class="text-xs text-gray-500 mt-1">UUID asignado por la DIAN al software de facturación.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold">PIN</label>
                        <input type="number" name="pin"
                               value="{{ old('pin', $software->pin ?? '') }}"
                               class="w-full border rounded p-2"
                               placeholder="89092">
                        <p class="text-xs text-gray-500 mt-1">PIN numérico definido al registrar el software.</p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded"
                            @disabled(empty($company->api_token))>
                        Guardar y enviar al API
                    </button>
                    <a href="{{ route('admin.companies.edit', $company) }}"
                       class="px-4 py-2 bg-gray-300 rounded">Volver</a>
                </div>

                @if (empty($company->api_token))
                    <p class="text-red-600 text-sm mt-2">
                        El botón está deshabilitado porque la empresa no tiene token. Volvé al paso 1 y sincronizá.
                    </p>
                @endif
            </form>
        </div>

        {{-- Detalles del software ya configurado --}}
        @if ($software && $software->last_synced_at)
            <div class="bg-white p-6 shadow rounded mt-6">
                <h3 class="font-semibold mb-3">✅ Software configurado</h3>
                <p class="text-sm text-gray-600 mb-3">
                    Última sincronización: {{ $software->last_synced_at->diffForHumans() }}
                </p>

                <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div>
                        <dt class="font-semibold">ID interno (Qimera)</dt>
                        <dd>{{ $software->api_software_id ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold">Identifier (UUID)</dt>
                        <dd class="font-mono text-xs">{{ $software->identifier }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold">PIN</dt>
                        <dd>{{ $software->pin }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="font-semibold">URL DIAN (facturación)</dt>
                        <dd class="font-mono text-xs break-all">{{ $software->url ?? '—' }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="font-semibold">URL DIAN (nómina)</dt>
                        <dd class="font-mono text-xs break-all">{{ $software->url_payroll ?? '—' }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="font-semibold">URL DIAN (docs equivalentes)</dt>
                        <dd class="font-mono text-xs break-all">{{ $software->url_eqdocs ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        @endif

    </div>
</x-app-layout>