<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('food_votes')->orderBy('id')->chunkById(100, function ($votes): void {
            foreach ($votes as $vote) {
                DB::table('food_votes')->where('id', $vote->id)->update([
                    'week_start' => CarbonImmutable::parse($vote->week_start)->toDateString(),
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
