<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'identification_number',
        'dv',
        'type_document_identification_id',
        'type_organization_id',
        'type_regime_id',
        'type_liability_id',
        'business_name',
        'merchant_registration',
        'municipality_id',
        'address',
        'phone',
        'email',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'api_company_id',
        'api_token',
        'api_password',
        'api_response',
        'last_synced_at',
		'habilitation_passed',
		'habilitation_passed_at',
		'test_invoice_response',
		'test_invoice_sent_at',
		'test_invoice_success',
    ];

    protected function casts(): array
    {
        return [
            'api_response' => 'array',
            'last_synced_at' => 'datetime',
			'habilitation_passed' => 'boolean',
			'habilitation_passed_at' => 'datetime',
			'test_invoice_response' => 'array',
			'test_invoice_sent_at' => 'datetime',
			'test_invoice_success' => 'boolean',
        ];
    }

    /**
     * Devuelve solo los campos que van en el body del POST al API.
     */
    public function toApiPayload(): array
    {
        return [
            'type_document_identification_id' => (int) $this->type_document_identification_id,
            'type_organization_id' => (int) $this->type_organization_id,
            'type_regime_id' => (int) $this->type_regime_id,
            'type_liability_id' => (int) $this->type_liability_id,
            'business_name' => $this->business_name,
            'merchant_registration' => $this->merchant_registration,
            'municipality_id' => (int) $this->municipality_id,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'mail_host' => $this->mail_host,
            'mail_port' => $this->mail_port,
            'mail_username' => $this->mail_username,
            'mail_password' => $this->mail_password,
            'mail_encryption' => $this->mail_encryption,
        ];
    }
	
	public function software(): HasOne
	{
		return $this->hasOne(CompanySoftware::class);
	}
	
	public function certificate(): HasOne
	{
		return $this->hasOne(CompanyCertificate::class);
	}
	
	public function resolutions(): HasMany
	{
		return $this->hasMany(CompanyResolution::class);
	}

	public function habilitationResolution()
	{
		return $this->hasOne(CompanyResolution::class)->where('is_habilitation', true);
	}

	public function productionResolutions(): HasMany
	{
		return $this->hasMany(CompanyResolution::class)->where('is_habilitation', false);
	}
}