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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('name');
            $table->string('type'); // e.g., 'accident', 'natural_disaster', 'crime'
            $table->string('area'); // Could be neighborhood, district, or city
            $table->text('details')->nullable();
            $table->string('status')->default('reported'); // e.g., 'reported', 'investigating', 'resolved'
            $table->decimal('lat', 10, 8); // Latitude with precision
            $table->decimal('lng', 11, 8); // Longitude with precision
            $table->timestamps();

            // Indexes for better performance
            $table->index('type');
            $table->index('status');
            $table->index('area');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
