<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->default('default')->index();
            $table->text('description');
            $table->nullableMorphs('subject'); // Polymorphic relation to the affected model
            $table->nullableMorphs('causer');  // Polymorphic relation to the user who caused the activity
            $table->json('properties')->nullable();
            $table->string('event')->nullable();
            $table->string('batch_uuid')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['created_at', 'log_name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_logs');
    }
};
