<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LicenseTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $licenses = [
            ['code' => 'cc_by', 'name_ar' => 'مسموح استخدامه مع نسب المصدر - Attribution', 'name_en' => 'Attribution - مسموح استخدامه مع نسب المصدر'],
            ['code' => 'cc_by_sa', 'name_ar' => 'مسموح مع النسب، وأي عمل مشتق يكون بنفس الرخصة - Attribution ShareAlike', 'name_en' => 'Attribution ShareAlike - مسموح مع النسب، وأي عمل مشتق يكون بنفس الرخصة'],
            ['code' => 'cc_by_nc', 'name_ar' => 'مسموح لغير الاستخدام التجاري - Attribution NonCommercial', 'name_en' => 'Attribution NonCommercial - مسموح لغير الاستخدام التجاري'],
            ['code' => 'cc_by_nc_sa', 'name_ar' => 'غير تجاري + نفس الرخصة - Attribution NonCommercial ShareAlike', 'name_en' => 'Attribution NonCommercial ShareAlike - غير تجاري + نفس الرخصة'],
            ['code' => 'cc_by_nd', 'name_ar' => 'مسموح بالنشر دون تعديل - Attribution NoDerivatives', 'name_en' => 'Attribution NoDerivatives - مسموح بالنشر دون تعديل'],
            ['code' => 'cc_by_nc_nd', 'name_ar' => 'غير تجاري ودون تعديل - Attribution NonCommercial NoDerivatives', 'name_en' => 'Attribution NonCommercial NoDerivatives - غير تجاري ودون تعديل'],
            ['code' => 'cc0', 'name_ar' => 'تنازل شبه كامل عن الحقوق - CC0 Public Domain Dedication', 'name_en' => 'CC0 Public Domain Dedication - تنازل شبه كامل عن الحقوق'],
            ['code' => 'public_domain', 'name_ar' => 'ملكية عامة - Public Domain', 'name_en' => 'Public Domain - ملكية عامة'],
            ['code' => 'publisher_license', 'name_ar' => 'رخصة خاصة من الناشر - Publisher License', 'name_en' => 'Publisher License - رخصة خاصة من الناشر'],
            ['code' => 'institutional_license', 'name_ar' => 'رخصة جامعة أو مؤسسة - Institutional License', 'name_en' => 'Institutional License - رخصة جامعة أو مؤسسة'],
            ['code' => 'subscription_only', 'name_ar' => 'متاح باشتراك فقط - Subscription Only', 'name_en' => 'Subscription Only - متاح باشتراك فقط'],
            ['code' => 'all_rights_reserved', 'name_ar' => 'جميع الحقوق محفوظة - All Rights Reserved', 'name_en' => 'All Rights Reserved - جميع الحقوق محفوظة'],
            ['code' => 'unknown', 'name_ar' => 'غير معروف - Unknown', 'name_en' => 'Unknown - غير معروف'],
        ];

        DB::table('license_types')->upsert(
            array_map(fn (array $license): array => $license + [
                'description_ar' => null,
                'description_en' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ], $licenses),
            ['code'],
            ['name_ar', 'name_en', 'description_ar', 'description_en', 'updated_at']
        );
    }
}
