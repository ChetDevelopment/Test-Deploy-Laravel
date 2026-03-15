<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run role seeder first
        $this->call(RoleSeeder::class);
        $this->call(StudentUserSeeder::class);

        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
        $teacherRoleId = DB::table('roles')->where('name', 'teacher')->value('id');

        DB::table('users')->updateOrInsert(
            ['email' => 'pnc@admin.passerellesnumeriques.org'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password123'),
                'role_id' => $adminRoleId,
                'student_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'teacher@example.com'],
            [
                'name' => 'Teacher User',
                'password' => Hash::make('password123'),
                'role_id' => $teacherRoleId,
                'student_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Uncomment the line below to seed sample data for testing
        // $this->call(SampleDataSeeder::class);
    }
}
