<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $this->call(RoleSeeder::class);

        // 2. Survey domains & 50 items
        $this->call(SurveyItemSeeder::class);

        // 3. Default admin account
        $adminRole = Role::where('name', 'admin')->first();
        $admin = User::firstOrCreate(
            ['email' => 'admin@pcut.edu'],
            [
                'name'        => 'PCUT Administrator',
                'password'    => Hash::make('Admin@1234'),
                'role_id'     => $adminRole->id,
                'institution' => 'UNITEN',
            ]
        );

        // 4. Sample educator
        $educatorRole = Role::where('name', 'educator')->first();
        $educator = User::firstOrCreate(
            ['email' => 'lecturer@pcut.edu'],
            [
                'name'        => 'Dr. Azran Ahmad',
                'password'    => Hash::make('Educator@1234'),
                'role_id'     => $educatorRole->id,
                'institution' => 'UNITEN',
            ]
        );

        // 5. Sample class
        $class = ClassGroup::firstOrCreate(
            ['code' => 'CSC3401-S1-24'],
            [
                'name'          => 'Web Programming — Semester 1 2024',
                'description'   => 'Second-year computing students enrolled in Web Programming.',
                'educator_id'   => $educator->id,
                'semester'      => 'Semester 1',
                'academic_year' => '2024/2025',
            ]
        );

        // 6. Sample students
        $studentRole = Role::where('name', 'student')->first();
        $studentData = [
            ['name' => 'Ali Hassan',        'email' => 'ali@student.pcut.edu',     'student_id' => 'S2024001'],
            ['name' => 'Nur Aisyah',        'email' => 'aisyah@student.pcut.edu',  'student_id' => 'S2024002'],
            ['name' => 'Raj Kumar',         'email' => 'raj@student.pcut.edu',     'student_id' => 'S2024003'],
            ['name' => 'Lim Wei Xin',       'email' => 'weixin@student.pcut.edu',  'student_id' => 'S2024004'],
            ['name' => 'Fatimah Zahra',     'email' => 'fatimah@student.pcut.edu', 'student_id' => 'S2024005'],
        ];

        foreach ($studentData as $data) {
            $student = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'        => $data['name'],
                    'password'    => Hash::make('Student@1234'),
                    'role_id'     => $studentRole->id,
                    'student_id'  => $data['student_id'],
                    'institution' => 'UNITEN',
                ]
            );
            $class->students()->syncWithoutDetaching([$student->id]);
        }

        $this->command->info('✅  Database seeded successfully.');
        $this->command->info('   Admin:    admin@pcut.edu        / Admin@1234');
        $this->command->info('   Educator: lecturer@pcut.edu     / Educator@1234');
        $this->command->info('   Student:  ali@student.pcut.edu  / Student@1234');
    }
}
