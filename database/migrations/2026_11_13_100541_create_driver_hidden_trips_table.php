<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_hidden_trips', function (Blueprint $table) {
            $table->id();

            $table->foreignId('driver_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('trip_id')
                ->constrained('trips')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['driver_id', 'trip_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_hidden_trips');
    }
};