<?php

namespace App\Models\Clinical;

use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    protected $fillable = ['checklist_id', 'description', 'marks', 'sort_order'];

    public function checklist()
    {
        return $this->belongsTo(EvaluationChecklist::class, 'checklist_id');
    }
}
