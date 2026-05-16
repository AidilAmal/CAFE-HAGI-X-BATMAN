<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique();
            $table->string('customer_name')->nullable();
            $table->unsignedInteger('total_qty')->default(1);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('status')->default('success');
            $table->timestamp('ordered_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_orders');
    }
};
