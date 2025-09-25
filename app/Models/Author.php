<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Author extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'institution',
        'position',
        'bio',
        'email',
        'phone',
        'website',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'institution', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Documents by this author
     */
    public function documents()
    {
        return $this->belongsToMany(Document::class, 'document_author')
                   ->withPivot('sort_order', 'role')
                   ->withTimestamps()
                   ->orderByPivot('sort_order');
    }

    /**
     * Scope for active authors
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get full display name
     */
    public function getDisplayNameAttribute()
    {
        return $this->institution 
            ? "{$this->name} ({$this->institution})" 
            : $this->name;
    }
}
