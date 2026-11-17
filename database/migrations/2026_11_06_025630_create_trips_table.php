
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();

            $table->string('trip_code')->unique();

            $table->foreignId('driver_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('vehicle_id')
                ->constrained('vehicles')
                ->restrictOnDelete();

            $table->text('purpose');

            // Titik awal perjalanan
            $table->string('origin')->default('PT Trix Indonesia');
            $table->decimal('origin_latitude', 10, 7)->nullable();
            $table->decimal('origin_longitude', 10, 7)->nullable();

            // Waktu perjalanan
            $table->timestamp('planned_start')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();

            // Lokasi saat perjalanan berakhir
            $table->decimal('end_latitude', 10, 7)->nullable();
            $table->decimal('end_longitude', 10, 7)->nullable();

            $table->enum('status', [
                'active',
                'completed',
                'cancelled',
            ])->default('active');

            $table->timestamps();

            $table->index(['driver_id', 'status']);
            $table->index(['vehicle_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
