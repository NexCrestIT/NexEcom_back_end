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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('address_id')->nullable()->after('customer_id')->constrained('addresses')->onDelete('set null');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending')->after('status');
            $table->string('payment_method')->nullable()->after('payment_status');
            $table->string('razorpay_order_id')->nullable()->after('payment_method');
            $table->string('razorpay_payment_id')->nullable()->after('razorpay_order_id');
            $table->text('payment_error')->nullable()->after('razorpay_payment_id');
            $table->timestamp('paid_at')->nullable()->after('payment_error');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['address_id']);
            $table->dropColumn([
                'address_id',
                'payment_status',
                'payment_method',
                'razorpay_order_id',
                'razorpay_payment_id',
                'payment_error',
                'paid_at',
            ]);
        });
    }
};
