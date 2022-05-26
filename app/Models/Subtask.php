<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subtask extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at',
        'updated_at',
        'task_id',
    ];

    public function task() {
        return $this->belongsTo(Task::class);
    }
}
