<?php

namespace Database\Seeders\Data;

final class AcademicPublishingData
{
    /** @return list<string> */
    public static function sources(): array
    {
        return [
            'Elsevier',
            'Springer Nature',
            'Wiley',
            'Taylor & Francis',
            'SAGE Publications',
            'Oxford University Press',
            'Cambridge University Press',
            'IEEE',
            'ACM',
            'Emerald Publishing',
            'MDPI',
            'Frontiers',
            'De Gruyter',
            'Brill',
            'Nature Portfolio',
            'BMJ',
            'American Medical Association',
            'American Psychological Association',
            'American Chemical Society',
            'Royal Society of Chemistry',
            'American Physical Society',
            'Institute of Physics',
            'MIT Press',
            'Harvard University Press',
            'Princeton University Press',
            'University of Chicago Press',
            'Routledge',
            'Palgrave Macmillan',
            'World Scientific',
            'American Association for the Advancement of Science',
            'National Academy of Sciences',
            'Massachusetts Medical Society',
            'American Economic Association',
            'USC Annenberg Press',
            'Harvard Law Review Association',
            'The Yale Law Journal Company',
            'Stanford Law Review',
            'JMLR, Inc.',
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
            'Nature Portfolio' => [
                'Nature',
                'Nature Communications',
                'Scientific Reports',
                'Nature Medicine',
                'Nature Machine Intelligence',
            ],
            'American Association for the Advancement of Science' => [
                'Science',
                'Science Advances',
            ],
            'National Academy of Sciences' => [
                'Proceedings of the National Academy of Sciences',
            ],
            'Elsevier' => [
                'The Lancet',
                'Artificial Intelligence',
                'Expert Systems with Applications',
                'Pattern Recognition',
            ],
            'Massachusetts Medical Society' => [
                'The New England Journal of Medicine',
            ],
            'American Medical Association' => [
                'JAMA',
            ],
            'BMJ' => [
                'BMJ',
            ],
            'IEEE' => [
                'IEEE Access',
                'IEEE Transactions on Pattern Analysis and Machine Intelligence',
            ],
            'ACM' => [
                'ACM Computing Surveys',
                'Communications of the ACM',
            ],
            'MIT Press' => [
                'International Security',
            ],
            'Oxford University Press' => [
                'Journal of Communication',
                'The Quarterly Journal of Economics',
                'The Review of Economic Studies',
                'Oxford Journal of Legal Studies',
                'Social Forces',
                'European Sociological Review',
            ],
            'SAGE Publications' => [
                'Communication Research',
                'New Media & Society',
                'Journalism',
                'Journalism & Mass Communication Quarterly',
                'Media, Culture & Society',
                'European Journal of International Relations',
                'Psychological Science',
                'American Sociological Review',
            ],
            'Taylor & Francis' => [
                'Digital Journalism',
                'Political Communication',
            ],
            'USC Annenberg Press' => [
                'International Journal of Communication',
            ],
            'Cambridge University Press' => [
                'American Political Science Review',
                'International Organization',
                'World Politics',
                'American Journal of International Law',
            ],
            'Wiley' => [
                'American Journal of Political Science',
                'Political Psychology',
                'Econometrica',
                'Journal of Marriage and Family',
            ],
            'American Economic Association' => [
                'American Economic Review',
            ],
            'University of Chicago Press' => [
                'Journal of Political Economy',
                'American Journal of Sociology',
            ],
            'American Psychological Association' => [
                'American Psychologist',
                'Psychological Bulletin',
                'Psychological Review',
                'Journal of Personality and Social Psychology',
                'Journal of Counseling Psychology',
            ],
            'Harvard Law Review Association' => [
                'Harvard Law Review',
            ],
            'The Yale Law Journal Company' => [
                'Yale Law Journal',
            ],
            'Stanford Law Review' => [
                'Stanford Law Review',
            ],
            'JMLR, Inc.' => [
                'Journal of Machine Learning Research',
            ],
        ];
    }

    /** @return list<string> */
    public static function independentMagazines(): array
    {
        return [];
    }
}
