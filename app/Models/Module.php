<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    //
    protected $fillable = [
        'module',
        'course'
    ];

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'module');
    }
}
