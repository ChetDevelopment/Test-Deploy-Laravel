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

    public function up(): void
    {
        $this->addIndexIfMissing('students', ['class_id', 'id'], 'students_class_id_id_index');
        $this->addIndexIfMissing('students', ['academic_year_id', 'id'], 'students_academic_year_id_id_index');
        $this->addIndexIfMissing('students', ['generation', 'id'], 'students_generation_id_index');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('students', 'students_class_id_id_index');
        $this->dropIndexIfExists('students', 'students_academic_year_id_id_index');
        $this->dropIndexIfExists('students', 'students_generation_id_index');
    }
};
