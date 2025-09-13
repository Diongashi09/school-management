<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // User management
            ['name' => 'Manage Users', 'verb' => 'create', 'resource' => 'users'],
            ['name' => 'View Users', 'verb' => 'read', 'resource' => 'users'],
            ['name' => 'Edit Users', 'verb' => 'update', 'resource' => 'users'],
            ['name' => 'Delete Users', 'verb' => 'delete', 'resource' => 'users'],
            
            // Academic Year management
            ['name' => 'Manage Academic Years', 'verb' => 'create', 'resource' => 'academic_years'],
            ['name' => 'View Academic Years', 'verb' => 'read', 'resource' => 'academic_years'],
            ['name' => 'Edit Academic Years', 'verb' => 'update', 'resource' => 'academic_years'],
            ['name' => 'Delete Academic Years', 'verb' => 'delete', 'resource' => 'academic_years'],
            
            // Subject management
            ['name' => 'Manage Subjects', 'verb' => 'create', 'resource' => 'subjects'],
            ['name' => 'View Subjects', 'verb' => 'read', 'resource' => 'subjects'],
            ['name' => 'Edit Subjects', 'verb' => 'update', 'resource' => 'subjects'],
            ['name' => 'Delete Subjects', 'verb' => 'delete', 'resource' => 'subjects'],
            
            // Class management
            ['name' => 'Manage Classes', 'verb' => 'create', 'resource' => 'classes'],
            ['name' => 'View Classes', 'verb' => 'read', 'resource' => 'classes'],
            ['name' => 'Edit Classes', 'verb' => 'update', 'resource' => 'classes'],
            ['name' => 'Delete Classes', 'verb' => 'delete', 'resource' => 'classes'],
            
            // Student management
            ['name' => 'Manage Students', 'verb' => 'create', 'resource' => 'students'],
            ['name' => 'View Students', 'verb' => 'read', 'resource' => 'students'],
            ['name' => 'Edit Students', 'verb' => 'update', 'resource' => 'students'],
            ['name' => 'Delete Students', 'verb' => 'delete', 'resource' => 'students'],
            
            // Teacher management
            ['name' => 'Manage Teachers', 'verb' => 'create', 'resource' => 'teachers'],
            ['name' => 'View Teachers', 'verb' => 'read', 'resource' => 'teachers'],
            ['name' => 'Edit Teachers', 'verb' => 'update', 'resource' => 'teachers'],
            ['name' => 'Delete Teachers', 'verb' => 'delete', 'resource' => 'teachers'],
            
            // Grade management
            ['name' => 'Create Grades', 'verb' => 'create', 'resource' => 'grades'],
            ['name' => 'View Grades', 'verb' => 'read', 'resource' => 'grades'],
            ['name' => 'Edit Grades', 'verb' => 'update', 'resource' => 'grades'],
            ['name' => 'Delete Grades', 'verb' => 'delete', 'resource' => 'grades'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Assign permissions to roles
        $admin = Role::where('name', 'admin')->first();
        $teacher = Role::where('name', 'teacher')->first();
        $student = Role::where('name', 'student')->first();

        // Admin gets all permissions
        $admin->permissions()->attach(Permission::all());

        // Teacher permissions
        $teacherPermissions = Permission::whereIn('name', [
            'View Users',
            'View Academic Years',
            'View Subjects',
            'Manage Classes',
            'View Classes',
            'Edit Classes',
            'View Students',
            'Edit Students',
            'View Teachers',
            'Edit Teachers',
            'Create Grades',
            'View Grades',
            'Edit Grades',
        ])->get();
        $teacher->permissions()->attach($teacherPermissions);

        // Student permissions
        $studentPermissions = Permission::whereIn('name', [
            'View Academic Years',
            'View Subjects',
            'View Classes',
            'View Students',
            'View Teachers',
            'View Grades',
        ])->get();
        $student->permissions()->attach($studentPermissions);
    }
}
