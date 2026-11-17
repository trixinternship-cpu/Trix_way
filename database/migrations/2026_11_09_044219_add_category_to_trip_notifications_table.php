<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_notifications', function (Blueprint $table) {
            $table->string('category', 40)
                ->default('system')
                ->after('message');

            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::table('trip_notifications', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn('category');
        });
    }
};