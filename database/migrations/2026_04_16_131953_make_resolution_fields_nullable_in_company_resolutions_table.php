<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('company_resolutions', function (Blueprint $table) {
            $table->string('resolution')->nullable()->change();
            $table->date('resolution_date')->nullable()->change();
            $table->date('date_from')->nullable()->change();
            $table->date('date_to')->nullable()->change();
            $table->unsignedBigInteger('generated_to_date')->nullable()->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('company_resolutions', function (Blueprint $table) {
            $table->string('resolution')->nullable(false)->change();
            $table->date('resolution_date')->nullable(false)->change();
            $table->date('date_from')->nullable(false)->change();
            $table->date('date_to')->nullable(false)->change();
            $table->unsignedBigInteger('generated_to_date')->nullable(false)->default(0)->change();
        });
    }
};
