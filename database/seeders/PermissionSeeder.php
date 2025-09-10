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
            
            // Class management
            ['name' => 'Manage Classes', 'verb' => 'create', 'resource' => 'classes'],
            ['name' => 'View Classes', 'verb' => 'read', 'resource' => 'classes'],
            ['name' => 'Edit Classes', 'verb' => 'update', 'resource' => 'classes'],
            ['name' => 'Delete Classes', 'verb' => 'delete', 'resource' => 'classes'],
            
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
            'Manage Classes',
            'View Classes',
            'Edit Classes',
            'Create Grades',
            'View Grades',
            'Edit Grades',
        ])->get();
        $teacher->permissions()->attach($teacherPermissions);

        // Student permissions
        $studentPermissions = Permission::whereIn('name', [
            'View Classes',
            'View Grades',
        ])->get();
        $student->permissions()->attach($studentPermissions);
    }
}
