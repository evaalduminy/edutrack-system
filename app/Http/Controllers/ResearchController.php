<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ResearchController extends Controller
{
    // دالة (Method) لعرض قائمة الأبحاث
    public function index()
    {
        // مؤقتاً نعيد نص، لاحقاً سنجلب البيانات من قاعدة البيانات ونرسلها لصفحة Blade
        return "هذه صفحة تعرض جميع الأبحاث المتاحة في النظام.";
    }

    // دالة (Method) لعرض تفاصيل بحث واحد فقط بناءً على الـ ID الخاص به
    public function show($id)
    {
        return "هذه الصفحة تعرض تفاصيل البحث رقم: " . $id;
    }
}
