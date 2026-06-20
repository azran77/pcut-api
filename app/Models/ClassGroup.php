<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassGroup extends Model
{
    protected $table = 'class_groups';

    protected $fillable = [
        'name',
        'code',
        'description',
        'educator_id',
        'semester',
        'academic_year',
    ];

    public function educator()
    {
        return $this->belongsTo(User::class, 'educator_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'class_students', 'class_id', 'student_id');
    }

    public function submissions()
    {
        return $this->hasMany(SurveySubmission::class, 'class_id');
    }
}
