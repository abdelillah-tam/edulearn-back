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
}
