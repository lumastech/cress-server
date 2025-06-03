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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ref_id')->comment('Reference ID to related model');
            $table->string('file_path'); // Changed from 'file' to be more descriptive
            $table->string('type')->comment('File type/mime type');
            $table->string('status')->default('active');
            $table->timestamps();

            // Indexes
            $table->index('ref_id');
            $table->index('type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
