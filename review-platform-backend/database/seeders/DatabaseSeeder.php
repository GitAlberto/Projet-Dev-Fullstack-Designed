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
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Create regular user
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        // Create sample reviews
        \App\Models\Review::create([
            'user_id' => $user->id,
            'content' => 'Excellent product! Very fast delivery and great quality. I love it!',
            'sentiment' => 'positive',
            'score' => 95,
            'topics' => ['delivery', 'quality', 'product'],
        ]);

        \App\Models\Review::create([
            'user_id' => $user->id,
            'content' => 'The product is okay but the price is too high.',
            'sentiment' => 'neutral',
            'score' => 50,
            'topics' => ['price', 'product'],
        ]);

        \App\Models\Review::create([
            'user_id' => $user->id,
            'content' => 'Terrible experience. Slow delivery and bad customer service.',
            'sentiment' => 'negative',
            'score' => 20,
            'topics' => ['delivery', 'service'],
        ]);
    }
}
