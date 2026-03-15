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
            // Add absence status (excused/unexcused/pending) - separate from notification status
            $table->string('absence_status')->default('PENDING')->after('absence_reason');
            
            // Add comment field for admin/education team notes
            $table->text('comment')->nullable()->after('absence_status');
            
            // Add follow-up notes field
            $table->text('follow_up_notes')->nullable()->after('comment');
            
            // Add timestamps for when status was updated
            $table->timestamp('status_updated_at')->nullable()->after('follow_up_notes');
            $table->unsignedBigInteger('status_updated_by')->nullable()->after('status_updated_at');
            
            // Add foreign key for status_updated_by
            $table->foreign('status_updated_by')->references('id')->on('users')->onDelete('set null');
            
            // Add indexes for new fields
            $table->index('absence_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absence_notifications', function (Blueprint $table) {
            $table->dropForeign(['status_updated_by']);
            $table->dropColumn([
                'absence_status',
                'comment',
                'follow_up_notes',
                'status_updated_at',
                'status_updated_by',
            ]);
        });
    }
};
