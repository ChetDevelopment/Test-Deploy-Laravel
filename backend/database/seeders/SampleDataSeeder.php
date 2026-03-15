<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates comprehensive sample data for testing
     * the attendance management system including:
     * - Academic years
     * - Classes
     * - Sessions
     * - Students with biometric data
     * - Users (admin, teachers)
     * - Attendance records
     * - Biometric scans
     */
    public function run(): void
    {
        $this->command->info('Starting sample data seeding...');

        // Disable foreign key checks for faster inserts
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Clear existing data
        $this->clearData();

        // Seed data in order (respecting foreign keys)
        $academicYearId = $this->seedAcademicYears();
        $classIds = $this->seedClasses($academicYearId);
        $sessionIds = $this->seedSessions($academicYearId);
        $this->seedRoles();
        $userIds = $this->seedUsers();
        $studentIds = $this->seedStudents($classIds, $academicYearId);
        $this->seedAttendanceRecords($studentIds, $sessionIds, $userIds);
        $this->seedBiometricScans($studentIds, $sessionIds);

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info('Sample data seeding completed!');
    }

    private function clearData(): void
    {
        $this->command->info('Clearing existing data...');
        
        DB::table('biometric_scans')->delete();
        DB::table('attendance_records')->delete();
        DB::table('attendances')->delete();
        DB::table('absence_notifications')->delete();
        DB::table('users')->delete();
        DB::table('roles')->delete();
        DB::table('students')->delete();
        DB::table('sessions')->delete();
        DB::table('classes')->delete();
        DB::table('academic_years')->delete();
        
        // Reset auto increment
        DB::statement('ALTER TABLE biometric_scans AUTO_INCREMENT=1');
        DB::statement('ALTER TABLE attendance_records AUTO_INCREMENT=1');
        DB::statement('ALTER TABLE attendances AUTO_INCREMENT=1');
        DB::statement('ALTER TABLE absence_notifications AUTO_INCREMENT=1');
        DB::statement('ALTER TABLE users AUTO_INCREMENT=1');
        DB::statement('ALTER TABLE roles AUTO_INCREMENT=1');
        DB::statement('ALTER TABLE students AUTO_INCREMENT=1');
        DB::statement('ALTER TABLE sessions AUTO_INCREMENT=1');
        DB::statement('ALTER TABLE classes AUTO_INCREMENT=1');
        DB::statement('ALTER TABLE academic_years AUTO_INCREMENT=1');
    }

    private function seedAcademicYears(): int
    {
        $this->command->info('Seeding academic years...');

        $academicYears = [
            [
                'name' => '2025-2026',
                'current_term' => 'Term2',
                'status' => 'Current',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '2024-2025',
                'current_term' => 'Term3',
                'status' => 'Close',
                'created_at' => now()->subYear(),
                'updated_at' => now()->subYear(),
            ],
            [
                'name' => '2026-2027',
                'current_term' => 'Term1',
                'status' => 'Current',
                'created_at' => now()->addYear(),
                'updated_at' => now()->addYear(),
            ],
        ];

        DB::table('academic_years')->insert($academicYears);

        return DB::table('academic_years')->where('name', '2025-2026')->first()->id;
    }

    private function seedClasses(int $academicYearId): array
    {
        $this->command->info('Seeding classes...');

        $classes = [
            ['academic_year_id' => $academicYearId, 'class_name' => 'G10-A', 'room_number' => '101', 'created_at' => now(), 'updated_at' => now()],
            ['academic_year_id' => $academicYearId, 'class_name' => 'G10-B', 'room_number' => '102', 'created_at' => now(), 'updated_at' => now()],
            ['academic_year_id' => $academicYearId, 'class_name' => 'G10-C', 'room_number' => '103', 'created_at' => now(), 'updated_at' => now()],
            ['academic_year_id' => $academicYearId, 'class_name' => 'G11-A', 'room_number' => '201', 'created_at' => now(), 'updated_at' => now()],
            ['academic_year_id' => $academicYearId, 'class_name' => 'G11-B', 'room_number' => '202', 'created_at' => now(), 'updated_at' => now()],
            ['academic_year_id' => $academicYearId, 'class_name' => 'G12-A', 'room_number' => '301', 'created_at' => now(), 'updated_at' => now()],
            ['academic_year_id' => $academicYearId, 'class_name' => 'G12-B', 'room_number' => '302', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('classes')->insert($classes);

        return DB::table('classes')->where('academic_year_id', $academicYearId)
            ->pluck('id')
            ->toArray();
    }

    private function seedSessions(int $academicYearId): array
    {
        $this->command->info('Seeding sessions...');

        $sessions = [
            ['academic_year_id' => $academicYearId, 'name' => 'Session 1', 'start_time' => '08:00:00', 'end_time' => '10:00:00', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['academic_year_id' => $academicYearId, 'name' => 'Session 2', 'start_time' => '10:30:00', 'end_time' => '12:30:00', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['academic_year_id' => $academicYearId, 'name' => 'Session 3', 'start_time' => '13:30:00', 'end_time' => '15:30:00', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['academic_year_id' => $academicYearId, 'name' => 'Session 4', 'start_time' => '16:00:00', 'end_time' => '18:00:00', 'order' => 4, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('sessions')->insert($sessions);

        return DB::table('sessions')->where('academic_year_id', $academicYearId)
            ->orderBy('order')
            ->pluck('id')
            ->toArray();
    }

    private function seedUsers(): array
    {
        $this->command->info('Seeding users...');

        // Get role IDs
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id') ?? 1;
        $teacherRoleId = DB::table('roles')->where('name', 'teacher')->value('id') ?? 2;
        $educationRoleId = DB::table('roles')->where('name', 'education')->value('id') ?? 3;

        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@pnc.edu',
                'password' => Hash::make('admin123'),
                'role_id' => $adminRoleId,
                'student_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Chhay Bunthoeun',
                'email' => 'chhay.bunthoeun@pnc.edu',
                'password' => Hash::make('teacher123'),
                'role_id' => $teacherRoleId,
                'student_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sokha Kin',
                'email' => 'sokha.kin@pnc.edu',
                'password' => Hash::make('teacher123'),
                'role_id' => $teacherRoleId,
                'student_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Vichea Mao',
                'email' => 'vichea.mao@pnc.edu',
                'password' => Hash::make('teacher123'),
                'role_id' => $teacherRoleId,
                'student_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sothea Narin',
                'email' => 'sothea.narin@pnc.edu',
                'password' => Hash::make('teacher123'),
                'role_id' => $educationRoleId,
                'student_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pisey Steng',
                'email' => 'pisey.steng@pnc.edu',
                'password' => Hash::make('teacher123'),
                'role_id' => $teacherRoleId,
                'student_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ratha Kim',
                'email' => 'ratha.kim@pnc.edu',
                'password' => Hash::make('teacher123'),
                'role_id' => $teacherRoleId,
                'student_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);

        return DB::table('users')->pluck('id')->toArray();
    }

    private function seedRoles(): void
    {
        $this->command->info('Seeding roles...');

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

    private function seedStudents(array $classIds, int $academicYearId): array
    {
        $this->command->info('Seeding students...');

        $studentData = $this->generateStudentData();
        $students = [];
        $studentIds = [];

        foreach ($studentData as $index => $student) {
            $classId = $classIds[$index % count($classIds)];
            $generation = '2025';
            
            $students[] = [
                'fullname' => $student['fullname'],
                'username' => $student['username'],
                'email' => $student['email'],
                'generation' => $generation,
                'class' => $student['class'],
                'class_id' => $classId,
                'academic_year_id' => $academicYearId,
                'profile' => null,
                'gender' => $student['gender'],
                'parent_number' => $student['parent_number'],
                'contact' => $student['contact'],
                'password' => Hash::make('student123'),
                'card_id' => str_pad($index + 1, 8, '0', STR_PAD_LEFT),
                'fingerprint_template' => null,
                'fingerprint_enrolled' => $index % 3 !== 0, // 2/3 enrolled
                'last_biometric_scan' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert in batches
        foreach (array_chunk($students, 50) as $batch) {
            DB::table('students')->insert($batch);
        }

        $studentIds = DB::table('students')->pluck('id')->toArray();

        // Create student users
        $this->createStudentUsers($studentIds);

        return $studentIds;
    }

    private function createStudentUsers(array $studentIds): void
    {
        $this->command->info('Creating student users...');

        $studentRoleId = DB::table('roles')->where('name', 'student')->value('id') ?? 4;

        $users = [];
        foreach ($studentIds as $index => $studentId) {
            $student = DB::table('students')->where('id', $studentId)->first();
            
            $users[] = [
                'name' => $student->fullname,
                'email' => $student->email,
                'password' => Hash::make('student123'),
                'role_id' => $studentRoleId,
                'student_id' => $studentId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($users, 50) as $batch) {
            DB::table('users')->insert($batch);
        }
    }

    private function generateStudentData(): array
    {
        $firstNamesMale = [
            'Sok', 'Vanna', 'Dara', 'Sothea', 'Rith', 'Piseth', 'Chandara', 'Bona',
            'Sokha', 'Vichea', 'Sotheara', 'Kimsreang', 'Virak', 'Sokna', 'Ratha',
            'Chansothea', 'Pisal', 'Sokheng', 'Vibol', 'Sokpol', 'Chansovannara',
            'Raksmey', 'Sokkhoeun', 'Sokchan', 'Sokmony', 'Vannak', 'Rithy',
            'Sokunthea', 'Chanserey', 'Sokmey', 'Vannarith', 'Sokkang', 'Raksmey',
            'Sokhour', 'Vannara', 'Sokphalla', 'Rithisak', 'Sokvannara', 'Chanserey'
        ];

        $firstNamesFemale = [
            'Sokha', 'Sokna', 'Sokha', 'Ratha', 'Sothea', 'Pisey', 'Sokha', 'Vichey',
            'Sokha', 'Sokha', 'Sokha', 'Sotheara', 'Pisey', 'Sokha', 'Raksmey',
            'Sokha', 'Sokha', 'Sokha', 'Sotheara', 'Pisey', 'Sokha', 'Sokha',
            'Sokha', 'Sotheara', 'Pisey', 'Sokha', 'Ratha', 'Sokha', 'Sokha',
            'Sotheara', 'Pisey', 'Sokha', 'Sokha', 'Raksmey', 'Sokha', 'Sokha'
        ];

        $lastNames = [
            'Chhem', 'Thong', 'Sok', 'Ly', 'Keo', 'Mao', 'Lim', 'Chhay',
            'Narin', 'Kim', 'Steng', 'Bunthoeun', 'Pich', 'Srun', 'Touch',
            'Mek', 'Oun', 'Sin', 'Phan', 'Chhoeun', 'San', 'Nov', 'Kong',
            'Heng', 'Chin', 'Seang', 'Lay', 'Chhuon', 'Tea', 'Nget', 'Sarith'
        ];

        $classes = ['G10-A', 'G10-B', 'G10-C', 'G11-A', 'G11-B', 'G12-A', 'G12-B'];
        $genders = ['Male', 'Female'];
        $students = [];

        // Generate 100 students
        for ($i = 1; $i <= 100; $i++) {
            $gender = $genders[$i % 2];
            $firstName = $gender === 'Male' 
                ? $firstNamesMale[$i % count($firstNamesMale)] 
                : $firstNamesFemale[$i % count($firstNamesFemale)];
            $lastName = $lastNames[$i % count($lastNames)];
            $fullName = "$firstName $lastName";
            
            $username = strtolower($firstName) . '.' . strtolower($lastName) . $i;
            $email = strtolower($firstName) . '.' . strtolower($lastName) . $i . '@student.pnc.edu';
            
            // Generate Cambodian phone numbers
            $phoneDigits = str_pad($i, 8, '0', STR_PAD_LEFT);
            $parentNumber = '855' . $phoneDigits;
            $contact = '855' . str_pad($i + 50000, 8, '0', STR_PAD_LEFT);

            $students[] = [
                'fullname' => $fullName,
                'username' => $username,
                'email' => $email,
                'generation' => '2025',
                'class' => $classes[$i % count($classes)],
                'gender' => $gender,
                'parent_number' => $parentNumber,
                'contact' => $contact,
            ];
        }

        return $students;
    }

    private function seedAttendanceRecords(array $studentIds, array $sessionIds, array $userIds): void
    {
        $this->command->info('Seeding attendance records...');

        // Get admin user as default submitter
        $submitterId = $userIds[0];
        
        // Generate attendance for the past 30 days
        $attendanceRecords = [];
        $statuses = ['Present', 'Present', 'Present', 'Present', 'Late', 'Absent', 'Excused'];
        
        // Current date is 2026-03-09
        $startDate = Carbon::parse('2026-02-09');
        $endDate = Carbon::parse('2026-03-08');

        $recordId = 1;
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            // Skip weekends (Saturday = 6, Sunday = 0)
            if ($date->dayOfWeek === Carbon::SATURDAY || $date->dayOfWeek === Carbon::SUNDAY) {
                continue;
            }

            // Create attendance for each session
            foreach ($sessionIds as $sessionIndex => $sessionId) {
                // Not all students attend all sessions
                foreach ($studentIds as $studentIndex => $studentId) {
                    // Randomly skip some attendance (simulate not all students have records)
                    if (mt_rand(1, 100) > 90) {
                        continue;
                    }

                    // Weighted random status
                    $statusWeights = [0 => 'Present', 1 => 'Present', 2 => 'Present', 3 => 'Present', 4 => 'Late', 5 => 'Absent', 6 => 'Excused'];
                    $status = $statusWeights[array_rand($statusWeights)];

                    // Determine location based on session
                    $locations = ['Classroom 101', 'Classroom 102', 'Classroom 103', 'Lab 201', 'Lab 202', 'Library'];
                    $location = $locations[array_rand($locations)];

                    // Calculate created_at to spread over time
                    $createdAt = $date->copy()->addHours(mt_rand(8, 18))->addMinutes(mt_rand(0, 59));

                    $attendanceRecords[] = [
                        'student_id' => $studentId,
                        'session_id' => $sessionId,
                        'status' => $status,
                        'location' => $location,
                        'submitted_by' => $submitterId,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ];

                    $recordId++;

                    // Insert in batches to avoid memory issues
                    if (count($attendanceRecords) >= 500) {
                        DB::table('attendance_records')->insert($attendanceRecords);
                        $attendanceRecords = [];
                    }
                }
            }
        }

        // Insert remaining records
        if (!empty($attendanceRecords)) {
            DB::table('attendance_records')->insert($attendanceRecords);
        }

        $this->command->info('Created attendance records successfully');
    }

    private function seedBiometricScans(array $studentIds, array $sessionIds): void
    {
        $this->command->info('Seeding biometric scans...');

        $biometricScans = [];
        $scanTypes = ['card', 'fingerprint'];
        $statuses = ['success', 'success', 'success', 'failed', 'duplicate'];
        
        // Generate scans for the past 7 days
        $startDate = Carbon::parse('2026-03-02');
        
        $scanId = 1;
        for ($date = $startDate; $date->lte(Carbon::now()); $date->addDay()) {
            // Skip weekends
            if ($date->dayOfWeek === Carbon::SATURDAY || $date->dayOfWeek === Carbon::SUNDAY) {
                continue;
            }

            // Each day have scans for each session
            foreach ($sessionIds as $sessionIndex => $sessionId) {
                // Random number of scans per session
                $numScans = mt_rand(50, count($studentIds));

                for ($i = 0; $i < $numScans; $i++) {
                    $studentId = $studentIds[mt_rand(0, count($studentIds) - 1)];
                    $scanType = $scanTypes[array_rand($scanTypes)];
                    
                    // Get student's card_id for scan data
                    $student = DB::table('students')->where('id', $studentId)->first();
                    $scanData = $student->card_id ?? 'CARD' . str_pad($studentId, 8, '0', STR_PAD_LEFT);
                    
                    $status = $statuses[array_rand($statuses)];
                    $failureReason = $status !== 'success' ? 'Invalid card' : null;

                    // Scan time based on session
                    $sessionTimes = ['08:00:00', '10:30:00', '13:30:00', '16:00:00'];
                    $sessionTime = $sessionTimes[$sessionIndex];
                    $scanTime = $date->copy()->setTimeFromTimeString($sessionTime);

                    $biometricScans[] = [
                        'student_id' => $studentId,
                        'session_id' => $sessionId,
                        'scan_type' => $scanType,
                        'scan_data' => $scanData,
                        'status' => $status,
                        'failure_reason' => $failureReason,
                        'device_id' => 'DEVICE-001',
                        'ip_address' => '192.168.1.' . mt_rand(1, 254),
                        'created_at' => $scanTime,
                        'updated_at' => $scanTime,
                    ];

                    $scanId++;

                    // Insert in batches
                    if (count($biometricScans) >= 500) {
                        DB::table('biometric_scans')->insert($biometricScans);
                        $biometricScans = [];
                    }
                }
            }
        }

        // Insert remaining records
        if (!empty($biometricScans)) {
            DB::table('biometric_scans')->insert($biometricScans);
        }

        // Update last_biometric_scan for some students
        DB::table('students')
            ->where('fingerprint_enrolled', true)
            ->update(['last_biometric_scan' => Carbon::now()->subMinutes(mt_rand(1, 120))]);

        $this->command->info('Created biometric scans successfully');
    }
}
