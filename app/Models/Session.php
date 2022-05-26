<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    protected $hidden = [
        'student_id',
        'created_at',
        'updated_at',
    ];

    public function student() {
        return $this->belongsTo(Student::class);
    }
}
