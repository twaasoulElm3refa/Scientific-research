<?php

namespace Tests\Unit;

use App\Services\GoogleDriveOAuthService;
use App\Services\GoogleDriveService;
use PHPUnit\Framework\TestCase;

class GoogleDriveServiceTest extends TestCase
{
    public function test_storage_file_names_are_unique_and_path_safe(): void
    {
        $service = new GoogleDriveService(new GoogleDriveOAuthService);
        $firstName = $service->buildSafeFileName('../../Unsafe Document', 'PDF');
        $secondName = $service->buildSafeFileName('../../Unsafe Document', 'PDF');

        $this->assertNotSame($firstName, $secondName);
        $this->assertStringEndsWith('-Unsafe-Document.pdf', $firstName);
        $this->assertStringNotContainsString('..', $firstName);
        $this->assertStringNotContainsString('/', $firstName);
        $this->assertStringNotContainsString('\\', $firstName);
    }
}
