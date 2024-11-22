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
        Schema::create('agencies', function (Blueprint $table) {
            $table->id();
            $table->string('sequential_number')->nullable()->unique();
            $table->string('record_number')->nullable()->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('lawyer_id')->nullable()->constrained('lawyers')->cascadeOnDelete();
            $table->foreignId('representative_id')->nullable()->constrained('representatives')->cascadeOnDelete();
            $table->string('place_of_issue')->nullable();
            $table->string('type')->nullable();
            $table->text('authorizations')->nullable();
            $table->text('exceptions')->nullable();
            $table->string('cause');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agencies');
    }
};
