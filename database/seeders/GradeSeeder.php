<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\Grade;
use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get current academic year, classes, subjects, and students
        $currentYear = AcademicYear::where('is_current', true)->first();
        $classes = ClassModel::where('academic_year_id', $currentYear->id)->take(3)->get();
        $subjects = Subject::take(3)->get();
        $students = Student::take(10)->get();
        $teachers = User::whereHas('role', function ($q) {
            $q->where('name', 'teacher');
        })->take(3)->get();

        if (!$currentYear || $classes->isEmpty() || $subjects->isEmpty() || $students->isEmpty() || $teachers->isEmpty()) {
            $this->command->warn('Required data not found. Please run other seeders first.');
            return;
        }

        // Create sample exams
        $exams = [
            [
                'name' => 'Mathematics Mid-term Exam',
                'exam_type' => 'midterm',
                'class_id' => $classes->first()->id,
                'subject_id' => $subjects->where('name', 'Mathematics')->first()->id ?? $subjects->first()->id,
                'academic_year_id' => $currentYear->id,
                'total_marks' => 100.00,
                'passing_marks' => 40.00,
                'exam_date' => now()->subDays(30),
                'start_time' => '09:00',
                'end_time' => '11:00',
                'instructions' => 'Answer all questions. Show your work clearly.',
                'is_published' => true,
            ],
            [
                'name' => 'English Language Test',
                'exam_type' => 'test',
                'class_id' => $classes->first()->id,
                'subject_id' => $subjects->where('name', 'English Language')->first()->id ?? $subjects->first()->id,
                'academic_year_id' => $currentYear->id,
                'total_marks' => 50.00,
                'passing_marks' => 25.00,
                'exam_date' => now()->subDays(20),
                'start_time' => '10:00',
                'end_time' => '11:30',
                'instructions' => 'Read the questions carefully before answering.',
                'is_published' => true,
            ],
            [
                'name' => 'Science Quiz',
                'exam_type' => 'quiz',
                'class_id' => $classes->first()->id,
                'subject_id' => $subjects->where('name', 'Science')->first()->id ?? $subjects->first()->id,
                'academic_year_id' => $currentYear->id,
                'total_marks' => 25.00,
                'passing_marks' => 12.50,
                'exam_date' => now()->subDays(10),
                'start_time' => '14:00',
                'end_time' => '14:30',
                'instructions' => 'Multiple choice questions. Choose the best answer.',
                'is_published' => true,
            ],
            [
                'name' => 'Mathematics Assignment',
                'exam_type' => 'assignment',
                'class_id' => $classes->first()->id,
                'subject_id' => $subjects->where('name', 'Mathematics')->first()->id ?? $subjects->first()->id,
                'academic_year_id' => $currentYear->id,
                'total_marks' => 30.00,
                'passing_marks' => 15.00,
                'exam_date' => now()->subDays(5),
                'instructions' => 'Complete all problems and show detailed solutions.',
                'is_published' => true,
            ],
            [
                'name' => 'Upcoming Final Exam',
                'exam_type' => 'final',
                'class_id' => $classes->first()->id,
                'subject_id' => $subjects->first()->id,
                'academic_year_id' => $currentYear->id,
                'total_marks' => 150.00,
                'passing_marks' => 60.00,
                'exam_date' => now()->addDays(30),
                'start_time' => '09:00',
                'end_time' => '12:00',
                'instructions' => 'Comprehensive final examination. All topics covered.',
                'is_published' => false,
            ],
        ];

        foreach ($exams as $examData) {
            Exam::create($examData);
        }

        // Create sample grades for published exams
        $publishedExams = Exam::where('is_published', true)->get();

        foreach ($publishedExams as $exam) {
            $classStudents = $students->take(rand(5, 8)); // Random number of students per exam

            foreach ($classStudents as $student) {
                // Generate realistic grade distribution
                $totalMarks = $exam->total_marks;
                $passingMarks = $exam->passing_marks;
                
                // 70% chance of passing
                if (rand(1, 100) <= 70) {
                    $obtainedMarks = rand($passingMarks, $totalMarks);
                } else {
                    $obtainedMarks = rand(0, $passingMarks - 1);
                }

                // Add some variation to make it more realistic
                $obtainedMarks += (rand(-5, 5) * $totalMarks / 100);
                $obtainedMarks = max(0, min($totalMarks, $obtainedMarks));

                Grade::create([
                    'student_id' => $student->id,
                    'exam_id' => $exam->id,
                    'obtained_marks' => round($obtainedMarks, 2),
                    'remarks' => $obtainedMarks >= $passingMarks ? 'Good work!' : 'Needs improvement.',
                    'created_by' => $teachers->random()->id,
                ]);
            }
        }

        $this->command->info('Created ' . count($exams) . ' exams with sample grades.');
    }
}
