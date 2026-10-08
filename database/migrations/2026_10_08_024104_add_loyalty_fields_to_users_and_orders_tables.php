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
            $table->integer('loyalty_points')->default(0);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->unsignedInteger('loyalty_points_awarded')->default(0);
            $table->unsignedInteger('loyalty_points_redeemed')->default(0);
        });

        DB::table('orders')
            ->where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('payment_method', '!=', 'loyalty')
            ->orderBy('id')
            ->chunkById(100, function ($orders): void {
                foreach ($orders as $order) {
                    $points = (int) DB::table('order_items')->where('order_id', $order->id)->sum('quantity');
                    if ($points < 1) {
                        continue;
                    }

                    DB::table('orders')->where('id', $order->id)->update(['loyalty_points_awarded' => $points]);
                    DB::table('users')->where('id', $order->user_id)->increment('loyalty_points', $points);
                    DB::table('loyalty_transactions')->insert([
                        'user_id' => $order->user_id,
                        'order_id' => $order->id,
                        'type' => 'earned',
                        'points' => $points,
                        'description' => 'Points earned from order #'.$order->id,
                        'reference' => 'order-'.$order->id.'-earned',
                        'created_at' => $order->paid_at ?? $order->created_at,
                        'updated_at' => $order->paid_at ?? $order->created_at,
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['loyalty_points_awarded', 'loyalty_points_redeemed']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('loyalty_points');
        });
    }
};
