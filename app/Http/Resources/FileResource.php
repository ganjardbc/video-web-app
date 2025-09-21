<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'share_id' => $this->share_id,
            'original_name' => $this->original_name,
            'filename' => $this->filename,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'formatted_size' => $this->formatted_size,
            'type' => $this->type,
            'metadata' => $this->metadata,
            'is_public' => $this->is_public,
            'expires_at' => $this->expires_at ? $this->expires_at->toISOString() : null,
            'download_count' => $this->download_count,
            'share_url' => $this->share_url,
            'download_url' => $this->download_url,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Conditional fields
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),

            // Only show file path to the owner
            'path' => $this->when(
                $request->user() && $this->user_id === $request->user()->id,
                $this->path
            ),

            // Additional metadata for images
            'dimensions' => $this->when(
                $this->isImage() && is_array($this->metadata) && array_key_exists('dimensions', $this->metadata),
                $this->metadata['dimensions'] ?? null
            ),

            // Additional metadata for videos
            'duration' => $this->when(
                $this->isVideo() && is_array($this->metadata) && array_key_exists('duration', $this->metadata),
                $this->metadata['duration'] ?? null
            ),

            // File status
            'status' => [
                'is_expired' => $this->isExpired(),
                'is_image' => $this->isImage(),
                'is_video' => $this->isVideo(),
                'extension' => $this->getFileExtension(),
            ],
        ];
    }
}
