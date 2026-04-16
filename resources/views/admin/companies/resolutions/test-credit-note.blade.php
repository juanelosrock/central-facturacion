<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">
                📋 Nota crédito de prueba — {{ $company->business_name }}
            </h2>
            <a href="{{ route('admin.companies.resolutions.index', $company) }}"
               class="text-sm text-gray-500 hover:text-gray-700">
                ← Volver a resoluciones
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8" x-data="testCreditNoteEditor()">

        @if (session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded font-semibold">❌ {{ session('error') }}</div>
        @endif

        {{-- Debug panel --}}
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
                    <p class="font-semibold mb-1">📥 Respuesta:</p>
                    <pre class="bg-gray-900 text-yellow-200 p-3 rounded text-xs overflow-auto max-h-80">{{ is_array($debug['response_body']) ? json_encode($debug['response_body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $debug['response_body'] }}</pre>
                </div>
            </div>
        @endif

        <div class="bg-white shadow rounded overflow-hidden">

            {{-- Barra superior tipo Postman --}}
            <div class="bg-gray-800 px-4 py-3 flex items-center gap-3">
                <span class="px-3 py-1 bg-green-600 text-white text-xs font-bold rounded font-mono">POST</span>
                <code class="text-green-300 text-sm flex-1 truncate">
                    {{ rtrim(App\Models\Setting::get('qimera_api_url', ''), '/') }}/ubl2.1/credit-note
                </code>
                <span class="text-gray-400 text-xs">Auth: Bearer token empresa</span>
            </div>

            <div class="flex" style="min-height: 520px;">

                {{-- Panel izquierdo: editor JSON --}}
                <div class="flex-1 flex flex-col border-r border-gray-200">
                    <div class="bg-gray-100 px-4 py-2 flex items-center justify-between border-b border-gray-200">
                        <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Body · JSON</span>
                        <div class="flex gap-2">
                            <button type="button"
                                    @click="formatJson()"
                                    class="text-xs px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded font-mono">
                                Formatear
                            </button>
                            <button type="button"
                                    @click="resetPayload()"
                                    class="text-xs px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded">
                                Resetear
                            </button>
                        </div>
                    </div>

                    <div class="relative flex-1">
                        {{-- Números de línea --}}
                        <div class="absolute top-0 left-0 bottom-0 w-10 bg-gray-50 border-r border-gray-200 overflow-hidden pointer-events-none select-none">
                            <div class="p-2 text-right font-mono text-xs text-gray-400 leading-5" x-html="lineNumbers"></div>
                        </div>

                        <textarea
                            name="payload_json"
                            x-ref="editor"
                            x-model="json"
                            @input="updateLineNumbers(); validateJson()"
                            @keydown.tab.prevent="insertTab($event)"
                            spellcheck="false"
                            class="absolute inset-0 w-full h-full resize-none font-mono text-xs leading-5 p-2 pl-12 bg-gray-900 text-green-200 focus:outline-none border-0"
                            style="tab-size: 2;"
                        ></textarea>
                    </div>

                    {{-- Barra de estado del JSON --}}
                    <div class="bg-gray-100 border-t border-gray-200 px-4 py-1.5 flex items-center gap-4 text-xs">
                        <span :class="jsonValid ? 'text-green-600' : 'text-red-600'" x-text="jsonValid ? '✓ JSON válido' : '✗ ' + jsonError"></span>
                        <span class="text-gray-400" x-text="'Líneas: ' + lineCount"></span>
                        <span class="text-gray-400" x-text="'Bytes: ' + json.length"></span>
                    </div>
                </div>

                {{-- Panel derecho: info de la empresa --}}
                <div class="w-72 flex flex-col bg-gray-50">
                    <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
                        <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Contexto</span>
                    </div>
                    <div class="p-4 text-xs space-y-3 flex-1">
                        <div>
                            <p class="font-semibold text-gray-500 uppercase text-xs mb-1">Empresa</p>
                            <p class="font-mono text-gray-800">{{ $company->business_name }}</p>
                            <p class="text-gray-500">NIT: {{ $company->nit }}-{{ $company->dv }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-500 uppercase text-xs mb-1">Token API</p>
                            <p class="font-mono text-gray-600 break-all">{{ Str::limit($company->api_token, 40) }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-500 uppercase text-xs mb-1">Resolución de habilitación</p>
                            @php $hab = $company->habilitationResolution; @endphp
                            <p class="font-mono text-gray-800">{{ $hab->prefix }} / {{ $hab->resolution }}</p>
                            <p class="text-gray-500">Rango: {{ number_format($hab->from) }} → {{ number_format($hab->to) }}</p>
                        </div>
                        @php $nc = $company->productionResolutions->where('type_document_id', 4)->first(); @endphp
                        @if ($nc)
                        <div>
                            <p class="font-semibold text-gray-500 uppercase text-xs mb-1">Resolución nota crédito</p>
                            <p class="font-mono text-gray-800">{{ $nc->prefix }}</p>
                            <p class="text-gray-500">Rango: {{ number_format($nc->from) }} → {{ number_format($nc->to) }}</p>
                        </div>
                        @endif
                        <div class="pt-2 border-t border-gray-200">
                            <p class="font-semibold text-gray-500 uppercase text-xs mb-1">Campos clave a editar</p>
                            <ul class="text-gray-500 space-y-1">
                                <li>• <code>billing_reference.number</code>: número de la factura a anular</li>
                                <li>• <code>billing_reference.uuid</code>: CUFE de la factura</li>
                                <li>• <code>number</code>: número correlativo de la NC</li>
                                <li>• <code>discrepancyresponsecode</code>: motivo (1-6)</li>
                                <li>• La fecha/hora se genera al recargar</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pie: botón de envío --}}
            <form method="POST" action="{{ route('admin.companies.resolutions.test-credit-note', $company) }}"
                  @submit.prevent="submitForm($el)">
                @csrf
                <input type="hidden" name="payload_json" x-model="json">
                <div class="bg-gray-800 px-4 py-3 flex items-center justify-between">
                    <div class="text-xs text-gray-400">
                        Endpoint: <code class="text-gray-300">POST /ubl2.1/credit-note</code>
                    </div>
                    <button type="submit"
                            :disabled="!jsonValid"
                            :class="jsonValid
                                ? 'bg-green-600 hover:bg-green-700 text-white cursor-pointer'
                                : 'bg-gray-600 text-gray-400 cursor-not-allowed'"
                            class="px-6 py-2 rounded font-semibold text-sm transition-colors flex items-center gap-2">
                        <span x-show="!sending">▶ Enviar nota crédito</span>
                        <span x-show="sending">⏳ Enviando...</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

    <script>
        function testCreditNoteEditor() {
            const initialJson = @json($payloadJson);

            return {
                json: initialJson,
                jsonValid: true,
                jsonError: '',
                lineCount: 0,
                lineNumbers: '',
                sending: false,

                init() {
                    this.updateLineNumbers();
                    this.validateJson();
                },

                updateLineNumbers() {
                    const lines = this.json.split('\n').length;
                    this.lineCount = lines;
                    this.lineNumbers = Array.from({ length: lines }, (_, i) => i + 1).join('<br>');
                },

                validateJson() {
                    try {
                        JSON.parse(this.json);
                        this.jsonValid = true;
                        this.jsonError = '';
                    } catch (e) {
                        this.jsonValid = false;
                        this.jsonError = e.message;
                    }
                },

                formatJson() {
                    try {
                        const parsed = JSON.parse(this.json);
                        this.json = JSON.stringify(parsed, null, 2);
                        this.updateLineNumbers();
                        this.jsonValid = true;
                        this.jsonError = '';
                    } catch (e) {
                        this.jsonError = 'No se puede formatear: ' + e.message;
                    }
                },

                resetPayload() {
                    if (confirm('¿Resetear al payload original?')) {
                        this.json = initialJson;
                        this.updateLineNumbers();
                        this.validateJson();
                    }
                },

                insertTab(event) {
                    const ta = event.target;
                    const start = ta.selectionStart;
                    const end = ta.selectionEnd;
                    this.json = this.json.substring(0, start) + '  ' + this.json.substring(end);
                    this.$nextTick(() => {
                        ta.selectionStart = ta.selectionEnd = start + 2;
                    });
                },

                submitForm(form) {
                    if (!this.jsonValid) return;
                    this.sending = true;
                    form.submit();
                },
            };
        }
    </script>
</x-app-layout>
