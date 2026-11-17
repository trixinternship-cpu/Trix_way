
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_tracking_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trip_id')
                ->unique()
                ->constrained('trips')
                ->cascadeOnDelete();

            $table->enum('status', [
                'active',
                'stopped',
            ])->default('active');

            // Waktu sesi GPS
            $table->timestamp('started_at')->nullable();
            $table->timestamp('stopped_at')->nullable();

            // Lokasi GPS terakhir
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->decimal('accuracy', 8, 2)->nullable();
            $table->decimal('speed', 8, 2)->nullable();
            $table->decimal('heading', 6, 2)->nullable();

            // Waktu GPS direkam dan diterima server
            $table->timestamp('last_recorded_at')->nullable();
            $table->timestamp('last_received_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_tracking_sessions');
    }
};
