<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    // 🛡️ حماية (Mass Assignment):
    // لأن قواعد البيانات قد تحتوي على أعمدة حساسة، لارفيل تمنعنا من إدخال البيانات مباشرة كإجراء أمني.
    // هنا، نحن نخبر لارفيل بصراحة: "اسمح لنا فقط بتعبئة عمود (الاسم) name"، لحماية باقي النظام.
    protected $fillable = ['name', 'description'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function researches()
    {
        return $this->hasMany(Research::class);
    }
}
