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
            $table->string('sequential_number')->unique();
            $table->string('record_number')->unique();
            $table->foreignId('lawyer_id')->constrained('lawyers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->foreignId('representative_id')->constrained('representatives')->cascadeOnDelete();
            $table->string('place_of_issue');
            $table->string('type');
            $table->text('authorizations');
            $table->text('exceptions');
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
