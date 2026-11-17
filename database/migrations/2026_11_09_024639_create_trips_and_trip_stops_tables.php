<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat tabel trips JIKA BELUM ADA
        if (!Schema::hasTable('trips')) {
            Schema::create('trips', function (Blueprint $table) {
                $table->id();
                $table->string('trip_code')->unique();
                $table->foreignId('driver_id')->constrained('users')->restrictOnDelete();
                $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
                $table->string('purpose');
                $table->string('origin');
                $table->date('planned_start_date')->nullable();
                $table->time('planned_start_time')->nullable();
                $table->timestamp('actual_start_time')->nullable();
                $table->timestamp('actual_end_time')->nullable();
                $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('pending');
                $table->text('notes')->nullable();
                $table->text('cancel_reason')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }

        // 2. Buat tabel trip_stops JIKA BELUM ADA
        if (!Schema::hasTable('trip_stops')) {
            Schema::create('trip_stops', function (Blueprint $table) {
                $table->id();
                $table->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();
                $table->unsignedInteger('sequence');
                $table->enum('stop_type', ['destination', 'base'])->default('destination');
                $table->string('destination');
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->timestamp('arrival_time')->nullable();
                $table->timestamp('departure_time')->nullable();
                $table->decimal('arrival_latitude', 10, 7)->nullable();
                $table->decimal('arrival_longitude', 10, 7)->nullable();
                $table->enum('status', ['pending', 'arrived', 'departed'])->default('pending');
                $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('added_at')->nullable();
                $table->timestamps();
                $table->unique(['trip_id', 'sequence']);
            });
        }
    }

    public function down(): void
    {
        // Biarkan kosong atau hapus hanya trip_stops agar aman
        Schema::dropIfExists('trip_stops');
    }
};