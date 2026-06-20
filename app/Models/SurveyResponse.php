<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    protected $fillable = ['submission_id', 'item_id', 'response_value'];

    protected function casts(): array
    {
        return ['response_value' => 'integer'];
    }

    public function submission()
    {
        return $this->belongsTo(SurveySubmission::class, 'submission_id');
    }

    public function item()
    {
        return $this->belongsTo(SurveyItem::class, 'item_id');
    }
}
