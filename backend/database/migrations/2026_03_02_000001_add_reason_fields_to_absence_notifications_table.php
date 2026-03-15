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
        Schema::table('absence_notifications', function (Blueprint $table) {
            $table->text('absence_reason')->nullable()->after('error_message');
            $table->unsignedBigInteger('reason_submitted_by')->nullable()->after('absence_reason');
            $table->timestamp('reason_submitted_at')->nullable()->after('reason_submitted_by');
            
            $table->foreign('reason_submitted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absence_notifications', function (Blueprint $table) {
            $table->dropForeign(['reason_submitted_by']);
            $table->dropColumn(['absence_reason', 'reason_submitted_by', 'reason_submitted_at']);
        });
    }
};
