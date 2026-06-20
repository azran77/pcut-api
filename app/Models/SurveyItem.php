<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyItem extends Model
{
    protected $fillable = ['domain_id', 'item_code', 'statement', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function domain()
    {
        return $this->belongsTo(SurveyDomain::class, 'domain_id');
    }
}
