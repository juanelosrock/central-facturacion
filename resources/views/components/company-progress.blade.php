@props(['company', 'size' => 'md'])

@php
    $steps = [
        [
            'number' => 1,
            'label' => 'Empresa',
            'done' => !empty($company->api_token),
            'url' => route('admin.companies.edit', $company),
        ],
        [
            'number' => 2,
            'label' => 'Software',
            'done' => $company->software && $company->software->last_synced_at,
            'url' => route('admin.companies.software.edit', $company),
        ],
        [
            'number' => 3,
            'label' => 'Certificado',
            'done' => $company->certificate && $company->certificate->last_synced_at,
            'url' => route('admin.companies.certificate.edit', $company),
            'warning' => $company->certificate && $company->certificate->expiration_date?->isPast(),
        ],
		[
			'number' => 4,
			'label' => 'Resoluciones',
			'done' => $company->habilitation_passed,
			'url' => route('admin.companies.resolutions.index', $company),
		],
    ];

    $completed = collect($steps)->where('done', true)->count();
    $total = count($steps);
    $percent = (int) round(($completed / $total) * 100);

    $sizeClasses = match($size) {
        'sm' => ['circle' => 'w-6 h-6 text-xs', 'label' => 'text-xs', 'line' => 'h-0.5'],
        'lg' => ['circle' => 'w-12 h-12 text-base', 'label' => 'text-sm', 'line' => 'h-1'],
        default => ['circle' => 'w-8 h-8 text-sm', 'label' => 'text-xs', 'line' => 'h-1'],
    };
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    {{-- Barra de progreso --}}
    <div class="flex items-center">
        @foreach ($steps as $index => $step)
            {{-- Círculo del paso --}}
            <a href="{{ $step['url'] }}"
               class="flex flex-col items-center group"
               title="Paso {{ $step['number'] }}: {{ $step['label'] }}">
                <div class="{{ $sizeClasses['circle'] }} rounded-full flex items-center justify-center font-bold border-2 transition
                    @if ($step['done'] && !empty($step['warning']))
                        bg-yellow-500 border-yellow-600 text-white
                    @elseif ($step['done'])
                        bg-green-500 border-green-600 text-white
                    @else
                        bg-gray-200 border-gray-300 text-gray-600 group-hover:bg-gray-300
                    @endif">
                    @if ($step['done'] && empty($step['warning']))
                        ✓
                    @elseif ($step['done'] && !empty($step['warning']))
                        !
                    @else
                        {{ $step['number'] }}
                    @endif
                </div>
                <span class="{{ $sizeClasses['label'] }} mt-1 font-semibold
                    @if ($step['done'] && !empty($step['warning']))
                        text-yellow-700
                    @elseif ($step['done'])
                        text-green-700
                    @else
                        text-gray-600
                    @endif">
                    {{ $step['label'] }}
                </span>
            </a>

            {{-- Línea entre pasos (excepto después del último) --}}
            @if (!$loop->last)
                <div class="flex-1 {{ $sizeClasses['line'] }} mx-2 mb-5
                    @if ($steps[$index]['done']) bg-green-500 @else bg-gray-300 @endif">
                </div>
            @endif
        @endforeach
    </div>

    {{-- Texto resumen --}}
    @if ($size !== 'sm')
        <div class="mt-3 text-center text-sm text-gray-600">
            <span class="font-semibold">{{ $completed }}/{{ $total }}</span> pasos completados
            <span class="text-gray-400">({{ $percent }}%)</span>
        </div>
    @endif
</div>