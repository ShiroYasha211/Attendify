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
}
