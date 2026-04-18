<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'ar_name'        => 'البرمجة والتطوير',
                'en_name'        => 'Programming',
                'mode'           => 'online',
                'ar_description' => 'خدمات البرمجة وتطوير المواقع والتطبيقات',
                'en_description' => 'Programming and software development services',
            ],
            [
                'ar_name'        => 'التصميم الإبداعي',
                'en_name'        => 'Design',
                'mode'           => 'online',
                'ar_description' => 'خدمات التصميم الجرافيكي وتصميم الشعارات',
                'en_description' => 'Graphic design and logo design services',
            ],
            [
                'ar_name'        => 'الترجمة واللغات',
                'en_name'        => 'Translation',
                'mode'           => 'online',
                'ar_description' => 'خدمات الترجمة بين اللغات المختلفة',
                'en_description' => 'Translation services between different languages',
            ],
            [
                'ar_name'        => 'الاستشارات',
                'en_name'        => 'Consultations',
                'mode'           => 'online',
                'ar_description' => 'خدمات الاستشارات المهنية والتقنية',
                'en_description' => 'Professional and technical consulting services',
            ],
            [
                'ar_name'        => 'التوصيل والنقل',
                'en_name'        => 'Delivery',
                'mode'           => 'offline',
                'ar_description' => 'خدمات توصيل الطلبات والأغراض',
                'en_description' => 'Delivery and transportation services',
            ],
            [
                'ar_name'        => 'التعليم والتدريس',
                'en_name'        => 'Education',
                'mode'           => 'offline',
                'ar_description' => 'خدمات التعليم والتدريس والدروس الخصوصية',
                'en_description' => 'Education and tutoring services',
            ],
            [
                'ar_name'        => 'الصحة والطب',
                'en_name'        => 'Medicine',
                'mode'           => 'offline',
                'ar_description' => 'خدمات الرعاية الصحية والطبية',
                'en_description' => 'Health and medical care services',
            ],
            [
                'ar_name'        => 'خدمات المنزل',
                'en_name'        => 'Home Services',
                'mode'           => 'offline',
                'ar_description' => 'خدمات الصيانة والإصلاحات المنزلية',
                'en_description' => 'Home maintenance and repair services',
            ],
            [
                'ar_name'        => 'أخرى',
                'en_name'        => 'Others',
                'mode'           => 'online',
                'ar_description' => 'خدمات متنوعة أخرى',
                'en_description' => 'Other miscellaneous services',
            ],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['en_name' => $cat['en_name']],
                [
                    'ar_name'        => $cat['ar_name'],
                    'mode'           => $cat['mode'],
                    'ar_description' => $cat['ar_description'],
                    'en_description' => $cat['en_description'],
                ]
            );
        }

        $this->command->info('✅ ' . count($categories) . ' categories seeded!');
    }
}