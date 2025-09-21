<?php

namespace App\Http\Middleware;

use App\Models\File;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FileAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get file from route parameter
        $file = $request->route('file');

        if ($file instanceof File) {
            $user = $request->user();

            // Check if user can access this file
            if (!$file->canBeAccessedBy($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. You do not have permission to access this file.',
                ], 403);
            }

            // Check if file is expired
            if ($file->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This file has expired and is no longer accessible.',
                ], 410); // Gone
            }
        }

        return $next($request);
    }
}
