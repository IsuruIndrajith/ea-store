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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
$table->string('name');
$table->string('slug')->unique();
$table->string('platform'); // MT4 or MT5
$table->text('description')->nullable();
$table->unsignedInteger('price_cents');
$table->string('version')->default('1.0');
$table->string('file_path')->nullable(); // private disk path to the .ex4/.ex5
$table->boolean('is_active')->default(true);
$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
