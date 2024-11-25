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

        for ($i = 0; $i < 200; $i++) {
            DB::table('lawyers')->insert([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'email_verified_at' => $faker->optional()->dateTimeThisYear(),
                'password' => Hash::make('123456789'), // يمكنك تعديل كلمة المرور الافتراضية هنا
                'address' => $faker->address,
                'union_branch' => $faker->randomElement(['Damascus', 'Aleppo', 'Homs', 'Latakia']),
                'union_number' => $faker->unique()->numberBetween(10000000, 99999999),
                'affiliation_date' => $faker->date('Y-m-d', 'now'),
                'years_of_experience' => $faker->numberBetween(1, 40),
                'phone' => $faker->unique()->phoneNumber,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
