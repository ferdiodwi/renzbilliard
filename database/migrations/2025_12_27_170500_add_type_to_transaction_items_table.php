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
        Schema::table('transaction_items', function (Blueprint $table) {
            // Add type column to distinguish between session and product items
            $table->enum('type', ['session', 'product'])->after('transaction_id');
            
            // Make session_id nullable since product items won't have session_id
            $table->foreignId('session_id')->nullable()->change();
            
            // Add product_id for F&B items
            $table->foreignId('product_id')->nullable()->after('session_id')->constrained('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });
    }
};
