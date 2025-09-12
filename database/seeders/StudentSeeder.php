<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get current academic year and some classes
        $currentYear = AcademicYear::where('is_current', true)->first();
        $classes = ClassModel::where('academic_year_id', $currentYear->id)->take(5)->get();

        if (!$currentYear || $classes->isEmpty()) {
            $this->command->warn('No current academic year or classes found. Please run AcademicSeeder first.');
            return;
        }

        // Create sample students
        $students = [
            [
                'name' => 'John Smith',
                'email' => 'john.smith@school.com',
                'student_id' => 'STU2024001',
                'date_of_birth' => '2008-03-15',
                'gender' => 'male',
                'blood_group' => 'A+',
                'address' => '123 Main Street, City',
                'phone' => '555-0101',
                'emergency_contact_name' => 'Jane Smith',
                'emergency_contact_phone' => '555-0102',
                'admission_date' => '2024-09-01',
                'status' => 'active',
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@school.com',
                'student_id' => 'STU2024002',
                'date_of_birth' => '2007-07-22',
                'gender' => 'female',
                'blood_group' => 'B+',
                'address' => '456 Oak Avenue, City',
                'phone' => '555-0201',
                'emergency_contact_name' => 'Robert Johnson',
                'emergency_contact_phone' => '555-0202',
                'admission_date' => '2024-09-01',
                'status' => 'active',
            ],
            [
                'name' => 'Michael Brown',
                'email' => 'michael.brown@school.com',
                'student_id' => 'STU2024003',
                'date_of_birth' => '2006-11-08',
                'gender' => 'male',
                'blood_group' => 'O+',
                'address' => '789 Pine Road, City',
                'phone' => '555-0301',
                'emergency_contact_name' => 'Lisa Brown',
                'emergency_contact_phone' => '555-0302',
                'admission_date' => '2024-09-01',
                'status' => 'active',
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emily.davis@school.com',
                'student_id' => 'STU2024004',
                'date_of_birth' => '2009-01-14',
                'gender' => 'female',
                'blood_group' => 'AB+',
                'address' => '321 Elm Street, City',
                'phone' => '555-0401',
                'emergency_contact_name' => 'David Davis',
                'emergency_contact_phone' => '555-0402',
                'admission_date' => '2024-09-01',
                'status' => 'active',
            ],
            [
                'name' => 'James Wilson',
                'email' => 'james.wilson@school.com',
                'student_id' => 'STU2024005',
                'date_of_birth' => '2005-05-30',
                'gender' => 'male',
                'blood_group' => 'A-',
                'address' => '654 Maple Drive, City',
                'phone' => '555-0501',
                'emergency_contact_name' => 'Mary Wilson',
                'emergency_contact_phone' => '555-0502',
                'admission_date' => '2024-09-01',
                'status' => 'active',
            ],
            [
                'name' => 'Olivia Martinez',
                'email' => 'olivia.martinez@school.com',
                'student_id' => 'STU2024006',
                'date_of_birth' => '2008-09-12',
                'gender' => 'female',
                'blood_group' => 'B-',
                'address' => '987 Cedar Lane, City',
                'phone' => '555-0601',
                'emergency_contact_name' => 'Carlos Martinez',
                'emergency_contact_phone' => '555-0602',
                'admission_date' => '2024-09-01',
                'status' => 'active',
            ],
            [
                'name' => 'William Anderson',
                'email' => 'william.anderson@school.com',
                'student_id' => 'STU2024007',
                'date_of_birth' => '2007-12-03',
                'gender' => 'male',
                'blood_group' => 'O-',
                'address' => '147 Birch Court, City',
                'phone' => '555-0701',
                'emergency_contact_name' => 'Susan Anderson',
                'emergency_contact_phone' => '555-0702',
                'admission_date' => '2024-09-01',
                'status' => 'active',
            ],
            [
                'name' => 'Sophia Taylor',
                'email' => 'sophia.taylor@school.com',
                'student_id' => 'STU2024008',
                'date_of_birth' => '2006-04-18',
                'gender' => 'female',
                'blood_group' => 'AB-',
                'address' => '258 Spruce Street, City',
                'phone' => '555-0801',
                'emergency_contact_name' => 'Michael Taylor',
                'emergency_contact_phone' => '555-0802',
                'admission_date' => '2024-09-01',
                'status' => 'active',
            ],
        ];

        foreach ($students as $studentData) {
            // Create user account
            $user = User::create([
                'name' => $studentData['name'],
                'email' => $studentData['email'],
                'password' => Hash::make('password123'),
                'role_id' => 3, // Student role
            ]);

            // Create student profile
            $student = Student::create(array_merge($studentData, [
                'user_id' => $user->id,
            ]));

            // Enroll student in a random class
            $randomClass = $classes->random();
            Enrollment::create([
                'student_id' => $student->id,
                'class_id' => $randomClass->id,
                'academic_year_id' => $currentYear->id,
                'enrollment_date' => '2024-09-01',
                'status' => 'active',
            ]);
        }

        $this->command->info('Created ' . count($students) . ' students with enrollments.');
    }
}
