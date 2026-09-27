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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status')->default('pending')->index();
            $table->string('payment_reference')->nullable()->unique();
            $table->string('payment_method')->default('paystack');
            $table->timestamp('paid_at')->nullable();
        });

        DB::table('orders')->where('status', '!=', 'awaiting_payment')->update([
            'payment_status' => 'paid',
            'paid_at' => DB::raw('created_at'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['payment_reference']);
            $table->dropIndex(['payment_status']);
            $table->dropColumn(['payment_status', 'payment_reference', 'payment_method', 'paid_at']);
        });
    }
};
