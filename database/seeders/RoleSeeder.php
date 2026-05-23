<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin'],
            ['name' => 'Manager', 'slug' => 'manager'],
            ['name' => 'Inventory Manager', 'slug' => 'inventory-manager'],
            ['name' => 'Cashier', 'slug' => 'cashier'],
        ];

        foreach ($roles as $role) {
            \App\Models\Role::firstOrCreate(['slug' => $role['slug']], $role);
        }

        // Assign super-admin role to existing admin user
        $admin = \App\Models\User::where('email', 'admin@pos.test')->first();
        if ($admin) {
            $superAdminRole = \App\Models\Role::where('slug', 'super-admin')->first();
            $admin->update(['role_id' => $superAdminRole->id]);
        }
    }
}
