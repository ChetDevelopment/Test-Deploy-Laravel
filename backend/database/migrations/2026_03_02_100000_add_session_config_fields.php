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
        Schema::table('sessions', function (Blueprint $table) {
            $table->integer('late_threshold')->default(15)->after('order'); // minutes after start_time considered late
            $table->boolean('is_active')->default(true)->after('late_threshold'); // enable/disable session
            $table->string('description')->nullable()->after('is_active'); // optional description
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropColumn(['late_threshold', 'is_active', 'description']);
        });
    }
};
