<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء كائن من مكتبة Faker
        $faker = \Faker\Factory::create();

        // قائمة بالأدوار الممكنة
        $roles = ['user', 'employee'];

        // الحصول على جميع المستخدمين من جدول users
        $users = DB::table('users')->get();

        // الحصول على جميع المحامين من جدول lawyers
        $lawyers = DB::table('lawyers')->get();

        // إنشاء دور لكل مستخدم
        foreach ($users as $user) {
            DB::table('roles')->insert([
                'name' => $faker->randomElement($roles), // اختيار عشوائي لدور
                'rolable_id' => $user->id,               // معرف المستخدم
                'rolable_type' => 'App\\Models\\User',   // نوع الكيان
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // إنشاء دور لكل محامٍ
        foreach ($lawyers as $lawyer) {
            DB::table('roles')->insert([
                'name' => $faker->randomElement(['lawyer']),
                'rolable_id' => $lawyer->id,             // معرف المحامي
                'rolable_type' => 'App\\Models\\Lawyer', // نوع الكيان
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

    }
}
