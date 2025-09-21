<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FileUploadRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FileController extends Controller
{
    /**
     * Display a listing of authenticated user's files.
     */
    public function index(Request $request): JsonResponse
    {
        // $user = $request->user();

        // $query = File::byUser($user->id)->notExpired();
        $query = File::query()->notExpired();

        // Apply filters
        // if ($request->has('type')) {
        //     $query->where('type', $request->type);
        // }

        // if ($request->has('is_public')) {
        //     $query->where('is_public', $request->boolean('is_public'));
        // }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $perPage = min($request->get('per_page', 15), 100);
        $files = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => FileResource::collection($files),
            'meta' => [
                'current_page' => $files->currentPage(),
                'total' => $files->total(),
                'per_page' => $files->perPage(),
                'last_page' => $files->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created file.
     */
    public function store(FileUploadRequest $request): JsonResponse
    {
        try {
            $uploadedFile = $request->file('file');
            $originalName = $uploadedFile->getClientOriginalName();
            $mimeType = $uploadedFile->getMimeType();
            $size = $uploadedFile->getSize();

            // Generate unique filename
            $extension = $uploadedFile->getClientOriginalExtension();
            $filename = Str::uuid() . '.' . $extension;

            // Determine file type
            $type = str_starts_with($mimeType, 'image') ? 'image' : 'video';

            // Store file
            $path = $uploadedFile->storeAs('uploads', $filename, 'public');

            // Get file metadata
            $metadata = $this->getFileMetadata($uploadedFile, $type);

            // Create file record
            $file = File::create([
                'share_id' => File::generateShareId(),
                'original_name' => $originalName,
                'filename' => $filename,
                'path' => $path,
                'mime_type' => $mimeType,
                'size' => $size,
                'type' => $type,
                'metadata' => $metadata,
                'is_public' => $request->boolean('is_public', true),
                'user_id' => $request->user() ? $request->user()->id : null,
                'expires_at' => $request->expires_at,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => new FileResource($file),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'File upload failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified file (private - requires authentication).
     */
    public function show(Request $request, File $file): JsonResponse
    {
        $user = $request->user();

        if (!$file->canBeAccessedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found or access denied',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new FileResource($file),
        ]);
    }

    /**
     * Display the specified file (public - no authentication required).
     */
    public function showPublic(string $shareId): JsonResponse
    {
        $file = File::where('share_id', $shareId)
            ->public()
            ->notExpired()
            ->first();

        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'File not found or not accessible',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new FileResource($file),
        ]);
    }

    /**
     * Update the specified file.
     */
    public function update(Request $request, File $file): JsonResponse
    {
        // $user = $request->user();

        // if (!$file->canBeAccessedBy($user)) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'File not found or access denied',
        //     ], 404);
        // }

        $validator = Validator::make($request->all(), [
            'original_name' => 'string|max:255',
            'is_public' => 'boolean',
            'expires_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $file->update($request->only(['original_name', 'is_public', 'expires_at']));

        return response()->json([
            'success' => true,
            'message' => 'File updated successfully',
            'data' => $file,
        ]);
    }

    /**
     * Remove the specified file.
     */
    public function destroy(Request $request, File $file): JsonResponse
    {
        $user = $request->user();

        if (!$file->canBeAccessedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found or access denied',
            ], 404);
        }

        try {
            // Delete physical file
            Storage::disk('public')->delete($file->path);

            // Delete database record
            $file->delete();

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'File deletion failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle file visibility.
     */
    public function toggleVisibility(Request $request, File $file): JsonResponse
    {
        $file->update(['is_public' => !$file->is_public]);

        return response()->json([
            'success' => true,
            'message' => 'File visibility updated successfully',
            'data' => $file,
        ]);
    }

    /**
     * Download the specified file.
     */
    public function download(string $shareId)
    {
        $file = File::where('share_id', $shareId)
            ->notExpired()
            ->first();

        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'File not found or not accessible',
            ], 404);
        }

        // Check if file is public or user has access
        if (!$file->is_public && !$file->canBeAccessedBy(auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
            ], 403);
        }

        $filePath = Storage::disk('public')->path($file->path);

        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Physical file not found',
            ], 404);
        }

        // Increment download count
        $file->incrementDownloadCount();

        return response()->download($filePath, $file->original_name);
    }

    /**
     * Get file metadata based on type.
     */
    private function getFileMetadata($uploadedFile, string $type): array
    {
        $metadata = [];

        try {
            if ($type === 'image') {
                $imageInfo = @getimagesize($uploadedFile->getPathname());
                if ($imageInfo) {
                    $metadata['width'] = $imageInfo[0];
                    $metadata['height'] = $imageInfo[1];
                    $metadata['dimensions'] = $imageInfo[0] . 'x' . $imageInfo[1];
                }
            }

            // For videos, you might want to use FFMpeg or similar to get duration and dimensions
            // This is a basic implementation
            if ($type === 'video') {
                $metadata['duration'] = null; // Would need video processing library
            }
        } catch (\Exception $e) {
            // If metadata extraction fails, just return empty metadata
            $metadata = [];
        }

        return $metadata;
    }
}
