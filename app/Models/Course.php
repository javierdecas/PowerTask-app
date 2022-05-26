<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at',
        'updated_at',
        'student_id',
    ];

    public function subjects() {
        return $this->hasMany(Subject::class);
    }

    public function student() {
        return $this->belongsTo(Student::class);
    }
}
