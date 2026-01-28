<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add customer_id (nullable to avoid migration failure on existing rows)
            if (!Schema::hasColumn('orders', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('id');
            }

            // Add address reference
            if (!Schema::hasColumn('orders', 'address_id')) {
                $table->foreignId('address_id')->nullable()->after('customer_id');
            }

            // Payment columns
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status')->default('pending')->after('status');
            }

            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('payment_status');
            }

            if (!Schema::hasColumn('orders', 'razorpay_order_id')) {
                $table->string('razorpay_order_id')->nullable()->after('payment_method');
            }

            if (!Schema::hasColumn('orders', 'razorpay_payment_id')) {
                $table->string('razorpay_payment_id')->nullable()->after('razorpay_order_id');
            }

            if (!Schema::hasColumn('orders', 'payment_error')) {
                $table->text('payment_error')->nullable()->after('razorpay_payment_id');
            }

            if (!Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_error');
            }
        });

        // Try to backfill customer_id from legacy user_id column if present
        if (Schema::hasColumn('orders', 'user_id') && Schema::hasColumn('orders', 'customer_id')) {
            DB::table('orders')->whereNull('customer_id')->update([
                'customer_id' => DB::raw('user_id')
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
            if (Schema::hasColumn('orders', 'payment_error')) {
                $table->dropColumn('payment_error');
            }
            if (Schema::hasColumn('orders', 'razorpay_payment_id')) {
                $table->dropColumn('razorpay_payment_id');
            }
            if (Schema::hasColumn('orders', 'razorpay_order_id')) {
                $table->dropColumn('razorpay_order_id');
            }
            if (Schema::hasColumn('orders', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (Schema::hasColumn('orders', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
            if (Schema::hasColumn('orders', 'address_id')) {
                $table->dropColumn('address_id');
            }
            if (Schema::hasColumn('orders', 'customer_id')) {
                $table->dropColumn('customer_id');
            }
        });
    }
};
