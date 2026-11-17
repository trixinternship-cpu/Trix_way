
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id')->unique();
            $table->string('phone', 20)->nullable();

            $table->enum('role', ['hr', 'driver'])
                ->default('driver');

            $table->boolean('is_active')->default(true);

            $table->boolean('must_change_password')
                ->default(true);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropUnique(['employee_id']);

            $table->dropColumn([
                'employee_id',
                'phone',
                'role',
                'is_active',
                'must_change_password',
                'created_by',
            ]);
        });
    }
};
