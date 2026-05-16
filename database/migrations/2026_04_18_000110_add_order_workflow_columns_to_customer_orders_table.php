<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_orders', function (Blueprint $table) {
            $table->timestamp('processing_at')->nullable()->after('ordered_at');
            $table->timestamp('completed_at')->nullable()->after('processing_at');
            $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            $table->timestamp('stock_applied_at')->nullable()->after('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('customer_orders', function (Blueprint $table) {
            $table->dropColumn(['processing_at', 'completed_at', 'cancelled_at', 'stock_applied_at']);
        });
    }
};
