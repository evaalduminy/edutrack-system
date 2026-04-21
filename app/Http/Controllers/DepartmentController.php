<?php

namespace App\Http\Controllers;

use App\Models\Department; // استدعاء موديل قاعدة البيانات
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * دالة إنشاء القسم
     */
    public function createDepartment()
    {
        // مصفوفة بأسماء لنجعل الإضافة ممتعة وعشوائية
        $fakeNames = ['الهندسة المعمارية', 'علوم الذكاء الاصطناعي', 'الطب البشري', 'الهندسة البرمجية', 'كلية الفنون'];
        
        // إدخال القسم في قاعدة البيانات
        Department::create([
            'name' => $fakeNames[array_rand($fakeNames)], // يختار اسماً عشوائياً
        ]);

        // 🔄 بدلاً من إرجاع نص عادي كما فعلناسابقاً، سنأمر لارفيل بـ (توجيه) المستخدم
        // إلى صفحة عرض الأقسام بمجرد الانتهاء من الإضافة
        return redirect('/all-departments'); 
    }

    /**
     * دالة عرض الأقسام في القالب الجميل (Blade)
     */
    public function showDepartments()
    {
        // 1. جلب كل الأقسام من قاعدة البيانات
        $departments = Department::all(); 

        // 2. 🪄 السحر هنا! 
        // دالة view('departments') تقول للارفيل: اذهب لمجلد (resources/views)
        // وافتح ملف departments.blade.php
        // المعامل الثاني ['departments' => $departments] يقوم بحمل بيانات الأقسام
        // وتمريرها للشاشة (الـ View) لكي تستطيع استخدامها!
        return view('departments', ['departments' => $departments]);
    }
}
