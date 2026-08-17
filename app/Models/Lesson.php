<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    //

    protected $fillable = [
        'lesson',
        'duration',
        'module'
    ];

    public function watchers()
    {
        return $this->belongsToMany(User::class);
    }
}
