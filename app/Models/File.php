<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'share_id',
        'original_name',
        'filename',
        'path',
        'mime_type',
        'size',
        'type',
        'metadata',
        'is_public',
        'user_id',
        'expires_at',
        'download_count',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_public' => 'boolean',
        'expires_at' => 'datetime',
        'size' => 'integer',
        'download_count' => 'integer',
    ];

    protected $hidden = [
        'path', // Don't expose actual file path in API responses
    ];

    protected $appends = [
        'share_url',
        'download_url',
        'formatted_size',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getShareUrlAttribute(): string
    {
        return url("/share/{$this->share_id}");
    }

    public function getDownloadUrlAttribute(): string
    {
        return url("/api/files/download/{$this->share_id}");
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        if ($bytes === 0) return '0 Bytes';

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopePrivate($query)
    {
        return $query->where('is_public', false);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Methods
    public static function generateShareId(): string
    {
        do {
            $shareId = Str::random(12);
        } while (self::where('share_id', $shareId)->exists());

        return $shareId;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function canBeAccessedBy(?User $user = null): bool
    {
        if ($this->isExpired()) {
            return false;
        }

        if ($this->is_public) {
            return true;
        }

        return $user && $this->user_id === $user->id;
    }

    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    public function getFileExtension(): string
    {
        return pathinfo($this->original_name, PATHINFO_EXTENSION);
    }

    public function isImage(): bool
    {
        return $this->type === 'image' || str_starts_with($this->mime_type, 'image/');
    }

    public function isVideo(): bool
    {
        return $this->type === 'video' || str_starts_with($this->mime_type, 'video/');
    }
}
