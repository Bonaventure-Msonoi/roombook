<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalkInFeedback extends Model
{
    public const SATISFACTION_LABELS = [
        1 => 'Very dissatisfied',
        2 => 'Dissatisfied',
        3 => 'Neutral',
        4 => 'Satisfied',
        5 => 'Very satisfied',
    ];

    public const SATISFACTION_EMOJIS = [
        1 => '😢',
        2 => '😞',
        3 => '😐',
        4 => '🙂',
        5 => '😄',
    ];

    protected $table = 'walk_in_feedback';

    protected $fillable = [
        'client_name',
        'client_email',
        'visited_at',
        'recommend_score',
        'rating_reason',
        'satisfaction_level',
        'improvement_suggestions',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
            'recommend_score' => 'integer',
            'satisfaction_level' => 'integer',
        ];
    }

    public function satisfactionLabel(): string
    {
        return self::SATISFACTION_LABELS[$this->satisfaction_level] ?? (string) $this->satisfaction_level;
    }
}