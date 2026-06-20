<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyDomain extends Model
{
    protected $fillable = ['code', 'name', 'color', 'description', 'sort_order'];

    public function items()
    {
        return $this->hasMany(SurveyItem::class, 'domain_id');
    }
}
