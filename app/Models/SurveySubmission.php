<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveySubmission extends Model
{
    protected $fillable = [
        'student_id',
        'class_id',
        'total_score',
        'logit_score',
        'completed_at',
        'score_m', 'score_r', 'score_p', 'score_t', 'score_o',
        'logit_m', 'logit_r', 'logit_p', 'logit_t', 'logit_o',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'total_score'  => 'integer',
            'logit_score'  => 'float',
            'score_m' => 'integer', 'score_r' => 'integer',
            'score_p' => 'integer', 'score_t' => 'integer', 'score_o' => 'integer',
            'logit_m' => 'float',   'logit_r' => 'float',
            'logit_p' => 'float',   'logit_t' => 'float',   'logit_o' => 'float',
        ];
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class, 'class_id');
    }

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class, 'submission_id');
    }

    /**
     * Domain scores as an associative array.
     */
    public function getDomainScoresAttribute(): array
    {
        return [
            'M' => ['raw' => $this->score_m, 'logit' => $this->logit_m],
            'R' => ['raw' => $this->score_r, 'logit' => $this->logit_r],
            'P' => ['raw' => $this->score_p, 'logit' => $this->logit_p],
            'T' => ['raw' => $this->score_t, 'logit' => $this->logit_t],
            'O' => ['raw' => $this->score_o, 'logit' => $this->logit_o],
        ];
    }

    /**
     * Ability level label from logit score.
     */
    public function getAbilityLevelAttribute(): string
    {
        return match(true) {
            $this->logit_score >= 90 => 'Expert',
            $this->logit_score >= 75 => 'Advanced',
            $this->logit_score >= 55 => 'Intermediate',
            $this->logit_score >= 40 => 'Developing',
            default                  => 'Novice',
        };
    }
}
