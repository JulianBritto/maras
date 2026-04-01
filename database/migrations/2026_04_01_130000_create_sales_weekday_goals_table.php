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
        Schema::create('sales_weekday_goals', function (Blueprint $table) {
            $table->id();
            // 1 = lunes, 7 = domingo (ISO-8601)
            $table->unsignedTinyInteger('weekday')->unique();
            $table->unsignedBigInteger('amount')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_weekday_goals');
    }
};
