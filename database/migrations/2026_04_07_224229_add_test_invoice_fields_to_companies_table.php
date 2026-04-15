<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->json('test_invoice_response')->nullable()->after('habilitation_passed_at');
            $table->timestamp('test_invoice_sent_at')->nullable()->after('test_invoice_response');
            $table->boolean('test_invoice_success')->default(false)->after('test_invoice_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['test_invoice_response', 'test_invoice_sent_at', 'test_invoice_success']);
        });
    }
};