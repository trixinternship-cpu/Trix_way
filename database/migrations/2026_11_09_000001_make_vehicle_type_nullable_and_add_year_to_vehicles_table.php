<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['vehicle_type_id']);
            $table->unsignedBigInteger('vehicle_type_id')->nullable()->change();
            $table->foreign('vehicle_type_id')->references('id')->on('vehicle_types')->restrictOnDelete();

            if (! Schema::hasColumn('vehicles', 'year')) {
                $table->unsignedSmallInteger('year')->nullable()->after('model');
            }
        });
    }

    public function down(): void
    {
        if (DB::table('vehicles')->whereNull('vehicle_type_id')->exists()) {
            throw new RuntimeException('Rollback dibatalkan: terdapat kendaraan dengan vehicle_type_id NULL. Isi kategorinya terlebih dahulu.');
        }

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['vehicle_type_id']);
            $table->unsignedBigInteger('vehicle_type_id')->nullable(false)->change();
            $table->foreign('vehicle_type_id')->references('id')->on('vehicle_types')->restrictOnDelete();
            if (Schema::hasColumn('vehicles', 'year')) {
                $table->dropColumn('year');
            }
        });
    }
};
