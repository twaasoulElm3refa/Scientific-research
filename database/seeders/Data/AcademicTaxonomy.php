<?php

namespace Database\Seeders\Data;

final class AcademicTaxonomy
{
    /**
     * @return array<string, array<string, list<string>>>
     */
    public static function all(): array
    {
        return [
            'الإعلام والاتصال - Media and communication' => [
                'الصحافة - Journalism' => [
                    'الصحافة الرقمية - Digital journalism',
                    'الصحافة الاستقصائية - Investigative journalism',
                    'صحافة البيانات - Data journalism',
                    'الصحافة الاقتصادية - Economic press',
                    'الصحافة السياسية - Political journalism',
                    'الصحافة العلمية - Scientific journalism',
                ],
                'الإعلام الرقمي - Digital media' => [
                    'وسائل التواصل الاجتماعي - Social media',
                    'صناعة المحتوى الرقمي - Digital content industry',
                    'الإعلام والذكاء الاصطناعي - Media and artificial intelligence',
                    'الإعلام التفاعلي - Interactive media',
                    'المنصات الرقمية - Digital platforms',
                ],
                'الاتصال السياسي - Political communication' => [
                    'الحملات الانتخابية - Election campaigns',
                    'الرأي العام السياسي - Political public opinion',
                    'الدعاية السياسية - Political propaganda',
                ],
                'العلاقات العامة - Public relations' => [
                    'إدارة السمعة - Reputation management',
                    'اتصال الأزمات - Crisis communication',
                    'العلاقات العامة الرقمية - Digital PR',
                ],
                'الإذاعة والتلفزيون - Radio and television' => [
                    'الإنتاج الإذاعي - Radio production',
                    'الإنتاج التلفزيوني - Television production',
                    'البث الرقمي - Digital broadcasting',
                ],
                'الإعلان - Advertising' => [
                    'الإعلان الرقمي - Digital advertising',
                    'التخطيط الإعلاني - Advertising planning',
                    'سلوك المستهلك الإعلاني - Advertising consumer behaviour',
                ],
                'دراسات الجمهور - Audience studies' => [
                    'قياس الجمهور - Audience measurement',
                    'استخدامات وسائل الإعلام - Uses of media',
                    'تأثيرات وسائل الإعلام - Media influences',
                ],
                'الاتصال المؤسسي - Corporate communication' => [
                    'الاتصال الداخلي - Internal communication',
                    'الهوية المؤسسية - Corporate identity',
                    'المسؤولية الاجتماعية للمؤسسات - Corporate social responsibility',
                ],
            ],
            'العلوم السياسية - Political science' => [
                'النظم السياسية - Political systems' => [
                    'النظم المقارنة - Comparative systems',
                    'التحول الديمقراطي - Democratic transformation',
                    'الحوكمة السياسية - Political governance',
                ],
                'الفكر السياسي - Political thought' => [
                    'الفكر السياسي الحديث - Modern political thought',
                    'الفكر السياسي الإسلامي - Islamic political thought',
                    'النظرية السياسية - Political theory',
                ],
                'السياسات العامة - Public policies' => [
                    'تحليل السياسات العامة - Public policy analysis',
                    'تقييم السياسات - Policy evaluation',
                    'الحوكمة العامة - Public governance',
                ],
                'الدراسات الأمنية - Security studies' => [
                    'الأمن القومي - National security',
                    'الأمن الإقليمي - Regional security',
                    'الإرهاب والتطرف - Terrorism and extremism',
                    'الأمن السيبراني - Cyber security',
                    'الدراسات الاستراتيجية - Strategic studies',
                ],
                'الأحزاب والانتخابات - Parties and elections' => [
                    'النظم الانتخابية - Electoral systems',
                    'السلوك الانتخابي - Electoral behaviour',
                    'التنظيمات الحزبية - Party organizations',
                ],
                'الحركات السياسية - Political movements' => [
                    'الحركات الاجتماعية - Social movements',
                    'الحركات الاحتجاجية - Protest movements',
                    'المشاركة السياسية - Political participation',
                ],
            ],
            'العلاقات الدولية - International relations' => [
                'السياسة الخارجية - Foreign policy' => [
                    'تحليل السياسة الخارجية - Foreign policy analysis',
                    'السياسة الخارجية الأمريكية - American foreign policy',
                    'السياسة الخارجية الأوروبية - European foreign policy',
                    'السياسة الخارجية العربية - Arab foreign policy',
                    'السياسة الخارجية التركية - Turkish foreign policy',
                    'السياسة الخارجية الإيرانية - Iranian foreign policy',
                ],
                'المنظمات الدولية - International organizations' => [
                    'الأمم المتحدة - United Nations',
                    'المنظمات الإقليمية - Regional organizations',
                    'الحوكمة العالمية - Global Governance',
                ],
                'الصراعات الدولية - International conflicts' => [
                    'تسوية النزاعات - Conflict settlement',
                    'دراسات الحرب والسلام - War and Peace Studies',
                    'بناء السلام - Peacebuilding',
                ],
                'الدبلوماسية - Diplomacy' => [
                    'الدبلوماسية العامة - Public diplomacy',
                    'الدبلوماسية الرقمية - Digital diplomacy',
                    'التفاوض الدولي - International negotiation',
                ],
                'الأمن الدولي - International security' => [
                    'الحد من التسلح - Arms control',
                    'الانتشار النووي - Nuclear proliferation',
                    'الأمن الجماعي - Collective security',
                ],
                'العلاقات الإقليمية - Regional relations' => [
                    'دراسات الشرق الأوسط - Middle East Studies',
                    'الدراسات الأوروبية - European Studies',
                    'الدراسات الآسيوية - Asian Studies',
                ],
            ],
            'الاقتصاد - Economy' => [
                'الاقتصاد الكلي - Macroeconomics' => [
                    'النمو الاقتصادي - Economic growth',
                    'السياسة النقدية - Monetary policy',
                    'السياسة المالية - Fiscal policy',
                ],
                'الاقتصاد الجزئي - Microeconomics' => [
                    'نظرية الأسعار - Price theory',
                    'اقتصاديات الرفاه - Welfare economics',
                    'التنظيم الصناعي - Industrial organization',
                ],
                'الاقتصاد الدولي - International economics' => [
                    'التجارة الدولية - International trade',
                    'التمويل الدولي - International finance',
                    'التكامل الاقتصادي - Economic integration',
                ],
                'الاقتصاد السياسي - Political economy' => [
                    'الاقتصاد السياسي الدولي - International political economy',
                    'اقتصاديات المؤسسات - Institutional economics',
                    'الاقتصاد السياسي المقارن - Comparative political economy',
                ],
                'الاقتصاد التنموي - Development economics' => [
                    'التنمية المستدامة - Sustainable development',
                    'اقتصاديات الفقر - Economics of poverty',
                    'اقتصاديات العمل - Labor economics',
                ],
                'الاقتصاد الرقمي - Digital economy' => [
                    'اقتصاد المنصات - Platform economy',
                    'التجارة الإلكترونية - Ecommerce',
                    'الأصول الرقمية - Digital assets',
                ],
                'التمويل - Finance' => [
                    'الأسواق المالية - Financial markets',
                    'إدارة المخاطر المالية - Financial risk management',
                    'التمويل السلوكي - Behavioral finance',
                ],
            ],
            'القانون - Law' => [
                'القانون الدولي - International law' => [
                    'القانون الدولي العام - Public international law',
                    'القانون الدولي الخاص - Private international law',
                    'القانون الإنساني الدولي - International humanitarian law',
                ],
                'القانون الدستوري - Constitutional law' => [
                    'الرقابة الدستورية - Constitutional oversight',
                    'النظم الدستورية - Constitutional systems',
                    'الحريات العامة - Public freedoms',
                ],
                'القانون الإداري - Administrative law' => [
                    'القضاء الإداري - Administrative judiciary',
                    'العقود الإدارية - Administrative contracts',
                    'الوظيفة العامة - Public function',
                ],
                'القانون المدني - Civil law' => [
                    'قانون الالتزامات - Law of obligations',
                    'المسؤولية المدنية - Civil liability',
                    'قانون الأسرة - Family law',
                ],
                'القانون الجنائي - Criminal law' => [
                    'السياسة الجنائية - Criminal policy',
                    'علم الإجرام - Criminology',
                    'الإجراءات الجنائية - Criminal procedures',
                ],
                'القانون التجاري - Commercial law' => [
                    'قانون الشركات - Corporate law',
                    'التحكيم التجاري - Commercial arbitration',
                    'الملكية الفكرية - Intellectual property',
                ],
                'القانون الرقمي - Digital law' => [
                    'حماية البيانات - Data protection',
                    'الجرائم الإلكترونية - Cyber crimes',
                    'تنظيم الذكاء الاصطناعي - Regulating artificial intelligence',
                ],
                'حقوق الإنسان - Human rights' => [
                    'الآليات الدولية لحقوق الإنسان - International human rights mechanisms',
                    'حقوق الأقليات - Minority rights',
                    'العدالة الانتقالية - Transitional justice',
                ],
            ],
            'علم النفس - Psychology' => [
                'علم النفس السريري - Clinical psychology' => [
                    'التشخيص النفسي - Psychological diagnosis',
                    'العلاج النفسي - Psychotherapy',
                    'الصحة النفسية - Mental health',
                ],
                'علم النفس الاجتماعي - Social psychology' => [
                    'الاتجاهات الاجتماعية - Social trends',
                    'ديناميات الجماعة - Group dynamics',
                    'الهوية الاجتماعية - Social identity',
                ],
                'علم النفس التربوي - Educational psychology' => [
                    'الدافعية للتعلم - Motivation to learn',
                    'صعوبات التعلم - Learning difficulties',
                    'القياس النفسي التربوي - Educational psychometrics',
                ],
                'الإرشاد النفسي - Psychological counseling' => [
                    'الإرشاد الأسري - Family counseling',
                    'الإرشاد المهني - Professional guidance',
                    'الإرشاد المدرسي - School guidance',
                ],
                'علم النفس المعرفي - Cognitive psychology' => [
                    'الذاكرة والانتباه - Memory and attention',
                    'اتخاذ القرار - Decision making',
                    'الإدراك المعرفي - Cognitive perception',
                ],
                'علم النفس الصناعي والتنظيمي - Industrial and organizational psychology' => [
                    'الرضا الوظيفي - Job satisfaction',
                    'القيادة التنظيمية - Organizational leadership',
                    'الصحة النفسية المهنية - Occupational mental health',
                ],
            ],
            'علم الاجتماع والعلوم الاجتماعية - Sociology and social sciences' => [
                'علم الاجتماع العام - General sociology' => [
                    'النظرية الاجتماعية - Social theory',
                    'التغير الاجتماعي - Social change',
                    'البناء الاجتماعي - Social construction',
                ],
                'علم اجتماع الأسرة - Family sociology' => [
                    'الزواج والأسرة - Marriage and family',
                    'الطفولة والمراهقة - Childhood and adolescence',
                    'التحولات الأسرية - Family transitions',
                ],
                'علم الاجتماع السياسي - Political sociology' => [
                    'المجتمع المدني - Civil society',
                    'السلطة الاجتماعية - Social power',
                    'الثقافة السياسية - Political culture',
                ],
                'علم الاجتماع الاقتصادي - Economic sociology' => [
                    'الأسواق والمجتمع - Markets and society',
                    'العمل والتنظيم - Work and organization',
                    'عدم المساواة - Inequality',
                ],
                'الدراسات السكانية - Population studies' => [
                    'الديموغرافيا - Demographics',
                    'الهجرة - Immigration',
                    'التحضر - Urbanization',
                ],
                'الخدمة الاجتماعية - Social service' => [
                    'تنمية المجتمع - Community development',
                    'الرعاية الاجتماعية - Social care',
                    'السياسة الاجتماعية - Social policy',
                ],
            ],
            'التربية - Education' => [
                'المناهج وطرق التدريس - Curricula and teaching methods' => [
                    'تصميم المناهج - Curriculum design',
                    'استراتيجيات التدريس - Teaching strategies',
                    'تقويم المناهج - Curriculum evaluation',
                ],
                'تكنولوجيا التعليم - Educational technology' => [
                    'التعلم الإلكتروني - Elearning',
                    'تصميم التعليم - Educational design',
                    'الواقع الممتد في التعليم - Extended reality in education',
                ],
                'الإدارة التربوية - Educational administration' => [
                    'القيادة المدرسية - School leadership',
                    'التخطيط التربوي - Educational planning',
                    'جودة التعليم - Quality of education',
                ],
                'أصول التربية - Fundamentals of education' => [
                    'فلسفة التربية - Philosophy of education',
                    'اجتماعيات التربية - Sociology of education',
                    'سياسات التعليم - Education policies',
                ],
                'التربية الخاصة - Special education' => [
                    'اضطراب طيف التوحد - Autism spectrum disorder',
                    'الإعاقة السمعية والبصرية - Hearing and visual impairment',
                    'الموهبة والتفوق - Talent and excellence',
                ],
                'القياس والتقويم - Measurement and evaluation' => [
                    'بناء الاختبارات - Build tests',
                    'التقويم التكويني - Formative calendar',
                    'تحليل نتائج التعلم - Analysis of learning outcomes',
                ],
            ],
            'التاريخ - History' => [
                'التاريخ القديم - Ancient history' => [
                    'تاريخ الشرق الأدنى القديم - History of the Ancient Near East',
                    'التاريخ اليوناني والروماني - Greek and Roman history',
                    'الآثار والحضارات - Antiquities and civilizations',
                ],
                'التاريخ الإسلامي - Islamic history' => [
                    'السيرة والتاريخ المبكر - Prophetic biography and early history',
                    'تاريخ الدول الإسلامية - History of Islamic countries',
                    'التاريخ الحضاري الإسلامي - Islamic civilizational history',
                ],
                'التاريخ الحديث - Modern history' => [
                    'التاريخ العثماني - Ottoman history',
                    'تاريخ الاستعمار - History of colonialism',
                    'النهضة العربية - Arab Renaissance',
                ],
                'التاريخ المعاصر - Contemporary history' => [
                    'تاريخ العالم المعاصر - Contemporary world history',
                    'التاريخ العربي المعاصر - Contemporary Arab history',
                    'تاريخ الحركات الوطنية - History of national movements',
                ],
                'التاريخ الاجتماعي والثقافي - Social and cultural history' => [
                    'التاريخ الشفوي - Oral history',
                    'تاريخ الحياة اليومية - History of daily life',
                    'تاريخ الأفكار - History of ideas',
                ],
            ],
            'الدراسات الدينية - Religious studies' => [
                'الدراسات الإسلامية - Islamic studies' => [
                    'العقيدة والفكر الإسلامي - Islamic belief and thought',
                    'السيرة النبوية - Prophetic biography',
                    'الدراسات القرآنية - Quranic studies',
                ],
                'الفقه وأصوله - Jurisprudence and its principles' => [
                    'الفقه المقارن - Comparative jurisprudence',
                    'أصول الفقه - Principles of jurisprudence',
                    'فقه النوازل - Contemporary issues jurisprudence',
                ],
                'الحديث وعلومه - Hadith and its sciences' => [
                    'مصطلح الحديث - Hadith terminology',
                    'نقد الحديث - Criticism of the hadith',
                    'شروح الحديث - Explanations of the hadith',
                ],
                'مقارنة الأديان - Comparative religions' => [
                    'الأديان الإبراهيمية - Abrahamic religions',
                    'أديان آسيا - Religions of Asia',
                    'الحوار بين الأديان - Interfaith dialogue',
                ],
                'علم الاجتماع الديني - Sociology of religion' => [
                    'الدين والمجتمع - Religion and society',
                    'الحركات الدينية - Religious movements',
                    'التدين المعاصر - Contemporary religiosity',
                ],
            ],
            'الفلسفة - Philosophy' => [
                'الفلسفة القديمة - Ancient philosophy' => [
                    'الفلسفة اليونانية - Greek philosophy',
                    'الفلسفة الهلنستية - Hellenistic philosophy',
                    'فلسفة العصور القديمة - Philosophy of antiquity',
                ],
                'الفلسفة الإسلامية - Islamic philosophy' => [
                    'علم الكلام - Theology',
                    'الفلسفة المشائية - Peripatetic philosophy',
                    'التصوف الفلسفي - Philosophical mysticism',
                ],
                'الفلسفة الحديثة والمعاصرة - Modern and contemporary philosophy' => [
                    'العقلانية والتجريبية - Rationalism and empiricism',
                    'الوجودية - Existentialism',
                    'فلسفة ما بعد الحداثة - Postmodern philosophy',
                ],
                'الأخلاق - Ethics' => [
                    'الأخلاق التطبيقية - Applied ethics',
                    'أخلاقيات الطب - Medical ethics',
                    'أخلاقيات التقنية - Technology ethics',
                ],
                'المنطق وفلسفة العلم - Logic and philosophy of science' => [
                    'المنطق الرمزي - Symbolic logic',
                    'مناهج العلوم - Science curricula',
                    'فلسفة المعرفة - Philosophy of knowledge',
                ],
            ],
            'اللغات والآداب - Languages and Literatures' => [
                'اللغة العربية - Arabic language' => [
                    'النحو والصرف - Grammar and morphology',
                    'اللسانيات العربية - Arabic linguistics',
                    'البلاغة العربية - Arabic rhetoric',
                ],
                'الأدب العربي - Arabic literature' => [
                    'الأدب القديم - Ancient literature',
                    'الأدب الحديث - Modern literature',
                    'النقد الأدبي - Literary criticism',
                ],
                'اللغات الأجنبية - Foreign languages' => [
                    'اللغة الإنجليزية - English language',
                    'اللغة الفرنسية - French language',
                    'اللغة الألمانية - German language',
                ],
                'الأدب المقارن - Comparative literature' => [
                    'دراسات التأثير والتلقي - Impact and reception studies',
                    'الآداب العالمية - World Etiquette',
                    'الدراسات العابرة للثقافات - Cross cultural studies',
                ],
                'اللسانيات - Linguistics' => [
                    'علم الأصوات - Phonology',
                    'علم الدلالة - Semantics',
                    'اللسانيات الاجتماعية - Sociolinguistics',
                ],
                'الترجمة - Translation' => [
                    'الترجمة الأدبية - Literary translation',
                    'الترجمة المتخصصة - Specialized translation',
                    'تقنيات الترجمة - Translation techniques',
                ],
            ],
            'علوم الحاسب - Computer Science' => [
                'علوم البيانات - Data science' => [
                    'تحليل البيانات - Data analysis',
                    'البيانات الضخمة - Big data',
                    'تصور البيانات - Data visualization',
                ],
                'الأمن السيبراني - Cyber security' => [
                    'أمن الشبكات - Network security',
                    'التشفير - Encryption',
                    'التحليل الجنائي الرقمي - Digital forensics',
                ],
                'نظم المعلومات - Information systems' => [
                    'نظم دعم القرار - Decision support systems',
                    'نظم معلومات المؤسسات - Enterprise information systems',
                    'تحليل النظم - Systems analysis',
                ],
                'هندسة البرمجيات - Software engineering' => [
                    'معمارية البرمجيات - Software architecture',
                    'اختبار البرمجيات - Software testing',
                    'التطوير الرشيق - Agile development',
                ],
                'الشبكات - networks' => [
                    'الشبكات اللاسلكية - Wireless networks',
                    'الحوسبة السحابية - Cloud computing',
                    'إنترنت الأشياء - Internet of things',
                ],
                'قواعد البيانات - Databases' => [
                    'قواعد البيانات الموزعة - Distributed databases',
                    'مستودعات البيانات - Data warehouses',
                    'قواعد بيانات NoSQL - NoSQL databases',
                ],
            ],
            'الذكاء الاصطناعي - Artificial intelligence' => [
                'تعلم الآلة - Machine learning' => [
                    'التعلم الخاضع للإشراف - Supervised learning',
                    'التعلم غير الخاضع للإشراف - Unsupervised learning',
                    'التعلم المعزز - Reinforcement learning',
                    'النماذج التنبؤية - Predictive models',
                ],
                'التعلم العميق - Deep learning' => [
                    'الشبكات العصبية الالتفافية - Convolutional neural networks',
                    'الشبكات العصبية المتكررة - Recurrent neural networks',
                    'نماذج المحولات - Transformer models',
                ],
                'معالجة اللغة الطبيعية - Natural language processing' => [
                    'فهم اللغة الطبيعية - Understanding natural language',
                    'توليد النصوص - Text generation',
                    'تحليل المشاعر - Sentiment analysis',
                ],
                'الرؤية الحاسوبية - Computer vision' => [
                    'تصنيف الصور - Image classification',
                    'كشف الأجسام - Object detection',
                    'تحليل الفيديو - Video analysis',
                ],
                'الذكاء الاصطناعي التوليدي - Generative artificial intelligence' => [
                    'النماذج اللغوية الكبيرة - Large linguistic models',
                    'توليد الصور - Generate images',
                    'الذكاء الاصطناعي متعدد الوسائط - Multimedia artificial intelligence',
                ],
                'الروبوتات - Robots' => [
                    'الروبوتات المتنقلة - Mobile robots',
                    'التحكم الذكي - Intelligent control',
                    'التفاعل بين الإنسان والروبوت - Human robot interaction',
                ],
            ],
            'الهندسة - Engineering' => [
                'الهندسة المدنية - Civil engineering' => [
                    'هندسة الإنشاءات - Construction engineering',
                    'هندسة النقل - Transportation engineering',
                    'الهندسة الجيوتقنية - Geotechnical engineering',
                ],
                'الهندسة المعمارية - Architecture' => [
                    'التصميم المعماري - Architectural design',
                    'التخطيط العمراني - Urban planning',
                    'العمارة المستدامة - Sustainable architecture',
                ],
                'الهندسة الكهربائية - Electrical engineering' => [
                    'نظم القدرة - Power systems',
                    'الإلكترونيات - Electronics',
                    'هندسة الاتصالات - Communications engineering',
                ],
                'الهندسة الميكانيكية - Mechanical engineering' => [
                    'الديناميكا الحرارية - Thermodynamics',
                    'ميكانيكا الموائع - Fluid mechanics',
                    'التصميم الميكانيكي - Mechanical design',
                ],
                'الهندسة الكيميائية - Chemical engineering' => [
                    'هندسة العمليات - Process engineering',
                    'هندسة المواد الكيميائية - Chemical engineering',
                    'التقنيات الحيوية الصناعية - Industrial biotechnology',
                ],
                'الهندسة الصناعية - Industrial engineering' => [
                    'بحوث العمليات - Operations research',
                    'إدارة الجودة - Quality management',
                    'هندسة النظم - Systems engineering',
                ],
                'الهندسة الطبية الحيوية - Biomedical engineering' => [
                    'الأجهزة الطبية - Medical devices',
                    'معالجة الإشارات الحيوية - Biosignal processing',
                    'المواد الحيوية - Biomaterials',
                ],
            ],
            'الطب والعلوم الصحية - Medicine and health sciences' => [
                'الطب الباطني - Internal medicine' => [
                    'أمراض القلب - Heart disease',
                    'أمراض الجهاز الهضمي - Gastrointestinal diseases',
                    'الغدد الصماء - Endocrine glands',
                ],
                'الجراحة - Surgery' => [
                    'الجراحة العامة - General surgery',
                    'جراحة الأعصاب - Neurosurgery',
                    'جراحة القلب والصدر - Cardiothoracic surgery',
                ],
                'الصحة العامة - Public health' => [
                    'علم الوبائيات - Epidemiology',
                    'السياسات الصحية - Health policies',
                    'صحة المجتمع - Community health',
                ],
                'الصيدلة - Pharmacy' => [
                    'علم الأدوية - Pharmacology',
                    'الصيدلة السريرية - Clinical pharmacy',
                    'الكيمياء الدوائية - Pharmaceutical chemistry',
                ],
                'التمريض - Nursing' => [
                    'تمريض الحالات الحرجة - Critical care nursing',
                    'تمريض صحة المجتمع - Community health nursing',
                    'إدارة التمريض - Nursing administration',
                ],
                'طب الأسنان - Dentistry' => [
                    'علاج الجذور - Root canal treatment',
                    'تقويم الأسنان - Orthodontics',
                    'جراحة الفم والفكين - Oral and maxillofacial surgery',
                ],
                'العلوم الطبية الأساسية - Basic medical sciences' => [
                    'علم التشريح - Anatomy',
                    'علم وظائف الأعضاء - Physiology',
                    'علم الأمراض - Pathology',
                ],
            ],
            'الإدارة والأعمال - Management and business' => [
                'إدارة الأعمال - Business Administration' => [
                    'الإدارة الاستراتيجية - Strategic management',
                    'السلوك التنظيمي - Organizational behavior',
                    'ريادة الأعمال - Entrepreneurship',
                ],
                'التسويق - Marketing' => [
                    'التسويق الرقمي - Digital marketing',
                    'بحوث التسويق - Marketing research',
                    'إدارة العلامة التجارية - Brand management',
                ],
                'المحاسبة - Accounting' => [
                    'المحاسبة المالية - Financial accounting',
                    'المحاسبة الإدارية - Management accounting',
                    'المراجعة - Auditing',
                ],
                'إدارة الموارد البشرية - Human resources management' => [
                    'تطوير المواهب - Talent development',
                    'تقييم الأداء - Performance evaluation',
                    'علاقات العمل - Work relations',
                ],
                'إدارة العمليات - Operations management' => [
                    'إدارة سلاسل الإمداد - Supply chain management',
                    'إدارة المشروعات - Project management',
                    'إدارة الإنتاج - Production management',
                ],
                'نظم المعلومات الإدارية - Management information systems' => [
                    'ذكاء الأعمال - Business intelligence',
                    'التحول الرقمي - Digital transformation',
                    'حوكمة تقنية المعلومات - IT Governance',
                ],
            ],
            'العلوم الطبيعية - Natural sciences' => [
                'الفيزياء - Physics' => [
                    'فيزياء الجسيمات - Particle physics',
                    'فيزياء المادة المكثفة - Condensed matter physics',
                    'الفيزياء الفلكية - Astrophysics',
                ],
                'الكيمياء - Chemistry' => [
                    'الكيمياء العضوية - Organic chemistry',
                    'الكيمياء التحليلية - Analytical chemistry',
                    'الكيمياء الفيزيائية - Physical chemistry',
                ],
                'الأحياء - Biology' => [
                    'علم الأحياء الجزيئي - Molecular biology',
                    'علم الوراثة - Genetics',
                    'علم الأحياء الدقيقة - Microbiology',
                ],
                'علوم الأرض - Earth sciences' => [
                    'الجيولوجيا - Geology',
                    'الجيوفيزياء - Geophysics',
                    'علم المعادن - Mineralogy',
                ],
                'علوم الفضاء والفلك - Space sciences and astronomy' => [
                    'علم الكونيات - Cosmology',
                    'فيزياء الشمس - Sun physics',
                    'علوم الكواكب - Planetary science',
                ],
                'علوم المواد - Materials science' => [
                    'المواد النانوية - Nanomaterials',
                    'المواد المركبة - Composite materials',
                    'المواد الذكية - Smart materials',
                ],
            ],
            'العلوم البيئية - Environmental sciences' => [
                'التغير المناخي - Climate change' => [
                    'نمذجة المناخ - Climate modeling',
                    'التكيف المناخي - Climate adaptation',
                    'التخفيف من الانبعاثات - Emission mitigation',
                ],
                'إدارة الموارد الطبيعية - Natural resources management' => [
                    'إدارة المياه - Water management',
                    'إدارة الأراضي - Land management',
                    'حفظ التنوع الحيوي - Preserving biodiversity',
                ],
                'التلوث البيئي - Environmental pollution' => [
                    'تلوث الهواء - Air pollution',
                    'تلوث المياه - Water pollution',
                    'إدارة النفايات - Waste management',
                ],
                'الطاقة المتجددة - Renewable energy' => [
                    'الطاقة الشمسية - Solar energy',
                    'طاقة الرياح - Wind energy',
                    'اقتصاد الهيدروجين - Hydrogen economy',
                ],
                'الاستدامة - Sustainability' => [
                    'الاقتصاد الدائري - Circular economy',
                    'المدن المستدامة - Sustainable cities',
                    'تقييم الأثر البيئي - Environmental impact assessment',
                ],
            ],
            'الرياضيات والإحصاء - Mathematics and statistics' => [
                'الرياضيات البحتة - Pure mathematics' => [
                    'الجبر - Algebra',
                    'التحليل الرياضي - Mathematical analysis',
                    'الهندسة والطوبولوجيا - Geometry and topology',
                ],
                'الرياضيات التطبيقية - Applied mathematics' => [
                    'المعادلات التفاضلية - Differential equations',
                    'النمذجة الرياضية - Mathematical modeling',
                    'التحليل العددي - Numerical analysis',
                ],
                'الإحصاء - Statistics' => [
                    'الاستدلال الإحصائي - Statistical inference',
                    'تصميم التجارب - Design of experiments',
                    'الإحصاء متعدد المتغيرات - Multivariate statistics',
                ],
                'الاحتمالات - Probability' => [
                    'العمليات العشوائية - Random processes',
                    'نظرية القياس الاحتمالي - Probabilistic measurement theory',
                    'نماذج المخاطر - Risk models',
                ],
                'بحوث العمليات - Operations research' => [
                    'البرمجة الرياضية - Mathematical programming',
                    'نظرية الطوابير - Queuing theory',
                    'المحاكاة - Simulation',
                ],
                'الإحصاء الحيوي - Biostatistics' => [
                    'تحليل البقاء - Survival analysis',
                    'التجارب السريرية - Clinical trials',
                    'الوبائيات الكمية - Quantitative epidemiology',
                ],
            ],
        ];
    }
}
