<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistAnswer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'instance_id',
        'question_id',
        'answer_value',
    ];

    /**
     * Get the checklist instance this answer belongs to.
     */
    public function instance(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ChecklistInstance::class, 'instance_id');
    }

    /**
     * Get the question this answer is for.
     */
    public function question(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ChecklistQuestion::class, 'question_id');
    }
}
