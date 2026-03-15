<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Performance optimization: Add missing indexes for frequently queried columns
     */
    public function up(): void
    {
        // Attendance Records - Add timestamp-based indexes for common queries
        Schema::table('attendance_records', function (Blueprint $table) {
            // Composite indexes for student/session timeline queries
            $table->index(['student_id', 'created_at'], 'attendance_records_student_created_at_index');
            $table->index(['session_id', 'created_at'], 'attendance_records_session_created_at_index');
            // Index for created_at queries (used in stats)
            $table->index('created_at', 'attendance_records_created_at_index');
        });

        // Biometric Scans - Add indexes for common query patterns
        Schema::table('biometric_scans', function (Blueprint $table) {
            $table->index(['student_id', 'created_at'], 'biometric_scans_student_created_index');
            $table->index(['session_id', 'created_at'], 'biometric_scans_session_created_index');
            $table->index(['scan_type', 'status'], 'biometric_scans_type_status_index');
        });

        // Sessions - Add composite index for academic year ordering queries
        Schema::table('sessions', function (Blueprint $table) {
            $table->index(['academic_year_id', 'order'], 'sessions_academic_year_order_index');
        });

        // Attendance Follow Ups - Add indexes
        Schema::table('attendance_follow_ups', function (Blueprint $table) {
            $table->index('attendance_record_id', 'attendance_follow_ups_record_index');
            $table->index(['attendance_record_id', 'created_at'], 'attendance_follow_ups_record_created_index');
        });

        // Absence Notifications - Add indexes
        Schema::table('absence_notifications', function (Blueprint $table) {
            $table->index('student_id', 'absence_notifications_student_index');
            $table->index('session_id', 'absence_notifications_session_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropIndex('attendance_records_student_created_at_index');
            $table->dropIndex('attendance_records_session_created_at_index');
            $table->dropIndex('attendance_records_created_at_index');
        });

        Schema::table('biometric_scans', function (Blueprint $table) {
            $table->dropIndex('biometric_scans_student_created_index');
            $table->dropIndex('biometric_scans_session_created_index');
            $table->dropIndex('biometric_scans_type_status_index');
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->dropIndex('sessions_academic_year_order_index');
        });

        Schema::table('attendance_follow_ups', function (Blueprint $table) {
            $table->dropIndex('attendance_follow_ups_record_index');
            $table->dropIndex('attendance_follow_ups_record_created_index');
        });

        Schema::table('absence_notifications', function (Blueprint $table) {
            $table->dropIndex('absence_notifications_student_index');
            $table->dropIndex('absence_notifications_session_index');
        });
    }
};
