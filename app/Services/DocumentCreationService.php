<?php

namespace App\Services;

use App\Contracts\GoogleDrive;
use App\Exceptions\DocumentCreationException;
use App\Exceptions\GoogleDriveException;
use App\Models\Document;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

class DocumentCreationService
{
    public function __construct(private readonly GoogleDrive $googleDrive) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, UploadedFile $file, User $creator): Document
    {
        $temporaryPath = $file->getRealPath();
        $uploadedDriveFileId = null;

        DB::beginTransaction();

        try {
            $publicationMonth = CarbonImmutable::createFromFormat('!Y-m', $data['publish_date']);
            $uploaded = $this->googleDrive->uploadFile($file, $data['file_name'], $publicationMonth);
            $uploadedDriveFileId = $uploaded['id'];

            $document = Document::create([
                'user_id' => $creator->id,
                'source_id' => $data['source_id'],
                'magazine_id' => $data['magazine_id'],
                'document_type_id' => $data['document_type_id'],
                'language_id' => $data['language_id'],
                'category_id' => $data['category_id'],
                'subcategory_id' => $data['subcategory_id'],
                'specialization_id' => $data['specialization_id'] ?? null,
                'country_id' => $data['country_id'] ?? null,
                'submission_id' => $data['submission_id'],
                'title' => $data['file_name'],
                'doi' => $data['doi'],
                'publication_year' => $data['publish_year'] ?? null,
                'publication_date' => $publicationMonth->toDateString(),
                'total_pages' => $data['pages_number'] ?? null,
                'drive_file_id' => $uploaded['id'],
                'drive_file_name' => $uploaded['name'],
                'drive_folder_id' => $uploaded['folder_id'],
                'drive_url' => $uploaded['web_view_link'],
                'original_file_name' => $file->getClientOriginalName(),
                'file_extension' => strtolower($file->getClientOriginalExtension()),
                'mime_type' => $uploaded['mime_type'] ?? $file->getMimeType(),
                'file_size' => $uploaded['size'] ?? $file->getSize(),
                'status' => 'active',
                'is_active' => true,
            ]);

            $authorPivot = collect($data['author_ids'])
                ->values()
                ->mapWithKeys(fn ($authorId, int $index): array => [
                    (int) $authorId => ['author_order' => $index + 1],
                ])
                ->all();
            $document->authors()->attach($authorPivot);

            $contributorPivot = collect($data['contributors'] ?? [])
                ->values()
                ->mapWithKeys(fn (array $contributor, int $index): array => [
                    (int) $contributor['id'] => [
                        'role' => $contributor['role'] ?? null,
                        'contributor_order' => $index + 1,
                    ],
                ])
                ->all();

            if ($contributorPivot !== []) {
                $document->contributors()->attach($contributorPivot);
            }

            DB::commit();

            return $document->load($this->relations());
        } catch (Throwable $exception) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            if ($uploadedDriveFileId !== null) {
                try {
                    $this->googleDrive->deleteFile($uploadedDriveFileId);
                } catch (Throwable $compensationException) {
                    Log::critical('Document creation compensation could not delete the Drive file.', [
                        'drive_file_id' => $uploadedDriveFileId,
                        'exception' => $compensationException,
                    ]);
                }
            }

            Log::error('Document creation failed.', [
                'user_id' => $creator->id,
                'drive_file_id' => $uploadedDriveFileId,
                'exception' => $exception,
            ]);

            if ($exception instanceof GoogleDriveException && $uploadedDriveFileId === null) {
                throw new DocumentCreationException(
                    'The document could not be uploaded to Google Drive.',
                    502,
                    $exception,
                );
            }

            throw new DocumentCreationException(
                'The document could not be saved.',
                500,
                $exception,
            );
        } finally {
            if (is_file($temporaryPath)) {
                File::delete($temporaryPath);
            }
        }
    }

    /**
     * @return list<string>
     */
    private function relations(): array
    {
        return [
            'user:id,name',
            'source:id,name',
            'magazine:id,name',
            'documentType:id,name',
            'languageRecord:id,name,code',
            'category:id,name',
            'subcategory:id,category_id,name',
            'specialization:id,subcategory_id,name',
            'country:id,name,code',
            'authors:id,name',
            'contributors:id,name',
        ];
    }
}
