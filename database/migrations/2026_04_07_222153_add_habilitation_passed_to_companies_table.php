<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('habilitation_passed')->default(false)->after('api_password');
            $table->timestamp('habilitation_passed_at')->nullable()->after('habilitation_passed');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['habilitation_passed', 'habilitation_passed_at']);
        });
    }
};