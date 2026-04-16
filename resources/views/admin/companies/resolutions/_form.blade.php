@if ($errors->any())
    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div x-data="{ tipoDoc: {{ old('type_document_id', 1) }} }" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">

    {{-- Tipo de documento: siempre visible --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold mb-1">Tipo de documento</label>
        <select name="type_document_id" x-model.number="tipoDoc"
                class="w-full border rounded p-2">
            <option value="1">1 — Factura electrónica de venta</option>
            <option value="2">2 — Factura de exportación</option>
            <option value="3">3 — Factura de contingencia</option>
            <option value="4">4 — Nota crédito</option>
            <option value="5">5 — Nota débito</option>
            <option value="7">7 — Documento soporte</option>
        </select>
    </div>

    {{-- Prefijo: siempre visible --}}
    <div>
        <label class="block text-sm font-semibold mb-1">Prefijo</label>
        <input type="text" name="prefix" value="{{ old('prefix') }}"
               class="w-full border rounded p-2 uppercase"
               :placeholder="tipoDoc === 4 ? 'NC' : tipoDoc === 5 ? 'ND' : 'FE'">
    </div>

    {{-- Rango desde/hasta: siempre visible --}}
    <div>
        <label class="block text-sm font-semibold mb-1">Rango desde</label>
        <input type="number" name="from" value="{{ old('from', 1) }}"
               class="w-full border rounded p-2" placeholder="1">
    </div>

    <div>
        <label class="block text-sm font-semibold mb-1">Rango hasta</label>
        <input type="number" name="to" value="{{ old('to') }}"
               class="w-full border rounded p-2" placeholder="99999999">
    </div>

    {{-- Campos solo para tipos que requieren resolución DIAN (no nota crédito/débito) --}}
    <template x-if="tipoDoc !== 4 && tipoDoc !== 5">
        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Número de resolución DIAN</label>
                <input type="text" name="resolution" value="{{ old('resolution') }}"
                       class="w-full border rounded p-2" placeholder="18764000001234">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Fecha de la resolución</label>
                <input type="date" name="resolution_date" value="{{ old('resolution_date') }}"
                       class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Clave técnica</label>
                <input type="text" name="technical_key" value="{{ old('technical_key') }}"
                       class="w-full border rounded p-2 font-mono text-xs">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Generados a la fecha</label>
                <input type="number" name="generated_to_date" value="{{ old('generated_to_date', 0) }}"
                       class="w-full border rounded p-2">
            </div>

            <div></div>

            <div>
                <label class="block text-sm font-semibold mb-1">Vigencia desde</label>
                <input type="date" name="date_from" value="{{ old('date_from') }}"
                       class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Vigencia hasta</label>
                <input type="date" name="date_to" value="{{ old('date_to') }}"
                       class="w-full border rounded p-2">
            </div>
        </div>
    </template>

    {{-- Nota informativa para nota crédito/débito --}}
    <template x-if="tipoDoc === 4 || tipoDoc === 5">
        <div class="md:col-span-2 p-3 bg-blue-50 border border-blue-200 rounded text-sm text-blue-700">
            ℹ️ Las notas crédito/débito no requieren número de resolución DIAN, fecha ni clave técnica.
            Solo se envían prefijo y rango.
        </div>
    </template>

</div>
