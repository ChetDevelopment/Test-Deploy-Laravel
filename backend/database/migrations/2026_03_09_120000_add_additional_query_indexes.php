<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $index): bool
    {
        $results = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);

        return !empty($results);
    }

    private function addIndexIfMissing(string $table, array $columns, string $index): void
    {
        if ($this->indexExists($table, $index)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($columns, $index): void {
            $tableBlueprint->index($columns, $index);
        });
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (!$this->indexExists($table, $index)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($index): void {
            $tableBlueprint->dropIndex($index);
        });
    }

    /**
     * Add indexes for hot query paths used in dashboards and predictions.
     */
    public function up(): void
    {
        $dateColumn = Schema::hasColumn('attendance_records', 'date') ? 'date' : 'created_at';

        $this->addIndexIfMissing('attendance_records', [$dateColumn, 'status'], 'attendance_records_date_status_index');
        $this->addIndexIfMissing('attendance_records', ['student_id', $dateColumn, 'status'], 'attendance_records_student_date_status_index');
        $this->addIndexIfMissing('attendance_records', ['session_id', 'status'], 'attendance_records_session_status_index');

        $this->addIndexIfMissing('absence_notifications', ['session_id', 'status', 'notification_type'], 'absence_notifications_session_status_type_index');
        $this->addIndexIfMissing('absence_notifications', ['student_id', 'absence_status', 'created_at'], 'absence_notifications_student_absence_status_created_index');

        $this->addIndexIfMissing('biometric_scans', ['status', 'created_at'], 'biometric_scans_status_created_index');
        $this->addIndexIfMissing('biometric_scans', ['student_id', 'scan_type', 'status', 'created_at'], 'biometric_scans_student_scan_status_created_index');
    }

    /**
     * Reverse the indexes.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('attendance_records', 'attendance_records_date_status_index');
        $this->dropIndexIfExists('attendance_records', 'attendance_records_student_date_status_index');
        $this->dropIndexIfExists('attendance_records', 'attendance_records_session_status_index');

        $this->dropIndexIfExists('absence_notifications', 'absence_notifications_session_status_type_index');
        $this->dropIndexIfExists('absence_notifications', 'absence_notifications_student_absence_status_created_index');

        $this->dropIndexIfExists('biometric_scans', 'biometric_scans_status_created_index');
        $this->dropIndexIfExists('biometric_scans', 'biometric_scans_student_scan_status_created_index');
    }
};
