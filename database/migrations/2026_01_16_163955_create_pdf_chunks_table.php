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
        Schema::create('pdf_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pdf_file_id')->constrained('pdf_files')->cascadeOnDelete();
            $table->text('content');
            $table->json('embedding');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdf_chunks');
    }
};
