<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $hidden = [
        'subject_id',
        'created_at',
        'updated_at',
        'student_id',
    ];

    public function subtasks() {
        return $this->hasMany(Subtask::class);
    }

    public function student() {
        return $this->belongsTo(Student::class);
    }
    public function subject() {
        return $this->belongsTo(Subject::class);
    }
}
