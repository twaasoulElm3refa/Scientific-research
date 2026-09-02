<?php

namespace Database\Seeders\Data;

final class AcademicPublishingData
{
    /** @return list<string> */
    public static function sources(): array
    {
        return [
            'Elsevier - إلسفير',
            'Springer Nature - سبرينغر نيتشر',
            'Wiley - وايلي',
            'Taylor & Francis - تايلور وفرانسيس',
            'SAGE Publications - منشورات سيج',
            'Oxford University Press - مطبعة جامعة أكسفورد',
            'Cambridge University Press - مطبعة جامعة كامبريدج',
            'IEEE - معهد مهندسي الكهرباء والإلكترونيات',
            'ACM - جمعية آلات الحوسبة',
            'Emerald Publishing - إيميرالد للنشر',
            'MDPI - المعهد متعدد التخصصات للنشر الرقمي',
            'Frontiers - فرونتيرز',
            'De Gruyter - دي غرويتر',
            'Brill - بريل',
            'Nature Portfolio - مجموعة نيتشر',
            'BMJ - المجلة الطبية البريطانية',
            'American Medical Association - الجمعية الطبية الأمريكية',
            'American Psychological Association - الجمعية الأمريكية لعلم النفس',
            'American Chemical Society - الجمعية الكيميائية الأمريكية',
            'Royal Society of Chemistry - الجمعية الملكية للكيمياء',
            'American Physical Society - الجمعية الفيزيائية الأمريكية',
            'Institute of Physics - معهد الفيزياء',
            'MIT Press - مطبعة معهد ماساتشوستس للتكنولوجيا',
            'Harvard University Press - مطبعة جامعة هارفارد',
            'Princeton University Press - مطبعة جامعة برينستون',
            'University of Chicago Press - مطبعة جامعة شيكاغو',
            'Routledge - روتليدج',
            'Palgrave Macmillan - بالغريف ماكميلان',
            'World Scientific - وورلد ساينتيفك',
            'American Association for the Advancement of Science - الجمعية الأمريكية لتقدم العلوم',
            'National Academy of Sciences - الأكاديمية الوطنية للعلوم',
            'Massachusetts Medical Society - جمعية ماساتشوستس الطبية',
            'American Economic Association - الجمعية الاقتصادية الأمريكية',
            'USC Annenberg Press - مطبعة أننبرغ بجامعة جنوب كاليفورنيا',
            'Harvard Law Review Association - جمعية هارفارد لمراجعة القانون',
            'The Yale Law Journal Company - شركة مجلة ييل للقانون',
            'Stanford Law Review - مراجعة ستانفورد للقانون',
            'JMLR, Inc. - مؤسسة مجلة أبحاث تعلم الآلة',
        ];
    }

