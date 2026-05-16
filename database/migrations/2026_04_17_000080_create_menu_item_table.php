<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('qty_required')->default(1);
            $table->timestamps();
            $table->unique(['menu_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_item');
    }
};
