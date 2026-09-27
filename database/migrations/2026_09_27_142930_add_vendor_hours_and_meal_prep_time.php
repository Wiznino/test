<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('accepting_orders')->default(true);
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
        });

        Schema::table('foods', function (Blueprint $table) {
            $table->unsignedSmallInteger('preparation_minutes')->default(15);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            $table->dropColumn('preparation_minutes');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['accepting_orders', 'opening_time', 'closing_time']);
        });
    }
};
