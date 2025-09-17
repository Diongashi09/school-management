<?php

namespace App\Providers;

use App\Repositories\Interfaces\AcademicYearRepositoryInterface;
use App\Repositories\AcademicYearRepository;
use App\Repositories\Interfaces\SubjectRepositoryInterface;
use App\Repositories\SubjectRepository;
use App\Repositories\Interfaces\ClassRepositoryInterface;
use App\Repositories\ClassRepository;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use App\Repositories\StudentRepository;
use App\Repositories\Interfaces\TeacherRepositoryInterface;
use App\Repositories\TeacherRepository;
use App\Repositories\Interfaces\ClassTeacherRepositoryInterface;
use App\Repositories\ClassTeacherRepository;
use App\Repositories\Interfaces\ExamRepositoryInterface;
use App\Repositories\ExamRepository;
use App\Repositories\Interfaces\GradeRepositoryInterface;
use App\Repositories\GradeRepository;
use App\Repositories\Interfaces\ParentRepositoryInterface;
use App\Repositories\ParentRepository;
use App\Repositories\Interfaces\StudentParentRepositoryInterface;
use App\Repositories\StudentParentRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AcademicYearRepositoryInterface::class, AcademicYearRepository::class);
        $this->app->bind(SubjectRepositoryInterface::class, SubjectRepository::class);
        $this->app->bind(ClassRepositoryInterface::class, ClassRepository::class);
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
        $this->app->bind(TeacherRepositoryInterface::class, TeacherRepository::class);
        $this->app->bind(ClassTeacherRepositoryInterface::class, ClassTeacherRepository::class);
        $this->app->bind(ExamRepositoryInterface::class, ExamRepository::class);
        $this->app->bind(GradeRepositoryInterface::class, GradeRepository::class);
        // Parent Management
        $this->app->bind(ParentRepositoryInterface::class, ParentRepository::class);
        $this->app->bind(StudentParentRepositoryInterface::class, StudentParentRepository::class);
        $this->app->bind(
            \App\Repositories\Interfaces\AttendanceRepositoryInterface::class,
            \App\Repositories\AttendanceRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}