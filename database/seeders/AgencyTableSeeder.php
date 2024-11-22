<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class AgencyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // إدخال بيانات وهمية لـ 200 وكالة
        for ($i = 0; $i < 200; $i++) {
            DB::table('agencies')->insert([
                'sequential_number' => $faker->unique()->numerify('########'), // رقم تسلسلي فريد
                'record_number' => $faker->unique()->numerify('########'),     // رقم السجل فريد
                'user_id' => $faker->numberBetween(304, 605),                   // ID المستخدم من 1 إلى 300 (تأكد من وجود هذه IDs)
                'lawyer_id' => $faker->numberBetween(1, 201),     // ID المحامي من 1 إلى 200، يمكن أن يكون فارغًا
                'representative_id' => $faker->numberBetween(1, 2), // ID الممثل من 1 إلى 100، يمكن أن يكون فارغًا
                'place_of_issue' => $faker->city,
                'type' => $faker->randomElement(['جنائية', 'مدنية', 'تجارية', 'عائلية']),
                'authorizations' => $faker->paragraph,
                'exceptions' => $faker->paragraph,
                'cause' => $faker->sentence,
                'status' => $faker->randomElement(['pending', 'approved', 'rejected']),
                'is_active' => $faker->boolean,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
