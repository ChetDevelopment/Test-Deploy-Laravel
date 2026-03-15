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
        if (! $this->indexExists($table, $index)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($index): void {
            $tableBlueprint->dropIndex($index);
        });
    }

    /**
     * Run the migrations.
     * Performance optimization for dashboard + reporting queries.
     */
    public function up(): void
    {
        // Helps range scans with a status filter: `where status = ? and created_at between ...`
        $this->addIndexIfMissing('attendance_records', ['status', 'created_at'], 'idx_attendance_records_status_created');

        // Speeds up activity timeline queries.
        $this->addIndexIfMissing('teacher_activities', ['created_at'], 'idx_teacher_activities_created');

        // Speeds up "current session" lookups.
        $this->addIndexIfMissing('sessions', ['start_time', 'end_time'], 'idx_sessions_time');

        // Speeds up student filtering by class + year.
        $this->addIndexIfMissing('students', ['class_id', 'academic_year_id'], 'idx_students_class_academic');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('attendance_records', 'idx_attendance_records_status_created');
        $this->dropIndexIfExists('teacher_activities', 'idx_teacher_activities_created');
        $this->dropIndexIfExists('sessions', 'idx_sessions_time');
        $this->dropIndexIfExists('students', 'idx_students_class_academic');
    }
};
