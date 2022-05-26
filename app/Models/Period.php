<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    use HasFactory;

    protected $hidden = [
        'student_id',
        'created_at',
        'updated_at',
    ];

    public function blocks() {
        return $this->hasMany(Block::class);
    }

    public function student() {
        return $this->belongsTo(Student::class);
    }
    public function subjects() {
        return $this->belongsToMany(Subject::class, 'contains');
    }
}
