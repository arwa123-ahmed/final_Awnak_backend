<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ChatbotResponse;

class ChatbotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ChatbotResponse::create([
            'question' => 'ازاي احجز خدمة',
            'keywords' => 'حجز,خدمة,booking,reserve',
            'answer' => 'تقدر تحجز خدمة من صفحة الخدمات وتختار مقدم الخدمة المناسب ليك '
        ]);

        ChatbotResponse::create([
            'question' => 'ازاي اضيف خدمة',
            'keywords' => 'اضافة,خدمة,add,service',
            'answer' => 'ادخل على حسابك واضغط على إضافة خدمة واملأ البيانات المطلوبة '
        ]);

        ChatbotResponse::create([
            'question' => 'نسيت الباسورد',
            'keywords' => 'password,forgot,نسيت,باسورد',
            'answer' => 'اضغط على "نسيت كلمة المرور" وهتوصلك رسالة لإعادة التعيين '
        ]);
    }
}
