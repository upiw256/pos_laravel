<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('cash_tendered', 14, 2)->nullable()->after('payment_method')->comment('Uang yang diberikan pelanggan (untuk tunai)');
            $table->decimal('change_amount', 14, 2)->nullable()->after('cash_tendered')->comment('Kembalian');
            $table->string('payment_detail')->nullable()->after('change_amount')->comment('Nama bank / nama QRIS provider');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['cash_tendered', 'change_amount', 'payment_detail']);
        });
    }
};
