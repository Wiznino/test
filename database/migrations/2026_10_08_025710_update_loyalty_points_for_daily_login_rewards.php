<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->decimal('loyalty_points', 10, 1)->default(0)->change();
        });

        Schema::table('loyalty_transactions', function (Blueprint $table): void {
            $table->decimal('points', 10, 1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('users')->whereRaw('loyalty_points != ROUND(loyalty_points, 0)')->exists()
            || DB::table('loyalty_transactions')->whereRaw('points != ROUND(points, 0)')->exists()) {
            throw new RuntimeException('Daily sign-in rewards have fractional points and cannot be safely rolled back.');
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->integer('loyalty_points')->default(0)->change();
        });

        Schema::table('loyalty_transactions', function (Blueprint $table): void {
            $table->unsignedInteger('points')->change();
        });
    }
};
