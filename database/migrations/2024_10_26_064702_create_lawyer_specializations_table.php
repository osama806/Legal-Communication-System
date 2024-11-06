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
        Schema::create('lawyer_specializations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lawyer_id')->constrained('lawyers')->cascadeOnDelete();
            $table->foreignId('specialization_id')->constrained('specializations')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lawyer_specializations');
    }
};
