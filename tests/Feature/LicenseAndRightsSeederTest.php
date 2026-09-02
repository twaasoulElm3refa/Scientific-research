<?php

namespace Tests\Feature;

use Database\Seeders\LicenseTypeSeeder;
use Database\Seeders\RightsStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseAndRightsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_license_and_rights_seeders_are_complete_and_idempotent(): void
    {
        $this->seed([LicenseTypeSeeder::class, RightsStatusSeeder::class]);
        $this->seed([LicenseTypeSeeder::class, RightsStatusSeeder::class]);

        $this->assertDatabaseCount('license_types', 13);
        $this->assertDatabaseCount('rights_statuses', 8);
        $this->assertDatabaseHas('license_types', [
            'code' => 'cc_by_sa',
            'name_ar' => 'مسموح مع النسب، وأي عمل مشتق يكون بنفس الرخصة',
            'name_en' => 'Attribution ShareAlike',
        ]);
        $this->assertDatabaseHas('rights_statuses', [
            'code' => 'restricted',
            'name_ar' => 'متاح جزئيًا أو يتطلب دخول',
            'name_en' => 'Restricted Access',
        ]);
    }
}
