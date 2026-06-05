<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = ['name', 'start_date', 'deadline', 'description'];

    protected $casts = [
        'start_date' => 'date',
        'deadline'   => 'date',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)
            ->whereNull('parent_id')
            ->orderBy('sort_order');
    }

    public function allTasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
