<?php

namespace Database\Factories;

use App\Models\Research;
use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResearchFactory extends Factory
{
    protected $model = Research::class;

    public function definition(): array
    {
        $titles = [
            'تطبيق الذكاء الاصطناعي في التشخيص الطبي',
            'أثر التعلم الآلي على تحسين الإنتاجية',
            'تطوير نظام إدارة المحتوى باستخدام Laravel',
            'دراسة مقارنة لخوارزميات التشفير الحديثة',
            'تحليل البيانات الضخمة في القطاع الصحي',
            'تصميم شبكات الحاسب الآمنة في المؤسسات',
            'تأثير الحوسبة السحابية على البنية التحتية',
            'معالجة اللغة الطبيعية العربية: تحديات وحلول',
            'أنظمة التوصية الذكية في التجارة الإلكترونية',
            'تطبيقات إنترنت الأشياء في المدن الذكية',
        ];

        return [
            'title'            => $this->faker->randomElement($titles),
            'abstract'         => $this->faker->realText(500),
            'file_path'        => null,
            'file_hash_sha256' => null,
            'status'           => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'ai_metadata'      => null,
            'researcher_id'    => User::factory(),
            'supervisor_id'    => User::factory(),
            'department_id'    => Department::factory(),
            'archived_at'      => null,
        ];
    }

    /**
     * Indicate that the research is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Indicate that the research is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'      => 'approved',
            'archived_at' => now(),
        ]);
    }
}
