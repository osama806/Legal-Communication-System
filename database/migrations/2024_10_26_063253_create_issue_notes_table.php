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
        Schema::create('issue_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lawyer_id')->constrained('lawyers')->cascadeOnDelete();
            $table->foreignId('issue_id')->constrained('issues', 'id')->cascadeOnDelete();
            $table->text('notes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_notes');
    }
};
