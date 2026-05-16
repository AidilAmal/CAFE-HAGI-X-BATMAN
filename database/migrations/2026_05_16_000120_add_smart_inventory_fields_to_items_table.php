<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (! Schema::hasColumn('items', 'barcode')) {
                $table->string('barcode')->nullable()->unique()->after('code');
            }

            if (! Schema::hasColumn('items', 'expired_at')) {
                $table->date('expired_at')->nullable()->after('min_stock');
            }
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'barcode')) {
                $table->dropUnique('items_barcode_unique');
                $table->dropColumn('barcode');
            }

            if (Schema::hasColumn('items', 'expired_at')) {
                $table->dropColumn('expired_at');
            }
        });
    }
};
