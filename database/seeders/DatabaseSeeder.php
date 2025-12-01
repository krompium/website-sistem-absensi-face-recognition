<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Classes;
use App\Models\Student;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create classes
        $classes = [
            ['name' => 'X IPA 1', 'grade' => '10', 'major' => 'IPA', 'homeroom_teacher' => 'Pak Budi'],
            ['name' => 'X IPA 2', 'grade' => '10', 'major' => 'IPA', 'homeroom_teacher' => 'Bu Siti'],
            ['name' => 'XI IPS 1', 'grade' => '11', 'major' => 'IPS', 'homeroom_teacher' => 'Pak Ahmad'],
        ];

        foreach ($classes as $class) {
            Classes::create($class);
        }

        // Create sample students
        Student::create([
            'nis' => '2024001',
            'name' => 'Ahmad Fauzi',
            'class_id' => 1,
            'gender' => 'male',
            'birth_date' => '2008-05-15',
            'phone' => '081234567890',
            'parent_phone' => '628123456789',
            'parent_name' => 'Bapak Fauzi',
            'address' => 'Jl. Merdeka No. 123',
            'is_active' => true,
        ]);

        Student::create([
            'nis' => '2024002',
            'name' => 'Siti Nurhaliza',
            'class_id' => 1,
            'gender' => 'female',
            'birth_date' => '2008-08-20',
            'phone' => '081234567891',
            'parent_phone' => '628123456788',
            'parent_name' => 'Ibu Haliza',
            'address' => 'Jl. Sudirman No. 456',
            'is_active' => true,
        ]);
    }
}