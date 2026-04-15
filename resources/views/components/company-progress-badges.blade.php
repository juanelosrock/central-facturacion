@props(['company'])

@php
    $step1 = !empty($company->api_token);
    $step2 = $company->software && $company->software->last_synced_at;
    $step3 = $company->certificate && $company->certificate->last_synced_at;
    $step3Expired = $company->certificate && $company->certificate->expiration_date?->isPast();
	$step4 = $company->habilitation_passed;
@endphp

<div class="flex gap-1">
    {{-- Paso 1: Empresa --}}
    <span class="px-2 py-0.5 rounded text-xs font-semibold
        {{ $step1 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}"
        title="Paso 1: Empresa">
        1 {{ $step1 ? '✓' : '' }}
    </span>

    {{-- Paso 2: Software --}}
    <span class="px-2 py-0.5 rounded text-xs font-semibold
        {{ $step2 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}"
        title="Paso 2: Software">
        2 {{ $step2 ? '✓' : '' }}
    </span>

    {{-- Paso 3: Certificado --}}
    <span class="px-2 py-0.5 rounded text-xs font-semibold
        @if ($step3 && $step3Expired)
            bg-red-100 text-red-800
        @elseif ($step3)
            bg-green-100 text-green-800
        @else
            bg-gray-100 text-gray-500
        @endif"
        title="Paso 3: Certificado{{ $step3Expired ? ' (vencido)' : '' }}">
        3 {{ $step3 ? ($step3Expired ? '⚠' : '✓') : '' }}
    </span>
	{{-- Paso 4: Resoluciones --}}
	@php $step4 = $company->habilitation_passed; @endphp
	<span class="px-2 py-0.5 rounded text-xs font-semibold
		{{ $step4 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}"
		title="Paso 4: Resoluciones">
		4 {{ $step4 ? '✓' : '' }}
	</span>
</div>