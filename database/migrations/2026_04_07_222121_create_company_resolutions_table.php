<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('company_resolutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // Marca si es la resolución de pruebas (habilitación) o de producción
            $table->boolean('is_habilitation')->default(false);

            // ID interno en Qimera
            $table->unsignedBigInteger('api_resolution_id')->nullable();

            // Campos del payload
            $table->unsignedInteger('type_document_id');
            $table->string('prefix')->nullable();
            $table->string('resolution');
            $table->date('resolution_date');
            $table->string('technical_key')->nullable();
            $table->unsignedBigInteger('from');
            $table->unsignedBigInteger('to');
            $table->unsignedBigInteger('generated_to_date')->default(0);
            $table->date('date_from');
            $table->date('date_to');

            // Respuesta del API
            $table->json('api_response')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_resolutions');
    }
};