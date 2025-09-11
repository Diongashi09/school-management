<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Subject;
use App\Models\ClassModel;
use Illuminate\Database\Seeder;

class AcademicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Academic Years
        $academicYears = [
            [
                'name' => '2023-2024',
                'start_date' => '2023-09-01',
                'end_date' => '2024-06-30',
                'is_current' => false,
            ],
            [
                'name' => '2024-2025',
                'start_date' => '2024-09-01',
                'end_date' => '2025-06-30',
                'is_current' => true,
            ],
        ];

        foreach ($academicYears as $year) {
            AcademicYear::create($year);
        }

        // Create Subjects
        $subjects = [
            ['name' => 'Mathematics', 'code' => 'MATH101', 'description' => 'Basic Mathematics', 'credits' => 3],
            ['name' => 'English Language', 'code' => 'ENG101', 'description' => 'English Language and Literature', 'credits' => 3],
            ['name' => 'Science', 'code' => 'SCI101', 'description' => 'General Science', 'credits' => 3],
            ['name' => 'Social Studies', 'code' => 'SOC101', 'description' => 'Social Studies and History', 'credits' => 2],
            ['name' => 'Physical Education', 'code' => 'PE101', 'description' => 'Physical Education and Sports', 'credits' => 1],
            ['name' => 'Art', 'code' => 'ART101', 'description' => 'Visual Arts and Crafts', 'credits' => 1],
            ['name' => 'Computer Science', 'code' => 'CS101', 'description' => 'Computer Science and Programming', 'credits' => 2],
            ['name' => 'Physics', 'code' => 'PHY201', 'description' => 'Advanced Physics', 'credits' => 4],
            ['name' => 'Chemistry', 'code' => 'CHE201', 'description' => 'Advanced Chemistry', 'credits' => 4],
            ['name' => 'Biology', 'code' => 'BIO201', 'description' => 'Advanced Biology', 'credits' => 4],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }

        // Get current academic year
        $currentYear = AcademicYear::where('is_current', true)->first();

        if ($currentYear) {
            // Create Classes for current academic year
            $classes = [
                ['name' => 'Grade 1A', 'grade_level' => 1, 'section' => 'A', 'capacity' => 25],
                ['name' => 'Grade 1B', 'grade_level' => 1, 'section' => 'B', 'capacity' => 25],
                ['name' => 'Grade 2A', 'grade_level' => 2, 'section' => 'A', 'capacity' => 25],
                ['name' => 'Grade 2B', 'grade_level' => 2, 'section' => 'B', 'capacity' => 25],
                ['name' => 'Grade 3A', 'grade_level' => 3, 'section' => 'A', 'capacity' => 30],
                ['name' => 'Grade 3B', 'grade_level' => 3, 'section' => 'B', 'capacity' => 30],
                ['name' => 'Grade 4A', 'grade_level' => 4, 'section' => 'A', 'capacity' => 30],
                ['name' => 'Grade 4B', 'grade_level' => 4, 'section' => 'B', 'capacity' => 30],
                ['name' => 'Grade 5A', 'grade_level' => 5, 'section' => 'A', 'capacity' => 30],
                ['name' => 'Grade 5B', 'grade_level' => 5, 'section' => 'B', 'capacity' => 30],
                ['name' => 'Grade 6A', 'grade_level' => 6, 'section' => 'A', 'capacity' => 30],
                ['name' => 'Grade 6B', 'grade_level' => 6, 'section' => 'B', 'capacity' => 30],
                ['name' => 'Grade 7A', 'grade_level' => 7, 'section' => 'A', 'capacity' => 35],
                ['name' => 'Grade 7B', 'grade_level' => 7, 'section' => 'B', 'capacity' => 35],
                ['name' => 'Grade 8A', 'grade_level' => 8, 'section' => 'A', 'capacity' => 35],
                ['name' => 'Grade 8B', 'grade_level' => 8, 'section' => 'B', 'capacity' => 35],
                ['name' => 'Grade 9A', 'grade_level' => 9, 'section' => 'A', 'capacity' => 35],
                ['name' => 'Grade 9B', 'grade_level' => 9, 'section' => 'B', 'capacity' => 35],
                ['name' => 'Grade 10A', 'grade_level' => 10, 'section' => 'A', 'capacity' => 40],
                ['name' => 'Grade 10B', 'grade_level' => 10, 'section' => 'B', 'capacity' => 40],
                ['name' => 'Grade 11A', 'grade_level' => 11, 'section' => 'A', 'capacity' => 40],
                ['name' => 'Grade 11B', 'grade_level' => 11, 'section' => 'B', 'capacity' => 40],
                ['name' => 'Grade 12A', 'grade_level' => 12, 'section' => 'A', 'capacity' => 40],
                ['name' => 'Grade 12B', 'grade_level' => 12, 'section' => 'B', 'capacity' => 40],
            ];

            foreach ($classes as $class) {
                ClassModel::create(array_merge($class, ['academic_year_id' => $currentYear->id]));
            }
        }
    }
}
