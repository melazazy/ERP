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
        Schema::table('roles', function (Blueprint $table) {
            $table->string('display_name')->after('name');
            $table->text('description')->nullable()->after('display_name');
            $table->integer('level')->nullable()->after('description');
            $table->boolean('is_active')->default(true)->after('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['display_name', 'description', 'level', 'is_active']);
        });
    }
}; 