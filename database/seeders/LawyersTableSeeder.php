<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class LawyersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // استرجاع جميع معرفات التخصصات الموجودة
        $specializationIds = DB::table('specializations')->pluck('id')->toArray();

        // التحقق من وجود أي تخصصات
        if (empty($specializationIds)) {
            throw new \Exception('There are no specializations in the specializations table.');
        }

        for ($i = 0; $i < 200; $i++) {
            $description = $faker->randomElement([
                "محامٍ متخصص في القضايا " . $faker->randomElement(['الجنائية', 'المدنية', 'التجارية', 'العائلية', 'التأمينية']) .
                " بخبرة تزيد عن " . $faker->numberBetween(5, 30) . " سنوات. يتمتع المحامي بخبرة واسعة في التعامل مع القضايا المعقدة وضمان أفضل النتائج لعملائه. مقره في " .
                $faker->city . "، وهو معروف بمصداقيته والتزامه بتقديم خدمات قانونية متميزة.",

                "محامٍ ذو خبرة " . $faker->numberBetween(3, 25) . " سنة في التعامل مع القضايا " .
                $faker->randomElement(['التجارية', 'العقارية', 'الإدارية', 'الشخصية', 'الأحوال الشخصية']) . ". معروف بحسن تعامله مع العملاء وبقدرته على تقديم استشارات قانونية دقيقة وشاملة. يعمل حاليًا في " .
                $faker->city . "، ويقدم خدماته للعملاء المحليين والدوليين.",

                "خبير قانوني معتمد يتمتع بخبرة تزيد عن " . $faker->numberBetween(7, 20) . " سنوات في القضايا " .
                $faker->randomElement(['العمالية', 'العائلية', 'العقارية', 'الجنائية']) . ". يحرص دائمًا على تحقيق العدالة لعملائه وتقديم نصائح قانونية واضحة ومباشرة. مقره في " .
                $faker->city . " ويعتبر من المحامين المرموقين في المنطقة.",

                "محامٍ مختص في القانون " . $faker->randomElement(['الجنائي', 'المدني', 'التجاري', 'الشخصي']) . " مع تاريخ حافل في الدفاع عن حقوق العملاء. يعمل منذ " .
                $faker->numberBetween(10, 35) . " سنة، ويتميز بأسلوبه الواضح والمباشر في تقديم الخدمات القانونية. يُعتبر مرجعًا في القضايا المعقدة في " .
                $faker->city . ".",

                "محامٍ محترف يقدم خدمات قانونية متميزة في القضايا " . $faker->randomElement(['المالية', 'الإدارية', 'الجنائية', 'العقارية']) . ". خبرته تمتد لأكثر من " .
                $faker->numberBetween(6, 15) . " عامًا، وهو معروف بقدرته على التفاوض وتحقيق أفضل النتائج لعملائه. يعمل في " .
                $faker->city . " ولديه سجل حافل من النجاحات."
            ]);

            // اختيار تخصص عشوائي
            $specializationId = $faker->randomElement($specializationIds);

            $lawyerId = DB::table('lawyers')->insertGetId([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'email_verified_at' => $faker->optional()->dateTimeThisYear(),
                'password' => Hash::make('123456789'), // يمكنك تعديل كلمة المرور الافتراضية هنا
                'address' => $faker->address,
                'specialization_id' => $faker->randomElement($specializationIds), // اختيار تخصص عشوائي
                'union_branch' => $faker->randomElement(['Damascus', 'Aleppo', 'Homs', 'Latakia']),
                'union_number' => $faker->unique()->numberBetween(10000000, 99999999),
                'affiliation_date' => $faker->date('Y-m-d', 'now'),
                'years_of_experience' => $faker->numberBetween(1, 40),
                'phone' => $faker->unique()->phoneNumber,
                'description' => $description, // الوصف المُولد
                'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($faker->name) . '&size=200', // URL الصورة
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // إدراج التخصص في جدول lawyer_specializations
            DB::table('lawyer_specializations')->insert([
                'lawyer_id' => $lawyerId,
                'specialization_id' => $specializationId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
