<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Research extends Model
{
    protected $fillable = ['title', 'abstract', 'file_path', 'status', 'researcher_id', 'supervisor_id', 'department_id'];

    public function researcher()
    {
        return $this->belongsTo(User::class, 'researcher_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
