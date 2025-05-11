<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin without user_type
        User::updateOrCreate(
            ['email' => 'admin@school.edu'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admin123'),
                // Remove user_type here
            ]
        );
        
        // Create teachers without user_type
        $sections = ['A', 'B', 'C', 'D', 'E'];
        
        foreach ($sections as $section) {
            User::updateOrCreate(
                ['email' => 'teacher' . strtolower($section) . '@school.edu'],
                [
                    'name' => 'Teacher ' . $section,
                    'password' => Hash::make('teacher' . strtolower($section) . '123'),
                    // Remove user_type here
                ]
            );
        }
        
        // Create parent without user_type
        User::updateOrCreate(
            ['email' => 'parent@example.com'],
            [
                'name' => 'Parent User',
                'password' => Hash::make('parent123'),
                // Remove user_type here
            ]
        );
    }
}