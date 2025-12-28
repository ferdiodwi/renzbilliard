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
        Schema::create('sessions_billiard', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('tables_billiard')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('rate_id')->constrained('rates')->onDelete('restrict')->onUpdate('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('duration_minutes');
            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['playing', 'finished'])->default('playing');
            $table->boolean('auto_stop')->default(true);
            $table->timestamps();

            $table->index(['status', 'end_time'], 'idx_sessions_status_endtime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions_billiard');
    }
};
