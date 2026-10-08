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
        Schema::table('orders', function (Blueprint $table): void {
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cancellation_reason')->nullable();
            $table->string('refund_status')->default('none')->index();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_reference')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->foreignId('refunded_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['cancelled_by']);
            $table->dropForeign(['refunded_by']);
            $table->dropIndex(['refund_status']);
            $table->dropColumn([
                'cancelled_at',
                'cancelled_by',
                'cancellation_reason',
                'refund_status',
                'refund_amount',
                'refund_reference',
                'refunded_at',
                'refunded_by',
            ]);
        });
    }
};
