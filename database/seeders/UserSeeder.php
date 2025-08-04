<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $managerRole = Role::where('name', 'Manager')->first();
        $staffRole = Role::where('name', 'Staf')->first();

        if (!$managerRole || !$staffRole) {
            $this->command->error('Roles not found. Please run RoleSeeder first.');
            return;
        }

        // Create Manager
        $manager = User::create([
            'name' => 'Manager Utama',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'role_id' => $managerRole->id,
            'manager_id' => null, // Manager tidak punya manager
        ]);

        // Create Staff
        $staff1 = User::create([
            'name' => 'Staf Satu',
            'email' => 'staf1@example.com',
            'password' => Hash::make('password'),
            'role_id' => $staffRole->id,
            'manager_id' => $manager->id,
        ]);

        $staff2 = User::create([
            'name' => 'Staf Dua',
            'email' => 'staf2@example.com',
            'password' => Hash::make('password'),
            'role_id' => $staffRole->id,
            'manager_id' => $manager->id,
        ]);

        $this->command->info('Users seeded successfully!');
        $this->command->info('Manager: manager@example.com / password');
        $this->command->info('Staff 1: staf1@example.com / password');
        $this->command->info('Staff 2: staf2@example.com / password');
    }
}
