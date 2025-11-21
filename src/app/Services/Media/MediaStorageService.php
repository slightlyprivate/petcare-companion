<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service for handling media storage operations.
 *
 * @group Services
 */
class MediaStorageService
{
    public function __construct(private string $disk = 'public') {}

    /**
     * Store an uploaded file on the configured disk.
     *
     * @return array{path: string, url: string, disk: string, context: string, original_name: string, mime_type: string}
     */
    public function store(UploadedFile $file, string $context = 'activities'): array
    {
        $directory = match ($context) {
            'pet_avatars' => 'pets/avatars',
            'activities' => 'activities/media',
            default => 'uploads',
        };

        $path = $file->store($directory, $this->disk);

        return [
            'path' => $path,
            'url' => $this->toPublicUrl($path),
            'disk' => $this->disk,
            'context' => $context,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
        ];
    }

    /**
     * Normalize a media reference for storage (relative path preferred).
     */
    public function normalizeReference(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $localPath = $this->extractLocalPath($value);

        return $localPath ?? trim($value);
    }

    /**
     * Delete a locally stored file if the reference is on the managed disk.
     */
    public function deleteIfLocal(?string $value): void
    {
        $path = $this->extractLocalPath($value);
        if (! $path) {
            return;
        }

        Storage::disk($this->disk)->delete($path);
    }

    /**
     * Convert a media reference into a public-facing URL.
     */
    public function toPublicUrl(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if ($this->isExternalUrl($value)) {
            return $value;
        }

        $relativePath = $this->extractLocalPath($value) ?? ltrim($value, '/');

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($this->disk);

        return $disk->url($relativePath);
    }

    /**
     * Extract the relative storage path if the reference points to the managed disk.
     */
    private function extractLocalPath(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $reference = trim($value);

        if ($this->isExternalUrl($reference)) {
            return null;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($this->disk);
        $diskUrl = rtrim($disk->url(''), '/');
        if ($diskUrl && Str::startsWith($reference, $diskUrl)) {
            return ltrim(Str::after($reference, $diskUrl), '/');
        }

        if (Str::startsWith($reference, '/storage')) {
            return ltrim(Str::after($reference, '/storage'), '/');
        }

        return ltrim($reference, '/');
    }

    private function isExternalUrl(string $value): bool
    {
        return Str::startsWith($value, ['http://', 'https://', '//', 'data:']);
    }
}
