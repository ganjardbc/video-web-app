<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('share_id')->unique(); // Public share identifier
            $table->string('original_name'); // Original filename
            $table->string('filename'); // Stored filename
            $table->string('path'); // File path
            $table->string('mime_type'); // File MIME type
            $table->unsignedBigInteger('size'); // File size in bytes
            $table->string('type')->default('image'); // image or video
            $table->json('metadata')->nullable(); // Additional metadata (dimensions, duration, etc.)
            $table->boolean('is_public')->default(true); // Public or private
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Owner (null for anonymous uploads)
            $table->timestamp('expires_at')->nullable(); // Optional expiration
            $table->unsignedInteger('download_count')->default(0); // Track downloads
            $table->timestamps();

            // Indexes for performance
            $table->index('share_id');
            $table->index('user_id');
            $table->index('is_public');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
