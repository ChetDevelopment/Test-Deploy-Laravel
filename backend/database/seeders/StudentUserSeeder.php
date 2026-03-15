<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create student role
        $studentRole = Role::firstOrCreate(
            ['name' => 'student'],
            ['description' => 'Student role for attendance and dashboard access']
        );

        $student = Student::firstOrCreate(
            ['email' => 'student@test.com'],
            [
                'fullname' => 'Test Student',
                'username' => 'student.test',
                'generation' => (string) now()->year,
                'class' => 'G10-A',
                'gender' => 'Male',
                'parent_number' => 'N/A',
                'contact' => 'N/A',
                'password' => 'student123',
                'card_id' => 'TESTCARD001',
                'fingerprint_enrolled' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => $student->email],
            [
                'name' => $student->fullname,
                'role_id' => $studentRole->id,
                'student_id' => $student->id,
                'password' => Hash::make('student123'),
            ]
        );

        $this->command->info('Student role and user created successfully!');
        $this->command->info('Email: student@test.com');
        $this->command->info('Password: student123');
    }
}
