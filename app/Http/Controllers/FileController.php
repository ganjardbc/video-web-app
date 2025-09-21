<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Display a listing of the files.
     */
    public function index(): Response
    {
        return Inertia::render('files/List');
    }

    /**
     * Show the form for creating a new file.
     */
    public function create(): Response
    {
        return Inertia::render('files/Create');
    }

    /**
     * Display the specified file.
     */
    public function show(Request $request, File $file): Response
    {
        $user = $request->user();

        // Check if user can access this file
        if (!$file->canBeAccessedBy($user)) {
            abort(404, 'File not found or access denied');
        }

        // Load the file with user relationship
        $file->load('user');

        // Get file URL for preview
        $fileUrl = null;
        if ($file->isImage() || $file->isVideo()) {
            $fileUrl = Storage::disk('public')->url($file->path);
        }

        return Inertia::render('files/Detail', [
            'file' => [
                'id' => $file->id,
                'share_id' => $file->share_id,
                'original_name' => $file->original_name,
                'filename' => $file->filename,
                'mime_type' => $file->mime_type,
                'size' => $file->size,
                'formatted_size' => $file->formatted_size,
                'type' => $file->type,
                'metadata' => $file->metadata,
                'is_public' => $file->is_public,
                'expires_at' => $file->expires_at,
                'download_count' => $file->download_count,
                'created_at' => $file->created_at,
                'updated_at' => $file->updated_at,
                'share_url' => $file->share_url,
                'download_url' => $file->download_url,
                'file_url' => $fileUrl,
                'extension' => $file->getFileExtension(),
                'is_image' => $file->isImage(),
                'is_video' => $file->isVideo(),
                'is_expired' => $file->isExpired(),
                'user' => $file->user ? [
                    'id' => $file->user->id,
                    'name' => $file->user->name,
                    'email' => $file->user->email,
                ] : null,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified file.
     */
    public function edit(Request $request, File $file): Response
    {
        $user = $request->user();

        if (!$file->canBeAccessedBy($user)) {
            abort(404, 'File not found or access denied');
        }

        return Inertia::render('files/Edit', [
            'file' => [
                'id' => $file->id,
                'share_id' => $file->share_id,
                'original_name' => $file->original_name,
                'mime_type' => $file->mime_type,
                'formatted_size' => $file->formatted_size,
                'type' => $file->type,
                'is_public' => $file->is_public,
                'expires_at' => $file->expires_at,
                'created_at' => $file->created_at,
            ],
        ]);
    }

    /**
     * Update the specified file.
     */
    public function update(Request $request, File $file): RedirectResponse
    {
        $user = $request->user();

        if (!$file->canBeAccessedBy($user)) {
            abort(404, 'File not found or access denied');
        }

        $validated = $request->validate([
            'original_name' => ['required', 'string', 'max:255'],
            'is_public' => ['boolean'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $file->update($validated);

        return redirect()->route('files.show', $file)
            ->with('success', 'File updated successfully.');
    }

    /**
     * Remove the specified file from storage.
     */
    public function destroy(Request $request, File $file): RedirectResponse
    {
        $user = $request->user();

        if (!$file->canBeAccessedBy($user)) {
            abort(404, 'File not found or access denied');
        }

        try {
            // Delete physical file
            Storage::disk('public')->delete($file->path);

            // Delete database record
            $file->delete();

            return redirect()->route('files.index')
                ->with('success', 'File deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->route('files.show', $file)
                ->with('error', 'File deletion failed: ' . $e->getMessage());
        }
    }

    /**
     * Toggle file visibility.
     */
    public function toggleVisibility(Request $request, File $file): RedirectResponse
    {
        $user = $request->user();

        if (!$file->canBeAccessedBy($user)) {
            abort(404, 'File not found or access denied');
        }

        $file->update(['is_public' => !$file->is_public]);

        $status = $file->is_public ? 'public' : 'private';

        return redirect()->route('files.show', $file)
            ->with('success', "File is now {$status}.");
    }

    /**
     * Copy share link to clipboard (returns JSON for AJAX).
     */
    public function getShareLink(Request $request, File $file)
    {
        $user = $request->user();

        if (!$file->canBeAccessedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found or access denied',
            ], 404);
        }

        if (!$file->is_public) {
            return response()->json([
                'success' => false,
                'message' => 'File must be public to generate share link',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'share_url' => $file->share_url,
        ]);
    }
}
