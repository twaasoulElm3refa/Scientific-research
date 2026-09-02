<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RightsStatusSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $statuses = [
            ['code' => 'open_access', 'name_ar' => 'متاح قانونيًا للجمهور - Open Access', 'name_en' => 'Open Access - متاح قانونيًا للجمهور'],
            ['code' => 'public_domain', 'name_ar' => 'ملكية عامة - Public Domain', 'name_en' => 'Public Domain - ملكية عامة'],
            ['code' => 'user_private', 'name_ar' => 'ملف خاص رفعه المستخدم - User Private', 'name_en' => 'User Private - ملف خاص رفعه المستخدم'],
            ['code' => 'licensed', 'name_ar' => 'عندك إذن أو اشتراك - Licensed', 'name_en' => 'Licensed - عندك إذن أو اشتراك'],
            ['code' => 'copyrighted', 'name_ar' => 'محمي بحقوق نشر - Copyrighted', 'name_en' => 'Copyrighted - محمي بحقوق نشر'],
            ['code' => 'unknown', 'name_ar' => 'غير معروف الحقوق - Unknown', 'name_en' => 'Unknown - غير معروف الحقوق'],
            ['code' => 'restricted', 'name_ar' => 'متاح جزئيًا أو يتطلب دخول - Restricted Access', 'name_en' => 'Restricted Access - متاح جزئيًا أو يتطلب دخول'],
            ['code' => 'internal_only', 'name_ar' => 'للاستخدام الداخلي فقط - Internal Only', 'name_en' => 'Internal Only - للاستخدام الداخلي فقط'],
        ];

        DB::table('rights_statuses')->upsert(
            array_map(fn (array $status): array => $status + [
                'description_ar' => null,
                'description_en' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ], $statuses),
            ['code'],
            ['name_ar', 'name_en', 'description_ar', 'description_en', 'updated_at']
        );
    }
}
