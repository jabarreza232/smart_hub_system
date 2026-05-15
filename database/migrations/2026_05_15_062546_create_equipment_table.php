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
        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique(); // Stock Keeping Unit / Barcode
            $table->string('name');
            $table->enum('type', ['workspace', 'studio_gear']);
            $table->integer('stock')->default(1);
            $table->string('location')->nullable(); // Contoh: "Rak 2, Loker B"
            $table->text('condition_notes')->nullable(); // Catatan teknis alat

            // Penambahan status 'retired'
            $table->enum('status', ['available', 'maintenance', 'in_use', 'retired'])->default('available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
