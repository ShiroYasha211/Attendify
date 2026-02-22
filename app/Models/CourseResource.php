<?php

namespace App\Models;

use App\Models\Academic\Subject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'created_by',
        'title',
        'file_path',
        'file_type',
        'category',
        'description',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getCategoryTextAttribute()
    {
        $categories = [
            'lectures' => 'محاضرات',
            'references' => 'مراجع',
            'summaries' => 'ملخصات / سنوات سابقة',
            'exams' => 'نماذج اختبارات',
            'other' => 'أخرى',
        ];

        return $categories[$this->category] ?? $this->category;
    }

    // Scopes
    public function scopeFilter($query, $filters)
    {
        return $query->when($filters['search'] ?? null, function ($q, $search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhereHas('subject', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        })->when($filters['major_id'] ?? null, function ($q, $id) {
            $q->whereHas('subject', fn($s) => $s->where('major_id', $id));
        })->when($filters['level_id'] ?? null, function ($q, $id) {
            $q->whereHas('subject', fn($s) => $s->where('level_id', $id));
        })->when($filters['subject_id'] ?? null, function ($q, $id) {
            $q->where('subject_id', $id);
        })->when($filters['category'] ?? null, function ($q, $cat) {
            $q->where('category', $cat);
        })->when($filters['year'] ?? null, function ($q, $year) {
            $q->byYear($year);
        });
    }

    public function scopeByYear($query, $year)
    {
        // Assuming year is like "2024", we look at created_at
        // Academic year usually starts in Sept and ends in June/Aug.
        // Simple approximation: Year 2024 covers 2024-01 to 2024-12 ? 
        // Or User selects "2023-2024"? 
        // For now, let's assume filtering by simple YYYY of created_at
        return $query->whereYear('created_at', $year);
    }

    public function scopePopular($query)
    {
        return $query->orderBy('downloads_count', 'desc');
    }
}
