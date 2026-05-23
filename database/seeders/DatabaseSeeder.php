<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ensure Roles exist
        $roles = ['super-admin', 'manager', 'inventory-manager', 'cashier'];
        foreach ($roles as $roleSlug) {
            \App\Models\Role::firstOrCreate(['slug' => $roleSlug], [
                'name' => str_replace('-', ' ', ucwords($roleSlug, '-'))
            ]);
        }

        // Generate Super Admin User
        $adminRole = \App\Models\Role::where('slug', 'super-admin')->first();
        \App\Models\User::firstOrCreate(['email' => 'admin@pos.test'], [
            'name' => 'Super Admin',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role_id' => $adminRole->id
        ]);
        
        $this->call([
            DummyDataSeeder::class
        ]);
    }
}
