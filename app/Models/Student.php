<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $hidden = [
        'google_id',
        'created_at',
        'updated_at',
    ];

    public function tasks() {
        return $this->hasMany(Task::class);
    }
    public function events() {
        return $this->hasMany(Event::class);
    }
    public function sessions() {
        return $this->hasMany(Session::class);
    }
    public function courses() {
        return $this->hasMany(Course::class);
    }
    public function subjects() {
        return $this->hasMany(Subject::class);
    }
    public function blocks() {
        return $this->hasMany(Block::class);
    }
    public function periods() {
        return $this->hasMany(Period::class);
    }
}
