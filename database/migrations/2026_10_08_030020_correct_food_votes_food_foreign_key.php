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
        Schema::table('food_votes', function (Blueprint $table): void {
            $table->dropForeign(['food_id']);
        });

        Schema::table('food_votes', function (Blueprint $table): void {
            $table->foreign('food_id', 'food_votes_food_id_foreign')
                ->references('id')
                ->on('foods')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_votes', function (Blueprint $table): void {
            $table->dropForeign(['food_id']);
        });

        Schema::table('food_votes', function (Blueprint $table): void {
            $table->foreign('food_id', 'food_votes_food_id_foreign')
                ->references('id')
                ->on('food')
                ->cascadeOnDelete();
        });
    }
};
