<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_attendance_latest_follow_up');
        DB::statement('DROP VIEW IF EXISTS v_admin_attendance_enriched');

        DB::statement("
            CREATE VIEW v_admin_attendance_enriched AS
            SELECT
                ar.id AS attendance_id,
                ar.student_id,
                ar.session_id,
                ar.status,
                ar.location,
                ar.created_at,
                DATE(ar.created_at) AS created_date,
                TIME(ar.created_at) AS created_time,
                s.fullname AS student_name,
                s.username AS student_code,
                s.parent_number AS parent_number,
                s.class_id,
                s.academic_year_id,
                s.generation,
                COALESCE(c.class_name, s.class, 'Unknown') AS class_name
            FROM attendance_records ar
            INNER JOIN students s ON s.id = ar.student_id
            LEFT JOIN classes c ON c.id = s.class_id
        ");

        DB::statement("
            CREATE VIEW v_attendance_latest_follow_up AS
            SELECT
                af.attendance_record_id,
                af.id AS follow_up_id,
                af.status,
                af.comment,
                af.note,
                af.resolved,
                af.is_excused,
                af.reason,
                af.created_at AS follow_up_created_at
            FROM attendance_follow_ups af
            INNER JOIN (
                SELECT attendance_record_id, MAX(id) AS latest_id
                FROM attendance_follow_ups
                GROUP BY attendance_record_id
            ) latest ON latest.latest_id = af.id
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_attendance_latest_follow_up');
        DB::statement('DROP VIEW IF EXISTS v_admin_attendance_enriched');
    }
};
