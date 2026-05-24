<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds PostgreSQL GIN full-text index on name + sku + barcode columns
     * for fast, Elasticsearch-like product search in POS without external deps.
     */
    public function up(): void
    {
        // B-Tree indexes on sku & barcode for exact/prefix lookups
        Schema::table('products', function (Blueprint $table) {
            $table->index('sku',     'products_sku_idx');
            $table->index('barcode', 'products_barcode_idx');
            $table->index('status',  'products_status_idx');
            $table->index(['category_id', 'status'], 'products_category_status_idx');
        });

        // GIN index for PostgreSQL full-text search (tsvector) on name
        // This enables O(log N) full-text search – similar to Elasticsearch
        DB::statement("
            CREATE INDEX IF NOT EXISTS products_fts_idx
            ON products
            USING GIN (to_tsvector('simple', coalesce(name, '') || ' ' || coalesce(sku, '') || ' ' || coalesce(barcode, '')))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS products_fts_idx');

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_sku_idx');
            $table->dropIndex('products_barcode_idx');
            $table->dropIndex('products_status_idx');
            $table->dropIndex('products_category_status_idx');
        });
    }
};
