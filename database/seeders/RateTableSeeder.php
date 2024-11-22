<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        // إدخال بيانات وهمية لـ 500 تقييم
        for ($i = 0; $i < 500; $i++) {
            DB::table('rates')->insert([
                'lawyer_id' => $faker->numberBetween(1, 200), // ID المحامي من 1 إلى 50 (تأكد من وجود هذه IDs)
                'user_id' => $faker->numberBetween(304, 603),  // ID المستخدم من 1 إلى 300 (تأكد من وجود هذه IDs)
                'rating' => $faker->numberBetween(1, 5),     // تقييم من 1 إلى 5
                'review' => $faker->optional()->sentence(10), // مراجعة من 10 كلمات تقريبًا، ويمكن أن تكون فارغة
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
