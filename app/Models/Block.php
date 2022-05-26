<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at',
        'updated_at',
        'student_id',
        'subject_id',
        'period_id',
    ];

    public function student() {
        return $this->belongsTo(Student::class);
    }
    public function period() {
        return $this->belongsTo(Period::class);
    }

    public function subject() {
        return $this->belongsTo(Subject::class);
    }
}
