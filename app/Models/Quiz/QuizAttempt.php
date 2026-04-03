<?php

namespace App\Models\Quiz;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'quiz_model_id',
        'student_id',
        'score',
        'max_score',
        'started_at',
        'submitted_at',
        'status',
    ];

    protected $casts = [
        'score'        => 'decimal:2',
        'max_score'    => 'decimal:2',
        'started_at'   => 'datetime',
        'submitted_at' => 'datetime',
    ];

    // ─── Relationships ───

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function quizModel()
    {
        return $this->belongsTo(QuizModel::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function answers()
    {
        return $this->hasMany(QuizAnswer::class, 'attempt_id');
    }

    // ─── Accessors ───

    public function getPercentageAttribute()
    {
        if ($this->max_score > 0) {
            return round(($this->score / $this->max_score) * 100, 1);
        }
        return 0;
    }

    public function getCorrectCountAttribute()
    {
        return $this->answers()->where('is_correct', true)->count();
    }

    public function getWrongCountAttribute()
    {
        return $this->answers()->where('is_correct', false)->count();
    }

    public function getDurationAttribute()
    {
        if ($this->started_at && $this->submitted_at) {
            return $this->started_at->diffInMinutes($this->submitted_at);
        }
        return null;
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'in_progress' => 'جاري',
            'submitted'   => 'تم التسليم',
            'graded'      => 'مُصحّح',
            default       => $this->status,
        };
    }

    // ─── Methods ───

    /**
     * Calculate and persist the score from answers.
     */
    public function calculateScore(): void
    {
        $totalScore = 0;
        $maxScore = 0;

        foreach ($this->answers()->with('question')->get() as $answer) {
            $questionScore = $answer->question->score ?? 1;
            $maxScore += $questionScore;

            if ($answer->is_correct) {
                $totalScore += $questionScore;
                $answer->update(['score_awarded' => $questionScore]);
            } else {
                $answer->update(['score_awarded' => 0]);
            }
        }

        $this->update([
            'score'     => $totalScore,
            'max_score' => $maxScore,
            'status'    => 'graded',
        ]);
    }

    /**
     * Check if the attempt is still within time limit.
     */
    public function isWithinTimeLimit(): bool
    {
        $quiz = $this->quiz;

        if (!$quiz->time_limit_minutes || !$this->started_at) {
            return true;
        }

        return now()->lt($this->started_at->addMinutes($quiz->time_limit_minutes));
    }

    /**
     * Get remaining time in seconds.
     */
    public function getRemainingSecondsAttribute(): ?int
    {
        $quiz = $this->quiz;

        if (!$quiz->time_limit_minutes || !$this->started_at) {
            return null;
        }

        $deadline = $this->started_at->addMinutes($quiz->time_limit_minutes);
        $remaining = now()->diffInSeconds($deadline, false);

        return max(0, $remaining);
    }
}
