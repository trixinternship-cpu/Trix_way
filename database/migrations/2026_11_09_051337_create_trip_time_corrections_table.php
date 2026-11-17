
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_time_corrections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trip_id')
                ->constrained('trips')
                ->cascadeOnDelete();

            $table->string('field_name', 50);

            $table->dateTime('old_value')->nullable();
            $table->dateTime('new_value')->nullable();

            $table->string('reason', 500);

            $table->foreignId('changed_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();

            $table->index(['trip_id', 'field_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_time_corrections');
    }
};
