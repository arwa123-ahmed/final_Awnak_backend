<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\User;
use App\Models\Category;
use App\Models\Service;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // ✅ امسحي الداتا القديمة مع تعطيل الـ foreign keys
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('messages')->delete();
        DB::table('ratings')->delete();
        DB::table('reports')->delete();
        DB::table('notifications')->delete();
        DB::table('service_matches')->delete();
        DB::table('services')->delete();
        DB::table('users')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ✅ categories الأول
        $this->call(CategorySeeder::class);

        $faker = Faker::create('ar_EG');

        $countries = [
            'مصري' => [
                'cities' => [
                    'القاهرة'    => ['مدينة نصر', 'المعادي', 'التجمع', 'الرحاب', 'مدينتي', 'الزمالك', 'المقطم', 'مصر الجديدة'],
                    'الجيزة'     => ['الدقي', 'المهندسين', '6 اكتوبر', 'الشيخ زايد', 'العياط'],
                    'الإسكندرية' => ['سيدي جابر', 'العصافرة', 'العرب الجديدة'],
                ],
                'names' => ['محمد أحمد', 'أحمد علي', 'محمود حسن', 'سارة محمد', 'نور أحمد', 'اروى المشد', 'مازن محمد', 'عمار الديب', 'محمد ايمن', 'محمد ياسر', 'محمود مصطفى', 'مريم محمد', 'بسمله خالد', 'حبيبه محمد', 'منار علي', 'اسيل احمد'],
            ],
            'سوري' => [
                'cities' => [
                    'حمص'  => ['الخالدية', 'الوعر', 'الإنشاءات', 'القصير', 'الحمره', 'تدمر', 'الرستن', 'تلكلخ', 'المزينة'],
                    'دمشق' => ['المزة', 'المالكي', 'كفرسوسة', 'الميدان', 'العماره', 'الصالحيه', 'دمر', 'داريا', 'دوما', 'التل'],
                    'حلب'  => ['الفرقان', 'الشهباء'],
                ],
                'names' => ['عبد الرحمن', 'خالد يوسف', 'لينا أحمد', 'رنا محمود', 'فاتنه مارديني', 'اسماء سحلول', 'غزل الاكتع', 'عدي محمد', 'فادي خالد', 'علي', 'سناء غالب', 'امنة محمود', 'جوري محمود', 'الاء عبدالرحمن', 'راما عبدالرحمن', 'حسان محمد', 'معروف محمد'],
            ],
            'فلسطيني' => [
                'cities' => [
                    'رام الله' => ['عين مصباح', 'الماصيون'],
                    'نابلس'    => ['رفيديا', 'المخفية', 'الجبل الشمالي'],
                    'غزة'      => ['الرمال', 'الشجاعية', 'تل الهوى'],
                    'الخليل'   => ['راس الجورة', 'الحاووز', 'عين سارة'],
                ],
                'names' => ['محمد عبد الله', 'أحمد يوسف', 'عمر خليل', 'لينا محمد', 'آية أحمد', 'سارة خالد', 'محمد أحمد', 'أحمد علي', 'محمود حسن', 'سارة محمد'],
            ],
        ];

        $onlineTitles = [
            'برمجة موقع ويب', 'تطوير تطبيق موبايل', 'تصميم واجهة مستخدم UI/UX',
            'تصميم شعار احترافي', 'إنشاء متجر إلكتروني', 'كتابة محتوى تسويقي',
            'كتابة مقالات SEO', 'ترجمة نصوص احترافية', 'استشارة تقنية',
            'تحليل بيانات', 'إدارة حملات إعلانية', 'تصميم سوشيال ميديا',
            'تطوير API', 'إصلاح أخطاء برمجية', 'إعداد سيرفر واستضافة',
        ];

        $offlineTitles = [
            'توصيل مساعدات طبية', 'مساعدة منزلية', 'تنظيف منازل',
            'رعاية أطفال', 'تعليم أطفال', 'مرافقة مريض',
            'توصيل أغراض', 'مساعدة كبار السن', 'شراء احتياجات منزلية',
            'إصلاحات منزلية بسيطة', 'صيانة كهرباء خفيفة', 'مساعدة في نقل أثاث',
            'إسعاف أولي بسيط', 'توصيل أدوية', 'تنظيم فعاليات صغيرة',
        ];

        $descriptions = [
            'خدمة احترافية يتم تنفيذها بدقة وسرعة عالية مع الالتزام بالمواعيد',
            'نقدم هذه الخدمة بجودة عالية وبأسلوب احترافي يناسب احتياجات العميل',
            'خدمة موثوقة مع خبرة سابقة وضمان رضا العميل',
            'يتم تنفيذ العمل حسب طلب العميل مع مرونة في التعديل',
            'خدمة سريعة وفعالة مع متابعة مستمرة حتى التسليم النهائي',
            'نلتزم بأعلى معايير الجودة لضمان أفضل نتيجة ممكنة',
            'خدمة مخصصة بالكامل حسب احتياجاتك',
            'تنفيذ احترافي مع اهتمام بالتفاصيل الصغيرة',
            'خدمة مناسبة للأفراد والشركات مع دعم مستمر',
            'نضمن تسليم العمل في الوقت المحدد وبأفضل جودة',
        ];

        // ── Users ──
        $totalUsers = 5000;
        $batchSize  = 50;
        $users      = [];

        for ($i = 0; $i < $totalUsers; $i++) {
            $nationality = array_rand($countries);
            $city        = array_rand($countries[$nationality]['cities']);
            $street      = $faker->randomElement($countries[$nationality]['cities'][$city]);
            $name        = $faker->randomElement($countries[$nationality]['names']);

            $users[] = [
                'name'           => $name,
                'email'          => 'user' . $i . '@gmail.com',
                'role'           => $i < ($totalUsers / 2) ? 'customer' : 'volunteer',
                'account_type'   => 'user',
                'nationality'    => $nationality,
                'city'           => $city,
                'street'         => $street,
                'phone'          => '01' . rand(100000000, 999999999),
                'gender'         => rand(0, 1) ? 'ذكر' : 'أنثى',
                'password'       => Hash::make('As@12345678'),
                'earnedBalance'  => rand(0, 5000),
                'balance'        => rand(500, 3000), // ✅ balance كافي للطلبات
                'average_rating' => rand(10, 50) / 10,
                'ratings_count'  => rand(0, 100),
                'activation'     => 1,
                'national_id'    => 'XXXX-XXXX-XXXX',
                'created_at'     => now(),
                'updated_at'     => now(),
            ];

            if (count($users) === $batchSize) {
                User::insert($users);
                $users = [];
            }
        }

        if (!empty($users)) {
            User::insert($users);
        }

        $this->command->info('✅ ' . $totalUsers . ' users seeded!');

        // ── Services ──
        $usersIds        = User::pluck('id')->toArray();
        $categoryIds     = Category::pluck('id')->toArray();
        $this->command->info('Users count: ' . count($usersIds));
$this->command->info('Categories count: ' . count($categoryIds));


        if (empty($usersIds) || empty($categoryIds)) {
            $this->command->warn('❌ No users or categories found!');
            return;
        }

        $totalServices = 500;
        $services      = [];

        for ($i = 0; $i < $totalServices; $i++) {
            $categoryId = $faker->randomElement($categoryIds);
            $isOnline   = rand(0, 1);

            $services[] = [
                'name'             => $isOnline
                    ? $faker->randomElement($onlineTitles)
                    : $faker->randomElement($offlineTitles),
                'description'      => $faker->randomElement($descriptions),
                'user_id'          => $faker->randomElement($usersIds),
                'category_id'      => $categoryId,
                'type'             => $faker->randomElement(['offer', 'request']),
                'timesalary'       => rand(10, 100),
                'status'           => 'pending',
                'service_location' => $isOnline ? 'online' : $faker->city(),
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            if (count($services) === $batchSize) {
                Service::insert($services);
                $services = [];
            }
        }

        if (!empty($services)) {
            Service::insert($services);
        }

        $this->command->info('✅ ' . $totalServices . ' services seeded!');
        $this->command->info('🎉 Database seeded successfully!');
    }
}