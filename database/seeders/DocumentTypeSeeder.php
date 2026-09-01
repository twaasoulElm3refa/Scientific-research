<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $types = [
            ['name' => 'كتاب', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'بحث علمي', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'مقال علمي', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ورقة بحثية', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'رسالة ماجستير', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'رسالة دكتوراه', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'رسالة جامعية', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'فصل من كتاب', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ورقة مؤتمر', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'وقائع مؤتمر', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'دراسة', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'تقرير بحثي', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'تقرير علمي', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ورقة عمل', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'مراجعة علمية', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ورقة سياسات', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'موجز سياسات', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'مرجع علمي', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'موسوعة', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'دليل', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'عرض تقديمي', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'وثيقة أكاديمية', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'أخرى', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('document_types')->upsert(
            $types,
            ['name'],
            ['is_active', 'updated_at']
        );
    }
}
