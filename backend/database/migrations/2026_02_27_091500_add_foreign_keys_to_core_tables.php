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
        Schema::table('classes', function (Blueprint $table) {
            $table->foreign('academic_year_id')
                ->references('id')
                ->on('academic_years')
                ->onDelete('set null');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->foreign('class_id')
                ->references('id')
                ->on('classes')
                ->onDelete('set null');

            $table->foreign('academic_year_id')
                ->references('id')
                ->on('academic_years')
                ->onDelete('set null');
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->foreign('academic_year_id')
                ->references('id')
                ->on('academic_years')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropForeign(['academic_year_id']);
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
        });
    }
};
