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
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45)->nullable(); // IPv6 requires up to 45 chars
            $table->string('device_os')->nullable(); // e.g., Windows, iOS, Android
            $table->string('device_os_version')->nullable();
            $table->string('device_type')->nullable(); // e.g., mobile, desktop, tablet
            $table->string('browser')->nullable(); // e.g., Chrome, Safari, Firefox
            $table->string('browser_version')->nullable();
            $table->timestamps();

            // Indexes for analytics queries
            $table->index('ip');
            $table->index('device_os');
            $table->index('device_type');
            $table->index('browser');
            $table->index('created_at'); // For time-based analytics
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
