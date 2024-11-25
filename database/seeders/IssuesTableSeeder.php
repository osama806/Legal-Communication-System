<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IssuesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        // إدخال بيانات وهمية لـ 200 قضية
        for ($i = 0; $i < 200; $i++) {
            DB::table('issues')->insert([
                'base_number' => $faker->unique()->numerify('########'), // رقم أساس فريد
                'record_number' => $faker->unique()->numerify('########'), // رقم السجل فريد
                'lawyer_id' => $faker->numberBetween(1, 200), // ID المحامي من 1 إلى 50 (تأكد من وجود هذه IDs)
                'agency_id' => $faker->numberBetween(2, 202),
                'court_name' => $faker->randomElement(['جنايات', 'شرعية', 'جزاء', 'صلح']),
                'type' => $faker->randomElement(['جنائية', 'مدنية', 'تجارية', 'عائلية']),
                'start_date' => $faker->date('Y-m-d', 'now'),
                'end_date' => $faker->optional()->date('Y-m-d', '+1 year'), // يمكن أن يكون فارغًا
                'status' => $faker->randomElement(['قيد الدعوى', 'مغلقة', 'معلقة']),
                'estimated_cost' => $faker->numberBetween(1000, 10000000), // تكلفة تقديرية بين 1000 و 100000
                'is_active' => $faker->boolean,
                'success_rate' => $faker->optional()->numberBetween(0, 100), // نسبة النجاح بين 0 و 100، يمكن أن تكون فارغة
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
