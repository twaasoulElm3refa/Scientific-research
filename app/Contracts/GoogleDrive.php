<?php

namespace App\Contracts;

use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;

interface GoogleDrive
{
    /**
     * @return array{id: string, name: string, folder_id: string, mime_type: string|null, size: int|null, web_view_link: string|null}
     */
    public function uploadFile(UploadedFile $file, string $displayName, CarbonInterface $folderDate): array;

    public function deleteFile(string $fileId): void;

    /**
     * @return array<string, mixed>
     */
    public function getFileMetadata(string $fileId): array;

    public function ensureFolder(string $parentFolderId, string $name): string;

    public function buildSafeFileName(string $displayName, string $extension): string;
}
