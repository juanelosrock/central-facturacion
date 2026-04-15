<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyCertificate extends Model
{
    use HasFactory;

    protected $table = 'company_certificates';

    protected $fillable = [
        'company_id',
        'api_certificate_id',
        'certificate',
        'password',
        'name',
        'expiration_date',
        'api_response',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'api_response' => 'array',
            'last_synced_at' => 'datetime',
            'expiration_date' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function toApiPayload(): array
    {
        return [
            'certificate' => $this->certificate,
            'password' => $this->password,
        ];
    }
}