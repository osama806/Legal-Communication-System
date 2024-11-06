<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->string('base_number')->unique();
            $table->string('record_number')->unique();
            $table->foreignId('lawyer_id')->constrained('lawyers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->string('court_name');
            $table->string('type');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status');
            $table->integer('estimated_cost');
            $table->boolean('is_active')->default(true);
            $table->float('success_rate')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
