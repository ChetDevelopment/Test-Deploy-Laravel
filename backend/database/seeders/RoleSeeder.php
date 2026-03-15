<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles if they don't exist
        $roles = [
            ['name' => 'admin', 'description' => 'System administrator with full access'],
            ['name' => 'teacher', 'description' => 'Teacher role with limited access'],
            ['name' => 'education', 'description' => 'Education department staff with academic management access'],
            ['name' => 'student', 'description' => 'Student role for attendance and dashboard access'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']],
                [
                    'description' => $role['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}