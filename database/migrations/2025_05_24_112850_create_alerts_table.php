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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('status')->default('pending'); // You might want a default status
            $table->text('message');
            $table->timestamp('initiated_at')->nullable(); // Default to current timestamp
            $table->decimal('lat', 10, 8)->nullable(); // Latitude with precision
            $table->decimal('lng', 11, 8)->nullable(); // Longitude with precision
            $table->decimal('accuracy', 10, 2)->nullable(); // Accuracy in meters
            $table->timestamps();

            // Add index for frequently queried columns
            $table->index('status');
            $table->index('initiated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
