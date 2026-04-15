<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyResolution extends Model
{
    use HasFactory;

    protected $table = 'company_resolutions';

    protected $fillable = [
        'company_id',
        'is_habilitation',
        'api_resolution_id',
        'type_document_id',
        'prefix',
        'resolution',
        'resolution_date',
        'technical_key',
        'from',
        'to',
        'generated_to_date',
        'date_from',
        'date_to',
        'api_response',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'is_habilitation' => 'boolean',
            'api_response' => 'array',
            'last_synced_at' => 'datetime',
            'resolution_date' => 'date',
            'date_from' => 'date',
            'date_to' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function toApiPayload(): array
    {
        return [
            'type_document_id' => (int) $this->type_document_id,
            'prefix' => $this->prefix,
            'resolution' => $this->resolution,
            'resolution_date' => $this->resolution_date?->format('Y-m-d'),
            'technical_key' => $this->technical_key,
            'from' => (int) $this->from,
            'to' => (int) $this->to,
            'generated_to_date' => (int) $this->generated_to_date,
            'date_from' => $this->date_from?->format('Y-m-d'),
            'date_to' => $this->date_to?->format('Y-m-d'),
        ];
    }

    /**
     * Payload fijo para la resolución de habilitación (DIAN ambiente de pruebas).
     */
    public static function habilitationPayload(): array
    {
        return [
            'type_document_id' => 1,
            'prefix' => 'SETP',
            'resolution' => '18760000001',
            'resolution_date' => '2019-01-19',
            'technical_key' => 'fc8eac422eba16e22ffd8c6f94b3f40a6e38162c',
            'from' => 990000000,
            'to' => 995000000,
            'generated_to_date' => 0,
            'date_from' => '2019-01-19',
            'date_to' => '2030-01-19',
        ];
    }
	
	/**
	 * Payload fijo de la factura de prueba para habilitación DIAN.
	 * La fecha y hora se generan al momento del envío.
	 */
	public static function habilitationTestInvoicePayload(\App\Models\Company $company): array
	{
		return [
			'number' => 990000101,
			'type_document_id' => 1,
			'date' => now()->format('Y-m-d'),
			'time' => now()->format('H:i:s'),
			'resolution_number' => '18760000001',
			'prefix' => 'SETP',
			'notes' => 'ESTA ES UNA NOTA DE PRUEBA, ESTA ES UNA NOTA DE PRUEBA, ESTA ES UNA NOTA DE PRUEBA, ESTA ES UNA NOTA DE PRUEBA, ESTA ES UNA NOTA DE PRUEBA, ESTA ES UNA NOTA DE PRUEBA, ESTA ES UNA NOTA DE PRUEBA, ESTA ES UNA NOTA DE PRUEBA, ESTA ES UNA NOTA DE PRUEBA, ESTA ES UNA NOTA DE PRUEBA, ESTA ES UNA NOTA DE PRUEBA, ESTA ES UNA NOTA DE PRUEBA, ESTA ES UNA NOTA DE PRUEBA, ESTA ES UNA NOTA DE PRUEBA',
			'disable_confirmation_text' => true,
			'establishment_name' => $company->business_name,
			'establishment_address' => $company->address,
			'establishment_phone' => (string) $company->phone,
			'establishment_municipality' => 600,
			'establishment_email' => $company->email,
			'sendmail' => true,
			'sendmailtome' => true,
			'send_customer_credentials' => false,
			'seze' => '2021-2017',
			'email_cc_list' => [
				['email' => 'soporte@sibco.com.co'],
				['email' => 'soporte@srwok.com'],
			],
			'head_note' => 'PRUEBA DE TEXTO LIBRE QUE DEBE POSICIONARSE EN EL ENCABEZADO DE PAGINA DE LA REPRESENTACION GRAFICA DE LA FACTURA ELECTRONICA VALIDACION PREVIA DIAN',
			'foot_note' => 'PRUEBA DE TEXTO LIBRE QUE DEBE POSICIONARSE EN EL PIE DE PAGINA DE LA REPRESENTACION GRAFICA DE LA FACTURA ELECTRONICA VALIDACION PREVIA DIAN',
			'customer' => [
				'identification_number' => 89008003,
				'dv' => 2,
				'name' => 'INVERSIONES DAVAL SAS',
				'phone' => '3103891693',
				'address' => 'CLL 4 NRO 33-90',
				'email' => 'soporte@sibco.com.co',
				'merchant_registration' => '0000000-00',
				'type_document_identification_id' => 6,
				'type_organization_id' => 1,
				'type_liability_id' => 7,
				'municipality_id' => 822,
				'type_regime_id' => 1,
			],
			'payment_form' => [
				'payment_form_id' => 1,
				'payment_method_id' => 1,
				'payment_due_date' => now()->addMonths(1)->format('Y-m-d'),
				'duration_measure' => '0',
			],
			'legal_monetary_totals' => [
				'line_extension_amount' => '840336.134',
				'tax_exclusive_amount' => '840336.134',
				'tax_inclusive_amount' => '1000000.00',
				'payable_amount' => '1000000.00',
			],
			'tax_totals' => [
				[
					'tax_id' => 1,
					'tax_amount' => '159663.865',
					'percent' => '19.00',
					'taxable_amount' => '840336.134',
				],
			],
			'invoice_lines' => [
				[
					'unit_measure_id' => 70,
					'invoiced_quantity' => '1',
					'line_extension_amount' => '840336.134',
					'free_of_charge_indicator' => false,
					'tax_totals' => [
						[
							'tax_id' => 1,
							'tax_amount' => '159663.865',
							'taxable_amount' => '840336.134',
							'percent' => '19.00',
						],
					],
					'description' => 'COMISION POR SERVICIOS',
					'notes' => 'ESTA ES UNA PRUEBA DE NOTA DE DETALLE DE LINEA.',
					'code' => 'COMISION',
					'type_item_identification_id' => 4,
					'price_amount' => '1000000.00',
					'base_quantity' => '1',
				],
			],
		];
	}
}