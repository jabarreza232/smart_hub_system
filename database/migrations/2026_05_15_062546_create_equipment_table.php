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
        $table->string('name');
        $table->enum('type', ['workspace', 'studio_gear']);
        $table->integer('stock')->default(1);
        $table->enum('status', ['available', 'maintenance', 'in_use'])->default('available');
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
