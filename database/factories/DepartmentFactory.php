<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        $departments = [
            'علوم الحاسب' => 'قسم متخصص في علوم الحاسب والبرمجيات',
            'الهندسة الكهربائية' => 'قسم الهندسة الكهربائية والإلكترونية',
            'الذكاء الاصطناعي' => 'قسم الذكاء الاصطناعي وتعلم الآلة',
            'إدارة الأعمال' => 'قسم إدارة الأعمال والتسويق',
            'الطب البشري' => 'كلية الطب البشري',
            'الهندسة المعمارية' => 'قسم الهندسة المعمارية والتصميم',
            'العلوم الإسلامية' => 'قسم العلوم الشرعية والإسلامية',
            'اللغة العربية' => 'قسم اللغة العربية وآدابها',
        ];

        $name = $this->faker->unique()->randomElement(array_keys($departments));

        return [
            'name'        => $name,
            'description' => $departments[$name],
        ];
    }
}
