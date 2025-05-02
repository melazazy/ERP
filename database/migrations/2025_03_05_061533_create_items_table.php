<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('items', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique(); // Make name unique
        $table->string('code')->unique(); // Add code column and make it unique
        $table->text('description')->nullable();
        $table->foreignId('subcategory_id')->constrained()->onDelete('cascade');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
