<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->enum('type', ['in', 'out', 'adjustment'])->comment('in=masuk, out=keluar, adjustment=opname');
            $table->integer('quantity')->comment('Positif untuk masuk, negatif untuk keluar/adjustment turun');
            $table->integer('stock_after')->comment('Saldo stok setelah movement ini');
            $table->string('reference_type')->nullable()->comment('Model class: Purchase, Sale, etc');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('ID dari purchase/sale terkait');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'variant_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
