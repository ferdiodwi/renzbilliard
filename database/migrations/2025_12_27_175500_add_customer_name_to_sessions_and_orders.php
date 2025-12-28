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
        Schema::table('sessions_billiard', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('table_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions_billiard', function (Blueprint $table) {
            $table->dropColumn('customer_name');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('customer_name');
        });
    }
};
