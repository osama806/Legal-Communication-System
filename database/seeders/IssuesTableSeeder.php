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
        for ($i = 0; $i < 500; $i++) {
            // تاريخ البداية
            $startDate = $faker->dateTimeBetween('-1 year', 'now'); // تاريخ البداية بين السنة الماضية والآن
            // تاريخ النهاية (قد يكون null)
            $endDate = $faker->optional()->dateTimeBetween($startDate, '+1 year'); // تاريخ النهاية بعد تاريخ البداية

            // تحديد الحالة بناءً على وجود تاريخ النهاية
            $status = $endDate ? 'مغلقة' : $faker->randomElement(['قيد الدعوى', 'قيد الإنجاز']);

            DB::table('issues')->insert([
                'base_number' => $faker->unique()->numerify('########'), // رقم أساس فريد
                'record_number' => $faker->unique()->numerify('########'), // رقم السجل فريد
                'lawyer_id' => $faker->numberBetween(1, 200), // ID المحامي من 1 إلى 200
                'agency_id' => $faker->numberBetween(1, 200),
                'court_id' => $faker->numberBetween(1, 200),
                'court_room_id' => $faker->numberBetween(1, 200),
                'start_date' => $startDate->format('Y-m-d'), // تحويل التاريخ إلى صيغة Y-m-d
                'end_date' => $endDate ? $endDate->format('Y-m-d') : null, // تحويل التاريخ إذا لم يكن null
                'status' => $status, // تعيين الحالة بناءً على تاريخ النهاية
                'estimated_cost' => $faker->numberBetween(1000, 10000000), // تكلفة تقديرية بين 1000 و 10000000
                'is_active' => $endDate ? 0 : 1, // إذا كان تاريخ النهاية غير فارغ، set is_active to 0
                'success_rate' => $faker->optional()->numberBetween(0, 100), // نسبة النجاح بين 0 و 100، يمكن أن تكون فارغة
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
