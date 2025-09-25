<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Document Management
            'view documents',
            'create documents',
            'edit documents',
            'delete documents',
            'publish documents',
            'feature documents',
            
            // Document Types
            'view document-types',
            'create document-types',
            'edit document-types',
            'delete document-types',
            
            // Authors
            'view authors',
            'create authors',
            'edit authors',
            'delete authors',
            
            // Subjects
            'view subjects',
            'create subjects',
            'edit subjects',
            'delete subjects',
            
            // JDIHN Integration
            'view jdihn-sync',
            'manage jdihn-sync',
            'retry jdihn-sync',
            
            // System Management
            'view activity-log',
            'view system-settings',
            'edit system-settings',
            
            // Statistics & Reports
            'view statistics',
            'export reports',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles with permissions
        
        // Super Admin - has all permissions
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - can manage most things
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'view users', 'create users', 'edit users',
            'view documents', 'create documents', 'edit documents', 'publish documents', 'feature documents',
            'view document-types', 'create document-types', 'edit document-types',
            'view authors', 'create authors', 'edit authors',
            'view subjects', 'create subjects', 'edit subjects',
            'view jdihn-sync', 'manage jdihn-sync', 'retry jdihn-sync',
            'view activity-log',
            'view statistics', 'export reports',
        ]);

        // Koordinator - can manage documents and moderate content
        $koordinator = Role::create(['name' => 'koordinator']);
        $koordinator->givePermissionTo([
            'view users',
            'view documents', 'create documents', 'edit documents', 'publish documents',
            'view document-types',
            'view authors', 'create authors', 'edit authors',
            'view subjects', 'create subjects', 'edit subjects',
            'view jdihn-sync', 'retry jdihn-sync',
            'view statistics',
        ]);

        // Pustakawan - can create and edit documents
        $pustakawan = Role::create(['name' => 'pustakawan']);
        $pustakawan->givePermissionTo([
            'view documents', 'create documents', 'edit documents',
            'view document-types',
            'view authors', 'create authors',
            'view subjects',
            'view jdihn-sync',
        ]);

        // Create default super admin user
        $superUser = User::create([
            'name' => 'Super Administrator',
            'email' => 'admin@jdih.local',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $superUser->assignRole('super_admin');

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('Default admin user created:');
        $this->command->info('Email: admin@jdih.local');
        $this->command->info('Password: password');
    }
}
