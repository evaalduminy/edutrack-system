<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use App\Models\Research;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Database Seeder
 *
 * Seeds the database with realistic test data for the EduTrack system.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Departments ───
        $departments = [
            ['name' => 'علوم الحاسب', 'description' => 'قسم علوم الحاسب والبرمجيات'],
            ['name' => 'الهندسة الكهربائية', 'description' => 'قسم الهندسة الكهربائية والإلكترونية'],
            ['name' => 'الذكاء الاصطناعي', 'description' => 'قسم الذكاء الاصطناعي وتعلم الآلة'],
            ['name' => 'إدارة الأعمال', 'description' => 'قسم إدارة الأعمال والتسويق'],
            ['name' => 'الطب البشري', 'description' => 'كلية الطب البشري'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['name' => $dept['name']], $dept);
        }

        $csDept = Department::where('name', 'علوم الحاسب')->first();
        $aiDept = Department::where('name', 'الذكاء الاصطناعي')->first();

        // ─── Users ───
        // Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@edutrack.local'],
            [
                'name'          => 'مدير النظام',
                'password'      => 'password',
                'role'          => 'super_admin',
                'department_id' => $csDept->id,
            ]
        );

        // Supervisor
        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@edutrack.local'],
            [
                'name'          => 'د. محمد أحمد',
                'password'      => 'password',
                'role'          => 'supervisor',
                'department_id' => $csDept->id,
            ]
        );

        // Researcher (أحمد — the test user from the requirements)
        $ahmed = User::firstOrCreate(
            ['email' => 'ahmed@edutrack.local'],
            [
                'name'          => 'أحمد محمد',
                'password'      => 'password',
                'role'          => 'researcher',
                'department_id' => $aiDept->id,
            ]
        );

        // ─── Research Entries ───
        $researchEntries = [
            [
                'title'         => 'تطبيق الذكاء الاصطناعي في التشخيص الطبي',
                'abstract'      => 'تهدف هذه الدراسة إلى استكشاف تطبيقات الذكاء الاصطناعي في مجال التشخيص الطبي، وتقييم فعالية نماذج التعلم العميق في الكشف المبكر عن الأمراض.',
                'status'        => 'approved',
                'researcher_id' => $ahmed->id,
                'supervisor_id' => $supervisor->id,
                'department_id' => $aiDept->id,
            ],
            [
                'title'         => 'تطوير نظام إدارة المحتوى باستخدام Laravel',
                'abstract'      => 'بحث تطبيقي يستعرض بناء نظام إدارة محتوى متكامل باستخدام إطار العمل Laravel مع التركيز على الأداء والأمان.',
                'status'        => 'pending',
                'researcher_id' => $ahmed->id,
                'supervisor_id' => $supervisor->id,
                'department_id' => $csDept->id,
            ],
            [
                'title'         => 'دراسة مقارنة لخوارزميات التشفير الحديثة',
                'abstract'      => 'مقارنة شاملة بين خوارزميات التشفير المتماثل وغير المتماثل من حيث الأمان والأداء والاستخدام العملي.',
                'status'        => 'approved',
                'researcher_id' => $ahmed->id,
                'supervisor_id' => $supervisor->id,
                'department_id' => $csDept->id,
            ],
        ];

        foreach ($researchEntries as $entry) {
            Research::firstOrCreate(['title' => $entry['title']], $entry);
        }

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('   Admin: admin@edutrack.local / password');
        $this->command->info('   Supervisor: supervisor@edutrack.local / password');
        $this->command->info('   Researcher: ahmed@edutrack.local / password');
    }
}
