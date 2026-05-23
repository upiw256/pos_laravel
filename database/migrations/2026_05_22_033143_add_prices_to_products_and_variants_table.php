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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->default(0)->after('is_variant');
            $table->decimal('sell_price', 12, 2)->default(0)->after('cost_price');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->default(0)->after('name');
            $table->decimal('sell_price', 12, 2)->default(0)->after('cost_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['cost_price', 'sell_price']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['cost_price', 'sell_price']);
        });
    }
};
