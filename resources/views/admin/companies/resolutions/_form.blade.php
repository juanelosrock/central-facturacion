@if ($errors->any())
    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div>
        <label class="block text-sm font-semibold">Tipo de documento (ID)</label>
        <input type="number" name="type_document_id" value="{{ old('type_document_id', 1) }}"
               class="w-full border rounded p-2">
        <p class="text-xs text-gray-500 mt-1">1 = Factura electrónica de venta</p>
    </div>

    <div>
        <label class="block text-sm font-semibold">Prefijo</label>
        <input type="text" name="prefix" value="{{ old('prefix') }}"
               class="w-full border rounded p-2 uppercase" placeholder="FE">
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-semibold">Número de resolución DIAN</label>
        <input type="text" name="resolution" value="{{ old('resolution') }}"
               class="w-full border rounded p-2" placeholder="18764000001234">
    </div>

    <div>
        <label class="block text-sm font-semibold">Fecha de la resolución</label>
        <input type="date" name="resolution_date" value="{{ old('resolution_date') }}"
               class="w-full border rounded p-2">
    </div>

    <div>
        <label class="block text-sm font-semibold">Clave técnica</label>
        <input type="text" name="technical_key" value="{{ old('technical_key') }}"
               class="w-full border rounded p-2 font-mono text-xs">
    </div>

    <div>
        <label class="block text-sm font-semibold">Rango desde</label>
        <input type="number" name="from" value="{{ old('from') }}"
               class="w-full border rounded p-2" placeholder="1">
    </div>

    <div>
        <label class="block text-sm font-semibold">Rango hasta</label>
        <input type="number" name="to" value="{{ old('to') }}"
               class="w-full border rounded p-2" placeholder="5000">
    </div>

    <div>
        <label class="block text-sm font-semibold">Generados a la fecha</label>
        <input type="number" name="generated_to_date" value="{{ old('generated_to_date', 0) }}"
               class="w-full border rounded p-2">
    </div>

    <div></div>

    <div>
        <label class="block text-sm font-semibold">Vigencia desde</label>
        <input type="date" name="date_from" value="{{ old('date_from') }}"
               class="w-full border rounded p-2">
    </div>

    <div>
        <label class="block text-sm font-semibold">Vigencia hasta</label>
        <input type="date" name="date_to" value="{{ old('date_to') }}"
               class="w-full border rounded p-2">
    </div>
</div>