<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\File;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test users (only if they don't exist)
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );

        // Create additional users for dashboard stats (only if not many exist)
        if (User::count() < 10) {
            $users = User::factory(15)->create();

            // Mark some users as verified
            $users->take(10)->each(function($user) {
                $user->update(['email_verified_at' => now()]);
            });
        }

        // Create test files with different types and dates (only if not many exist)
        if (File::count() < 10) {
            $allUsers = User::all();

        // Create files with various mime types for file type stats
        $fileData = [
            ['original_name' => 'image1.jpg', 'mime_type' => 'image/jpeg', 'size' => 1024000],
            ['original_name' => 'image2.png', 'mime_type' => 'image/png', 'size' => 2048000],
            ['original_name' => 'video1.mp4', 'mime_type' => 'video/mp4', 'size' => 10240000],
            ['original_name' => 'video2.avi', 'mime_type' => 'video/avi', 'size' => 15360000],
            ['original_name' => 'document1.pdf', 'mime_type' => 'application/pdf', 'size' => 512000],
            ['original_name' => 'document2.docx', 'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'size' => 256000],
            ['original_name' => 'audio1.mp3', 'mime_type' => 'audio/mpeg', 'size' => 3072000],
            ['original_name' => 'text1.txt', 'mime_type' => 'text/plain', 'size' => 1024],
        ];

        foreach ($fileData as $data) {
            File::create([
                'share_id' => File::generateShareId(),
                'original_name' => $data['original_name'],
                'filename' => 'stored_' . $data['original_name'],
                'path' => 'files/' . $data['original_name'],
                'mime_type' => $data['mime_type'],
                'size' => $data['size'],
                'type' => explode('/', $data['mime_type'])[0],
                'is_public' => rand(0, 1),
                'user_id' => $allUsers->random()->id,
                'download_count' => rand(0, 50),
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);
        }

        // Create additional files for the last 6 months
        for ($i = 0; $i < 50; $i++) {
            File::create([
                'share_id' => File::generateShareId(),
                'original_name' => 'file_' . $i . '.jpg',
                'filename' => 'stored_file_' . $i . '.jpg',
                'path' => 'files/file_' . $i . '.jpg',
                'mime_type' => 'image/jpeg',
                'size' => rand(100000, 5000000),
                'type' => 'image',
                'is_public' => rand(0, 1),
                'user_id' => $allUsers->random()->id,
                'download_count' => rand(0, 100),
                'created_at' => Carbon::now()->subDays(rand(1, 180)),
            ]);
        }
        } // Close the if statement for File::count() < 10
    }
}
