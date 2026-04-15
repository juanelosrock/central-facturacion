<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('company_softwares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            // ID interno del software en el API de Qimera
            $table->unsignedBigInteger('api_software_id')->nullable();

            // Campos principales (los que se envían en el PUT)
            $table->string('identifier'); // UUID DIAN
            $table->string('pin');

            // Campos de nómina y documentos equivalentes (vienen vacíos por defecto)
            $table->string('identifier_payroll')->nullable();
            $table->string('pin_payroll')->nullable();
            $table->string('identifier_eqdocs')->nullable();
            $table->string('pin_eqdocs')->nullable();

            // URLs DIAN devueltas
            $table->string('url')->nullable();
            $table->string('url_payroll')->nullable();
            $table->string('url_eqdocs')->nullable();

            // Respuesta cruda y timestamps de sync
            $table->json('api_response')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();

            $table->unique('company_id'); // 1:1
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_softwares');
    }
};