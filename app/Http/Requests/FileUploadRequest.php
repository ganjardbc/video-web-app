<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class FileUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow both authenticated and anonymous uploads
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                File::types(['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'mov', 'avi'])
                    ->max(100 * 1024), // 100MB
            ],
            'is_public' => 'boolean',
            'expires_at' => 'nullable|date|after:now|before:' . now()->addYear(),
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Please select a file to upload.',
            'file.file' => 'The uploaded file is not valid.',
            'file.max' => 'The file size cannot exceed 100MB.',
            'expires_at.after' => 'The expiration date must be in the future.',
            'expires_at.before' => 'The expiration date cannot be more than 1 year from now.',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'file' => 'uploaded file',
            'is_public' => 'visibility',
            'expires_at' => 'expiration date',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional custom validation logic
            if ($this->hasFile('file')) {
                $file = $this->file('file');

                // Check file integrity
                if (!$file->isValid()) {
                    $validator->errors()->add('file', 'The uploaded file is corrupted or invalid.');
                }

                // Additional MIME type validation
                $allowedMimes = [
                    'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                    'video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo'
                ];

                if (!in_array($file->getMimeType(), $allowedMimes)) {
                    $validator->errors()->add('file', 'The file type is not supported.');
                }
            }
        });
    }
}
