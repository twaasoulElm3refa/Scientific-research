<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Database\Seeders\Concerns\SeedsLookupNames;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    use SeedsLookupNames;

    public function run(): void
    {
        $types = [
            'كتاب - Book',
            'بحث علمي - Scientific research',
            'مقال علمي - Scientific article',
            'ورقة بحثية - Research paper',
            'رسالة ماجستير - Master\'s thesis',
            'رسالة دكتوراه - PhD thesis',
            'رسالة جامعية - University thesis',
            'فصل من كتاب - Chapter from a book',
            'ورقة مؤتمر - Conference paper',
            'وقائع مؤتمر - Conference proceedings',
            'دراسة - Study',
            'تقرير بحثي - Research report',
            'تقرير علمي - Scientific report',
            'ورقة عمل - Worksheet',
            'مراجعة علمية - Scientific review',
            'ورقة سياسات - Policy paper',
            'موجز سياسات - Policy brief',
            'مرجع علمي - Scientific reference',
            'موسوعة - Encyclopedia',
            'دليل - Evidence',
            'عرض تقديمي - Presentation',
            'وثيقة أكاديمية - Academic document',
            'أخرى - Other',
        ];

        foreach ($types as $typeName) {
            $this->seedLookup(DocumentType::class, $typeName);
        }
    }
}
