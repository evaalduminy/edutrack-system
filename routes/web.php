<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResearchController;

Route::get('/', function () {
    return view('welcome');
});

// المسار الجديد الخاص بنا
Route::get('/system', function () {
    return view('system');
});

// مسارات المتحكم (Controller) الخاصة بالأبحاث
Route::get('/researches', [ResearchController::class, 'index']);
Route::get('/researches/{id}', [ResearchController::class, 'show']);
