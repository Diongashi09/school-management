<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Teacher;
use App\Models\ClassTeacher;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get current academic year, classes, and subjects
        $currentYear = AcademicYear::where('is_current', true)->first();
        $classes = ClassModel::where('academic_year_id', $currentYear->id)->take(5)->get();
        $subjects = Subject::take(5)->get();

        if (!$currentYear || $classes->isEmpty() || $subjects->isEmpty()) {
            $this->command->warn('No current academic year, classes, or subjects found. Please run AcademicSeeder first.');
            return;
        }

        // Create sample teachers
        $teachers = [
            [
                'name' => 'Dr. Sarah Williams',
                'email' => 'sarah.williams@school.com',
                'employee_id' => 'TCH2024001',
                'date_of_birth' => '1985-03-15',
                'gender' => 'female',
                'phone' => '555-1001',
                'address' => '123 Teacher Lane, City',
                'qualification' => 'PhD in Mathematics',
                'specialization' => 'Advanced Mathematics',
                'hire_date' => '2020-09-01',
                'salary' => 75000.00,
                'status' => 'active',
            ],
            [
                'name' => 'Mr. John Davis',
                'email' => 'john.davis@school.com',
                'employee_id' => 'TCH2024002',
                'date_of_birth' => '1982-07-22',
                'gender' => 'male',
                'phone' => '555-1002',
                'address' => '456 Educator Street, City',
                'qualification' => 'Master of Arts in English',
                'specialization' => 'Literature and Composition',
                'hire_date' => '2019-09-01',
                'salary' => 68000.00,
                'status' => 'active',
            ],
            [
                'name' => 'Ms. Emily Johnson',
                'email' => 'emily.johnson@school.com',
                'employee_id' => 'TCH2024003',
                'date_of_birth' => '1988-11-08',
                'gender' => 'female',
                'phone' => '555-1003',
                'address' => '789 Science Avenue, City',
                'qualification' => 'Master of Science in Physics',
                'specialization' => 'Physics and Chemistry',
                'hire_date' => '2021-09-01',
                'salary' => 72000.00,
                'status' => 'active',
            ],
            [
                'name' => 'Mr. Michael Brown',
                'email' => 'michael.brown@school.com',
                'employee_id' => 'TCH2024004',
                'date_of_birth' => '1980-01-14',
                'gender' => 'male',
                'phone' => '555-1004',
                'address' => '321 History Road, City',
                'qualification' => 'Master of Arts in History',
                'specialization' => 'World History and Social Studies',
                'hire_date' => '2018-09-01',
                'salary' => 65000.00,
                'status' => 'active',
            ],
            [
                'name' => 'Ms. Lisa Anderson',
                'email' => 'lisa.anderson@school.com',
                'employee_id' => 'TCH2024005',
                'date_of_birth' => '1987-05-30',
                'gender' => 'female',
                'phone' => '555-1005',
                'address' => '654 Art Boulevard, City',
                'qualification' => 'Bachelor of Fine Arts',
                'specialization' => 'Visual Arts and Design',
                'hire_date' => '2022-09-01',
                'salary' => 58000.00,
                'status' => 'active',
            ],
            [
                'name' => 'Mr. David Wilson',
                'email' => 'david.wilson@school.com',
                'employee_id' => 'TCH2024006',
                'date_of_birth' => '1983-09-12',
                'gender' => 'male',
                'phone' => '555-1006',
                'address' => '987 Computer Street, City',
                'qualification' => 'Master of Science in Computer Science',
                'specialization' => 'Computer Science and Programming',
                'hire_date' => '2020-09-01',
                'salary' => 78000.00,
                'status' => 'active',
            ],
        ];

        foreach ($teachers as $teacherData) {
            // Create user account
            $user = User::create([
                'name' => $teacherData['name'],
                'email' => $teacherData['email'],
                'password' => Hash::make('password123'),
                'role_id' => 2, // Teacher role
            ]);

            // Create teacher profile
            $teacher = Teacher::create(array_merge($teacherData, [
                'user_id' => $user->id,
            ]));

            // Assign teacher to random classes and subjects
            $randomClass = $classes->random();
            $randomSubject = $subjects->random();
            
            ClassTeacher::create([
                'teacher_id' => $teacher->id,
                'class_id' => $randomClass->id,
                'subject_id' => $randomSubject->id,
                'academic_year_id' => $currentYear->id,
                'is_primary' => true, // Make them primary class teachers
            ]);

            // Assign additional subjects to some teachers
            if (rand(0, 1)) {
                $additionalSubject = $subjects->where('id', '!=', $randomSubject->id)->random();
                ClassTeacher::create([
                    'teacher_id' => $teacher->id,
                    'class_id' => $randomClass->id,
                    'subject_id' => $additionalSubject->id,
                    'academic_year_id' => $currentYear->id,
                    'is_primary' => false,
                ]);
            }
        }

        $this->command->info('Created ' . count($teachers) . ' teachers with class assignments.');
    }
}
