
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trip_id')
                ->constrained('trips')
                ->cascadeOnDelete();

            // Pengguna atau sistem yang melakukan aktivitas
            $table->foreignId('actor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('event_type');
            $table->text('description')->nullable();

            // Informasi tambahan aktivitas
            $table->json('metadata')->nullable();

            $table->timestamp('event_time');

            $table->timestamps();

            $table->index(['trip_id', 'event_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_events');
    }
};
