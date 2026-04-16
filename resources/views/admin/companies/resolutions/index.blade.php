<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Paso 4: Resoluciones — {{ $company->business_name }}
        </h2>
    </x-slot>

    <div class="py-12 max-w-5xl mx-auto sm:px-6 lg:px-8">

        {{-- Progreso --}}
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

        {{-- Debug --}}
        @if (session('api_debug'))
            @php $debug = session('api_debug'); @endphp
            <div class="mb-6 bg-yellow-50 border-2 border-yellow-400 p-6 shadow rounded">
                <h3 class="font-bold text-lg mb-3">🔍 Debug del último request al API</h3>
                <div class="mb-3">
                    <span class="font-semibold">Status:</span>
                    <span class="px-2 py-1 rounded font-mono {{ $debug['status'] >= 200 && $debug['status'] < 300 ? 'bg-green-200' : 'bg-red-300' }}">
                        {{ $debug['status'] }}
                    </span>
                    <code class="text-sm ml-2">{{ $debug['method'] }} {{ $debug['url'] }}</code>
                </div>
                <div class="mb-3">
                    <p class="font-semibold mb-1">📤 JSON enviado:</p>
                    <pre class="bg-gray-900 text-blue-200 p-3 rounded text-xs overflow-auto max-h-80">{{ $debug['payload_json'] }}</pre>
                </div>
                <div class="mb-3">
                    <p class="font-semibold mb-1">📥 Respuesta:</p>
                    <pre class="bg-gray-900 text-yellow-200 p-3 rounded text-xs overflow-auto max-h-80">{{ is_array($debug['response_body']) ? json_encode($debug['response_body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $debug['response_body'] }}</pre>
                </div>
            </div>
        @endif

        {{-- ============================================================ --}}
        {{-- PARTE 1: RESOLUCIÓN DE HABILITACIÓN (PRUEBAS) --}}
        {{-- ============================================================ --}}
        <div class="mb-6 bg-white p-6 shadow rounded border-l-4 border-blue-500">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="font-bold text-lg">🧪 Parte 1: Resolución de habilitación (pruebas DIAN)</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Datos fijos para el ambiente de pruebas DIAN. Se envía una sola vez con un click.
                    </p>
                </div>
                @if ($company->habilitation_passed)
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded font-semibold text-sm">
                        ✅ Habilitada {{ $company->habilitation_passed_at?->diffForHumans() }}
                    </span>
                @elseif ($company->habilitationResolution)
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded font-semibold text-sm">
                        ⏳ Resolución creada, esperando pruebas
                    </span>
                @else
                    <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded font-semibold text-sm">
                        Pendiente
                    </span>
                @endif
            </div>

            @if ($company->habilitationResolution)
                @php $hab = $company->habilitationResolution; @endphp
                <div class="bg-gray-50 p-4 rounded mb-4 text-sm">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div><span class="font-semibold">Prefijo:</span> {{ $hab->prefix }}</div>
                        <div><span class="font-semibold">Resolución:</span> {{ $hab->resolution }}</div>
                        <div><span class="font-semibold">Desde:</span> {{ $hab->from }}</div>
                        <div><span class="font-semibold">Hasta:</span> {{ $hab->to }}</div>
                        <div class="col-span-2"><span class="font-semibold">Clave técnica:</span> <code class="text-xs">{{ $hab->technical_key }}</code></div>
                        <div><span class="font-semibold">Vigencia:</span> {{ $hab->date_from?->format('Y-m-d') }} → {{ $hab->date_to?->format('Y-m-d') }}</div>
                        <div><span class="font-semibold">Última sync:</span> {{ $hab->last_synced_at?->diffForHumans() ?? '—' }}</div>
                    </div>
                </div>
            @endif

            <div class="flex flex-wrap gap-2">
				{{-- Botón crear/reenviar resolución --}}
				<form method="POST" action="{{ route('admin.companies.resolutions.habilitation', $company) }}">
					@csrf
					<button class="px-4 py-2 bg-blue-600 text-white rounded"
							@disabled(empty($company->api_token))>
						@if ($company->habilitationResolution)
							🔄 Reenviar resolución de habilitación
						@else
							➕ Crear resolución de habilitación
						@endif
					</button>
				</form>

				{{-- Botón enviar factura de prueba --}}
				@if ($company->habilitationResolution)
					<a href="{{ route('admin.companies.resolutions.test-invoice.show', $company) }}"
					   class="px-4 py-2 bg-orange-600 text-white rounded inline-block">
						🧾 Preparar factura de prueba
					</a>

					{{-- Botón nota crédito de prueba --}}
					<a href="{{ route('admin.companies.resolutions.test-credit-note.show', $company) }}"
					   class="px-4 py-2 bg-green-700 text-white rounded inline-block">
						📋 Preparar nota crédito de prueba
					</a>
				@endif

				{{-- Toggle de habilitación manual --}}
				@if ($company->habilitationResolution)
					<form method="POST" action="{{ route('admin.companies.resolutions.toggle-habilitation', $company) }}">
						@csrf
						<button class="px-4 py-2 {{ $company->habilitation_passed ? 'bg-gray-500' : 'bg-green-600' }} text-white rounded">
							@if ($company->habilitation_passed)
								↩ Desmarcar como habilitada
							@else
								✅ Marcar como habilitada
							@endif
						</button>
					</form>
				@endif
			</div>
			
			@if ($company->test_invoice_sent_at)
				<div class="mt-4 p-4 rounded border-2 {{ $company->test_invoice_success ? 'border-green-400 bg-green-50' : 'border-red-400 bg-red-50' }}">
					<div class="flex items-center justify-between mb-2">
						<h4 class="font-semibold">
							{{ $company->test_invoice_success ? '✅' : '❌' }}
							Última factura de prueba
						</h4>
						<span class="text-xs text-gray-600">
							Enviada {{ $company->test_invoice_sent_at->diffForHumans() }}
						</span>
					</div>

					@if ($company->test_invoice_response)
						<details class="text-sm">
							<summary class="cursor-pointer font-semibold text-gray-700">
								Ver respuesta del API
							</summary>
							<pre class="mt-2 bg-gray-900 text-green-200 p-3 rounded text-xs overflow-auto max-h-96">{{ json_encode($company->test_invoice_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
						</details>
					@endif
				</div>
			@endif

            @if (!$company->habilitation_passed && $company->habilitationResolution)
                <p class="text-sm text-gray-600 mt-3">
                    💡 Hacé las pruebas de facturación contra el ambiente de habilitación DIAN.
                    Cuando la DIAN te apruebe, tocá "Marcar como habilitada" para desbloquear
                    las resoluciones de producción.
                </p>
            @endif
        </div>

        {{-- ============================================================ --}}
        {{-- PARTE 2: RESOLUCIONES DE PRODUCCIÓN --}}
        {{-- ============================================================ --}}
        <div class="mb-6 bg-white p-6 shadow rounded border-l-4 {{ $company->habilitation_passed ? 'border-green-500' : 'border-gray-300' }}">
            <h3 class="font-bold text-lg mb-2">📄 Parte 2: Resoluciones de producción — Facturación electrónica</h3>

            @if (!$company->habilitation_passed)
                <div class="p-4 bg-gray-50 rounded text-sm text-gray-600">
                    🔒 Esta sección se desbloquea cuando marques la empresa como habilitada en la Parte 1.
                </div>
            @else

                {{-- Listado de resoluciones existentes --}}
                @if ($company->productionResolutions->count() > 0)
                    <div class="mb-6 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-3 py-2 text-left">Prefijo</th>
                                    <th class="px-3 py-2 text-left">Resolución</th>
                                    <th class="px-3 py-2 text-left">Rango</th>
                                    <th class="px-3 py-2 text-left">Vigencia</th>
                                    <th class="px-3 py-2 text-left">Sync</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($company->productionResolutions as $res)
                                    <tr class="border-b">
                                        <td class="px-3 py-2">{{ $res->prefix ?? '—' }}</td>
                                        <td class="px-3 py-2 font-mono text-xs">{{ $res->resolution }}</td>
                                        <td class="px-3 py-2">{{ number_format($res->from) }} → {{ number_format($res->to) }}</td>
                                        <td class="px-3 py-2 text-xs">
                                            {{ $res->date_from?->format('Y-m-d') }}<br>
                                            {{ $res->date_to?->format('Y-m-d') }}
                                        </td>
                                        <td class="px-3 py-2 text-xs">
                                            {{ $res->last_synced_at?->diffForHumans() ?? '—' }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <form action="{{ route('admin.companies.resolutions.destroy', [$company, $res]) }}" method="POST" class="inline">
                                                @csrf @method('DELETE')
                                                <button class="text-red-600 text-xs" onclick="return confirm('¿Eliminar localmente?')">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500 mb-4">No hay resoluciones de producción cargadas todavía.</p>
                @endif

                {{-- Formulario de nueva resolución --}}
                <div class="border-t pt-4">
                    <h4 class="font-semibold mb-3">➕ Nueva resolución</h4>
                    <form method="POST" action="{{ route('admin.companies.resolutions.store', $company) }}">
                        @csrf
                        @include('admin.companies.resolutions._form')
                        <button class="px-4 py-2 bg-blue-600 text-white rounded">Crear y enviar al API</button>
                    </form>
                </div>

            @endif
        </div>

        <div class="text-right">
            <a href="{{ route('admin.companies.edit', $company) }}"
               class="px-4 py-2 bg-gray-300 rounded inline-block">
                ← Volver a la empresa
            </a>
        </div>

    </div>
</x-app-layout>