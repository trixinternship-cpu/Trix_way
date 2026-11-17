
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_stops', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trip_id')
                ->constrained('trips')
                ->cascadeOnDelete();

            // Urutan tujuan dalam perjalanan
            $table->unsignedInteger('sequence');

            $table->enum('stop_type', [
                'destination',
                'base',
            ])->default('destination');

            $table->string('destination');

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Waktu kedatangan dan keberangkatan
            $table->timestamp('arrival_time')->nullable();
            $table->timestamp('departure_time')->nullable();

            // Lokasi GPS saat tiba
            $table->decimal('arrival_latitude', 10, 7)->nullable();
            $table->decimal('arrival_longitude', 10, 7)->nullable();

            $table->enum('status', [
                'pending',
                'arrived',
                'departed',
            ])->default('pending');

            $table->foreignId('added_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('added_at')->nullable();

            $table->timestamps();

            $table->unique(['trip_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_stops');
    }
};
