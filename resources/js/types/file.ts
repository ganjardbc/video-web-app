export interface FilePreview {
    id?: number;
    share_id?: string;
    original_name: string;
    filename?: string;
    mime_type: string;
    size: number;
    formatted_size?: string;
    type: string;
    metadata?: any;
    is_public?: boolean;
    share_url?: string;
    download_url?: string;
    created_at?: string;
    updated_at?: string;
    dimensions?: string;
    status?: {
        is_expired: boolean;
        is_image: boolean;
        is_video: boolean;
        extension: string;
    };
    // Local state for UI
    uploading?: boolean;
    progress?: number;
    error?: string;
    localUrl?: string; // For local preview before upload
    expires_at: string | null;
    download_count: number;
    file_url: string | null;
    extension: string;
    is_image: boolean;
    is_video: boolean;
    is_expired: boolean;
    user: {
        id: number;
        name: string;
        email: string;
    } | null;
}
