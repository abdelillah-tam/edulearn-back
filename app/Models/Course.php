<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Course extends Model
{
    //

    protected $fillable = [
        'title',
        'description',
        'category',
        'duration',
        'difficulty',
        'thumbnail',
        'prerequisites',
        'instructor'
    ];

    public function instructorUser()
    {
        return $this->belongsTo(User::class, 'instructor');
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }


    public function objectives()
    {
        return $this->hasMany(Objective::class, 'course');
    }

    public function modules()
    {
        return $this->hasMany(Module::class, 'course');
    }

    public function lessons()
    {
        return $this->hasManyThrough(
            Lesson::class,
            Module::class,
            'course',
            'module'
        );
    }
}
