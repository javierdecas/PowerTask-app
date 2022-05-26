<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $hidden = [
        'course_id',
        'created_at',
        'updated_at',
        'student_id',
        'google_id',
        'deleted',
        'pivot',        #muestra referencias en la tabla intermedia
    ];


    public function tasks() {
        return $this->hasMany(Task::class);
    }
    public function events() {
        return $this->hasMany(Event::class);
    }
    public function blocks() {
        return $this->hasMany(Block::class);
    }
    public function courses() {
        return $this->hasMany(Course::class);
    }

    public function student() {
        return $this->belongsTo(Student::class);
    }
    public function periods() {
        return $this->belongsToMany(Period::class, 'contains');
    }
}
