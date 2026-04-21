<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ResearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // التوجيه التلقائي إلى لوحة القيادة مباشرة
    return redirect('/dashboard');
});

// 🔓 إزالة قيد تسجيل الدخول (middleware auth) بناءً على طلبك
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// 🗄️ مسارات تجربة قواعد البيانات (Eloquent ORM) والأقسام التي عملناها مسبقاً (تم إرجاعها)
Route::get('/add-department', [DepartmentController::class, 'createDepartment']);
Route::get('/all-departments', [DepartmentController::class, 'showDepartments']);

// 📚 مسارات الأبحاث
Route::get('/researches', [ResearchController::class, 'index']);
Route::get('/researches/{id}', [ResearchController::class, 'show']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
