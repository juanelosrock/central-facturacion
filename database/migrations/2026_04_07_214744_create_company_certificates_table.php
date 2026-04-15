<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('company_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // ID interno del certificado en Qimera
            $table->unsignedBigInteger('api_certificate_id')->nullable();

            // Datos que enviamos al API
            $table->longText('certificate'); // base64 del .p12
            $table->string('password');

            // Datos que devuelve el API
            $table->string('name')->nullable();
            $table->timestamp('expiration_date')->nullable();

            // Respuesta cruda y timestamps
            $table->json('api_response')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();

            $table->unique('company_id'); // 1:1
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_certificates');
    }
};