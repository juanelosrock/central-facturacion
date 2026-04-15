@if ($errors->any())
    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<h3 class="font-semibold text-lg mb-3 border-b pb-1">Identificación</h3>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div>
        <label class="block text-sm font-semibold">NIT (identification_number)</label>
        <input type="text" name="identification_number"
               value="{{ old('identification_number', $company->identification_number ?? '') }}"
               class="w-full border rounded p-2" {{ isset($company) ? 'readonly' : '' }}>
    </div>
    <div>
        <label class="block text-sm font-semibold">Dígito de verificación (DV)</label>
        <input type="text" name="dv" maxlength="2"
               value="{{ old('dv', $company->dv ?? '') }}"
               class="w-full border rounded p-2" {{ isset($company) ? 'readonly' : '' }}>
    </div>
</div>

<h3 class="font-semibold text-lg mb-3 border-b pb-1">Datos de la empresa</h3>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div>
        <label class="block text-sm font-semibold">Razón social</label>
        <input type="text" name="business_name"
               value="{{ old('business_name', $company->business_name ?? '') }}"
               class="w-full border rounded p-2">
    </div>
    <div>
        <label class="block text-sm font-semibold">Matrícula mercantil</label>
        <input type="text" name="merchant_registration"
               value="{{ old('merchant_registration', $company->merchant_registration ?? '') }}"
               class="w-full border rounded p-2">
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold">Dirección</label>
        <input type="text" name="address"
               value="{{ old('address', $company->address ?? '') }}"
               class="w-full border rounded p-2">
    </div>
    <div>
        <label class="block text-sm font-semibold">Teléfono</label>
        <input type="text" name="phone"
               value="{{ old('phone', $company->phone ?? '') }}"
               class="w-full border rounded p-2">
    </div>
    <div>
        <label class="block text-sm font-semibold">Email</label>
        <input type="email" name="email"
               value="{{ old('email', $company->email ?? '') }}"
               class="w-full border rounded p-2">
    </div>
</div>

<h3 class="font-semibold text-lg mb-3 border-b pb-1">Catálogos (IDs)</h3>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div>
        <label class="block text-sm font-semibold">Tipo de documento</label>
        <input type="number" name="type_document_identification_id"
               value="{{ old('type_document_identification_id', $company->type_document_identification_id ?? 6) }}"
               class="w-full border rounded p-2">
    </div>
    <div>
        <label class="block text-sm font-semibold">Tipo de organización</label>
        <input type="number" name="type_organization_id"
               value="{{ old('type_organization_id', $company->type_organization_id ?? 1) }}"
               class="w-full border rounded p-2">
    </div>
    <div>
        <label class="block text-sm font-semibold">Tipo de régimen</label>
        <input type="number" name="type_regime_id"
               value="{{ old('type_regime_id', $company->type_regime_id ?? 1) }}"
               class="w-full border rounded p-2">
    </div>
    <div>
        <label class="block text-sm font-semibold">Tipo de responsabilidad</label>
        <input type="number" name="type_liability_id"
               value="{{ old('type_liability_id', $company->type_liability_id ?? 14) }}"
               class="w-full border rounded p-2">
    </div>
    <div>
		<label class="block text-sm font-semibold">Municipio</label>
		<input type="number" name="municipality_id"
			   value="{{ old('municipality_id', $company->municipality_id ?? '') }}"
			   class="w-full border rounded p-2">
	</div>
</div>

<h3 class="font-semibold text-lg mb-3 border-b pb-1">Configuración de correo</h3>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div>
        <label class="block text-sm font-semibold">Mail host</label>
        <input type="text" name="mail_host"
               value="{{ old('mail_host', $company->mail_host ?? 'smtp.gmail.com') }}"
               class="w-full border rounded p-2">
    </div>
    <div>
        <label class="block text-sm font-semibold">Mail port</label>
        <input type="text" name="mail_port"
               value="{{ old('mail_port', $company->mail_port ?? '587') }}"
               class="w-full border rounded p-2">
    </div>
    <div>
        <label class="block text-sm font-semibold">Mail username</label>
        <input type="text" name="mail_username"
               value="{{ old('mail_username', $company->mail_username ?? '') }}"
               class="w-full border rounded p-2">
    </div>
    <div>
        <label class="block text-sm font-semibold">Mail password</label>
        <input type="text" name="mail_password"
               value="{{ old('mail_password', $company->mail_password ?? '') }}"
               class="w-full border rounded p-2">
    </div>
    <div>
        <label class="block text-sm font-semibold">Mail encryption</label>
        <select name="mail_encryption" class="w-full border rounded p-2">
            @php $enc = old('mail_encryption', $company->mail_encryption ?? 'tls'); @endphp
            <option value="tls" @selected($enc === 'tls')>tls</option>
            <option value="ssl" @selected($enc === 'ssl')>ssl</option>
        </select>
    </div>
</div>