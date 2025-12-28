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
        Schema::create('tables_billiard', function (Blueprint $table) {
            $table->id();
            $table->string('table_number', 10)->unique();
            $table->enum('status', ['available', 'playing', 'maintenance'])->default('available');
            $table->timestamps();

            $table->index('status', 'idx_tables_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables_billiard');
    }
};
