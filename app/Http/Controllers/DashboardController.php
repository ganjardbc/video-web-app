<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with statistics and data
     */
    public function index()
    {
        $stats = $this->getDashboardStats();
        $recentFiles = $this->getRecentFiles();
        $recentUsers = $this->getRecentUsers();
        $fileTypeStats = $this->getFileTypeStats();
        $monthlyFileUploads = $this->getMonthlyFileUploads();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentFiles' => $recentFiles,
            'recentUsers' => $recentUsers,
            'fileTypeStats' => $fileTypeStats,
            'monthlyFileUploads' => $monthlyFileUploads,
        ]);
    }

    /**
     * Get overall dashboard statistics
     */
    private function getDashboardStats()
    {
        $totalUsers = User::count();
        $totalFiles = File::count();
        $totalFileSize = File::sum('size') ?? 0;
        $publicFiles = File::where('is_public', true)->count();
        $privateFiles = File::where('is_public', false)->count();

        // Users registered this month
        $usersThisMonth = User::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Files uploaded this month
        $filesThisMonth = File::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Verified users
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $unverifiedUsers = User::whereNull('email_verified_at')->count();

        return [
            'totalUsers' => $totalUsers,
            'totalFiles' => $totalFiles,
            'totalFileSize' => $this->formatBytes($totalFileSize),
            'totalFileSizeBytes' => $totalFileSize,
            'publicFiles' => $publicFiles,
            'privateFiles' => $privateFiles,
            'usersThisMonth' => $usersThisMonth,
            'filesThisMonth' => $filesThisMonth,
            'verifiedUsers' => $verifiedUsers,
            'unverifiedUsers' => $unverifiedUsers,
        ];
    }

    /**
     * Get recent files (last 10)
     */
    private function getRecentFiles()
    {
        return File::with('user:id,name')
            ->latest()
            ->take(10)
            ->get(['id', 'filename', 'original_name', 'type', 'size', 'is_public', 'created_at', 'user_id'])
            ->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->filename,
                    'original_name' => $file->original_name,
                    'type' => $file->type,
                    'size' => $this->formatBytes($file->size),
                    'is_public' => $file->is_public,
                    'created_at' => $file->created_at->diffForHumans(),
                    'user' => $file->user ? $file->user->name : 'Unknown',
                ];
            });
    }

    /**
     * Get recent users (last 10)
     */
    private function getRecentUsers()
    {
        return User::latest()
            ->take(10)
            ->get(['id', 'name', 'email', 'email_verified_at', 'created_at'])
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_verified' => $user->email_verified_at !== null,
                    'created_at' => $user->created_at->diffForHumans(),
                ];
            });
    }

    /**
     * Get file type statistics
     */
    private function getFileTypeStats()
    {
        $fileTypes = File::selectRaw('
                CASE
                    WHEN mime_type LIKE "image%" THEN "Images"
                    WHEN mime_type LIKE "video%" THEN "Videos"
                    WHEN mime_type LIKE "audio%" THEN "Audio"
                    WHEN mime_type = "application/pdf" THEN "Documents"
                    WHEN mime_type IN ("application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "text/plain") THEN "Documents"
                    ELSE "Others"
                END as file_category,
                COUNT(*) as count,
                SUM(size) as total_size
            ')
            ->groupBy('file_category')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->file_category,
                    'count' => $item->count,
                    'size' => $this->formatBytes($item->total_size),
                    'sizeBytes' => $item->total_size,
                ];
            });

        return $fileTypes;
    }    /**
     * Get monthly file upload statistics for the last 6 months
     */
    private function getMonthlyFileUploads()
    {
        $months = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = File::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();

            $months[] = [
                'month' => $date->format('M Y'),
                'count' => $count,
            ];
        }

        return $months;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
