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
                'description' => $faker->text(), // الوصف المُولد
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
