
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_locations', function (Blueprint $table) {
            $table->id();

            $table->string('company_name');
            $table->string('address')->nullable();

            // Koordinat kantor
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            // Radius geofence dalam meter
            $table->unsignedInteger('geofence_radius_meters')
                ->default(100);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_locations');
    }
};
