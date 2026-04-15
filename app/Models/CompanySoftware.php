<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanySoftware extends Model
{
    use HasFactory;
	
	protected $table = 'company_softwares';

    protected $fillable = [
        'company_id',
        'api_software_id',
        'identifier',
        'pin',
        'identifier_payroll',
        'pin_payroll',
        'identifier_eqdocs',
        'pin_eqdocs',
        'url',
        'url_payroll',
        'url_eqdocs',
        'api_response',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'api_response' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Payload para el PUT /ubl2.1/config/software
     */
    public function toApiPayload(): array
    {
        return [
            'id' => $this->identifier,
            'pin' => (int) $this->pin,
        ];
    }
}