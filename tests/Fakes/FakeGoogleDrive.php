<?php

namespace Tests\Fakes;

use App\Contracts\GoogleDrive;
use App\Exceptions\GoogleDriveException;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;

class FakeGoogleDrive implements GoogleDrive
{
    public bool $failUpload = false;

    public string $nextFileId = 'fake-drive-file-id';

    /** @var list<string> */
    public array $uploadedFiles = [];

    /** @var list<string> */
    public array $uploadedDisplayNames = [];

    /** @var list<string> */
    public array $deletedFiles = [];

    public function uploadFile(UploadedFile $file, string $displayName, CarbonInterface $folderDate): array
    {
        if ($this->failUpload) {
            throw new GoogleDriveException('Simulated Drive failure.');
        }

        $this->uploadedFiles[] = $file->getClientOriginalName();
        $this->uploadedDisplayNames[] = $displayName;

        return [
            'id' => $this->nextFileId,
            'name' => $this->buildSafeFileName($displayName, $file->getClientOriginalExtension()),
            'folder_id' => 'folder-'.$folderDate->format('Y-m'),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'web_view_link' => 'https://drive.google.com/file/d/'.$this->nextFileId.'/view',
        ];
    }

    public function deleteFile(string $fileId): void
    {
        $this->deletedFiles[] = $fileId;
    }

    public function getFileMetadata(string $fileId): array
    {
        return ['id' => $fileId];
    }

    public function ensureFolder(string $parentFolderId, string $name): string
    {
        return $parentFolderId.'-'.$name;
    }

    public function buildSafeFileName(string $displayName, string $extension): string
    {
        return 'uuid-safe-document.'.strtolower($extension);
    }
}
