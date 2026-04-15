<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // Identificación (se envía en la URL, no en el body)
            $table->string('identification_number')->unique();
            $table->string('dv', 2);

            // Campos del body del POST
            $table->unsignedInteger('type_document_identification_id');
            $table->unsignedInteger('type_organization_id');
            $table->unsignedInteger('type_regime_id');
            $table->unsignedInteger('type_liability_id');
            $table->string('business_name');     
			$table->string('merchant_registration')->nullable();
            $table->unsignedInteger('municipality_id');			
            $table->string('address');
            $table->string('phone')->nullable();
            $table->string('email');

            // Credenciales de correo
            $table->string('mail_host')->nullable();
            $table->string('mail_port')->nullable();
            $table->string('mail_username')->nullable();
            $table->string('mail_password')->nullable();
            $table->string('mail_encryption')->nullable();

            // Respuesta del API
            $table->unsignedBigInteger('api_company_id')->nullable();
            $table->text('api_token')->nullable();
            $table->text('api_password')->nullable();
            $table->json('api_response')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};