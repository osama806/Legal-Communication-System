<?php

namespace Database\Seeders;

use DB;
use Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 300; $i++) {
            DB::table('users')->insert([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'email_verified_at' => $faker->optional()->dateTimeThisYear(),
                'password' => Hash::make('123456789'), // كلمة المرور الافتراضية
                'address' => $faker->address,
                'birthdate' => $faker->date('Y-m-d', '2004-12-31'), // تاريخ الميلاد قبل 2004
                'birth_place' => $faker->city,
                'national_number' => $faker->unique()->numerify('###########'), // رقم وطني مكون من 11 رقم
                'gender' => $faker->randomElement(['male', 'female']),
                'phone' => $faker->unique()->phoneNumber,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