    /**
     * Journals are mapped only where the publishing relationship is well established.
     *
     * @return array<string, list<string>>
     */
    public static function magazinesBySource(): array
    {
        return [
            'Nature Portfolio - مجموعة نيتشر' => [
                'Nature - نيتشر',
                'Nature Communications - اتصالات نيتشر',
                'Scientific Reports - التقارير العلمية',
                'Nature Medicine - طب نيتشر',
                'Nature Machine Intelligence - ذكاء الآلة من نيتشر',
            ],
            'American Association for the Advancement of Science - الجمعية الأمريكية لتقدم العلوم' => [
                'Science - العلوم',
                'Science Advances - تقدم العلوم',
            ],
            'National Academy of Sciences - الأكاديمية الوطنية للعلوم' => [
                'Proceedings of the National Academy of Sciences - وقائع الأكاديمية الوطنية للعلوم',
            ],
            'Elsevier - إلسفير' => [
                'The Lancet - ذا لانسيت',
                'Artificial Intelligence - الذكاء الاصطناعي',
                'Expert Systems with Applications - النظم الخبيرة وتطبيقاتها',
                'Pattern Recognition - التعرف على الأنماط',
            ],
            'Massachusetts Medical Society - جمعية ماساتشوستس الطبية' => [
                'The New England Journal of Medicine - مجلة نيو إنجلاند الطبية',
            ],
            'American Medical Association - الجمعية الطبية الأمريكية' => [
                'JAMA - مجلة الجمعية الطبية الأمريكية',
            ],
            'BMJ - المجلة الطبية البريطانية' => [
                'BMJ - المجلة الطبية البريطانية',
            ],
            'IEEE - معهد مهندسي الكهرباء والإلكترونيات' => [
                'IEEE Access - الوصول المفتوح لمعهد مهندسي الكهرباء والإلكترونيات',
                'IEEE Transactions on Pattern Analysis and Machine Intelligence - معاملات معهد مهندسي الكهرباء والإلكترونيات في تحليل الأنماط وذكاء الآلة',
            ],
            'ACM - جمعية آلات الحوسبة' => [
                'ACM Computing Surveys - مسوحات الحوسبة لجمعية آلات الحوسبة',
                'Communications of the ACM - اتصالات جمعية آلات الحوسبة',
            ],
            'MIT Press - مطبعة معهد ماساتشوستس للتكنولوجيا' => [
                'International Security - الأمن الدولي',
            ],
            'Oxford University Press - مطبعة جامعة أكسفورد' => [
                'Journal of Communication - مجلة الاتصال',
                'The Quarterly Journal of Economics - المجلة الفصلية للاقتصاد',
                'The Review of Economic Studies - مراجعة الدراسات الاقتصادية',
                'Oxford Journal of Legal Studies - مجلة أكسفورد للدراسات القانونية',
                'Social Forces - القوى الاجتماعية',
                'European Sociological Review - المراجعة الأوروبية لعلم الاجتماع',
            ],
            'SAGE Publications - منشورات سيج' => [
                'Communication Research - بحوث الاتصال',
                'New Media & Society - الإعلام الجديد والمجتمع',
                'Journalism - الصحافة',
                'Journalism & Mass Communication Quarterly - الفصلية للصحافة والاتصال الجماهيري',
                'Media, Culture & Society - الإعلام والثقافة والمجتمع',
                'European Journal of International Relations - المجلة الأوروبية للعلاقات الدولية',
                'Psychological Science - العلوم النفسية',
                'American Sociological Review - المراجعة الأمريكية لعلم الاجتماع',
            ],
            'Taylor & Francis - تايلور وفرانسيس' => [
                'Digital Journalism - الصحافة الرقمية',
                'Political Communication - الاتصال السياسي',
            ],
            'USC Annenberg Press - مطبعة أننبرغ بجامعة جنوب كاليفورنيا' => [
                'International Journal of Communication - المجلة الدولية للاتصال',
            ],
            'Cambridge University Press - مطبعة جامعة كامبريدج' => [
                'American Political Science Review - المراجعة الأمريكية للعلوم السياسية',
                'International Organization - التنظيم الدولي',
                'World Politics - السياسة العالمية',
                'American Journal of International Law - المجلة الأمريكية للقانون الدولي',
            ],
            'Wiley - وايلي' => [
                'American Journal of Political Science - المجلة الأمريكية للعلوم السياسية',
                'Political Psychology - علم النفس السياسي',
                'Econometrica - إيكونومتريكا',
                'Journal of Marriage and Family - مجلة الزواج والأسرة',
            ],
            'American Economic Association - الجمعية الاقتصادية الأمريكية' => [
                'American Economic Review - المراجعة الاقتصادية الأمريكية',
            ],
            'University of Chicago Press - مطبعة جامعة شيكاغو' => [
                'Journal of Political Economy - مجلة الاقتصاد السياسي',
                'American Journal of Sociology - المجلة الأمريكية لعلم الاجتماع',
            ],
            'American Psychological Association - الجمعية الأمريكية لعلم النفس' => [
                'American Psychologist - عالم النفس الأمريكي',
                'Psychological Bulletin - النشرة النفسية',
                'Psychological Review - المراجعة النفسية',
                'Journal of Personality and Social Psychology - مجلة الشخصية وعلم النفس الاجتماعي',
                'Journal of Counseling Psychology - مجلة علم النفس الإرشادي',
            ],
            'Harvard Law Review Association - جمعية هارفارد لمراجعة القانون' => [
                'Harvard Law Review - مراجعة هارفارد للقانون',
            ],
            'The Yale Law Journal Company - شركة مجلة ييل للقانون' => [
                'Yale Law Journal - مجلة ييل للقانون',
            ],
            'Stanford Law Review - مراجعة ستانفورد للقانون' => [
                'Stanford Law Review - مراجعة ستانفورد للقانون',
            ],
            'JMLR, Inc. - مؤسسة مجلة أبحاث تعلم الآلة' => [
                'Journal of Machine Learning Research - مجلة أبحاث تعلم الآلة',
            ],
        ];
    }

    /** @return list<string> */
    public static function independentMagazines(): array
    {
        return [
        ];
    }
}
