<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'code',
        'parent_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Parent subject (for hierarchy)
     */
    public function parent()
    {
        return $this->belongsTo(Subject::class, 'parent_id');
    }

    /**
     * Child subjects
     */
    public function children()
    {
        return $this->hasMany(Subject::class, 'parent_id')->ordered();
    }

    /**
     * Documents in this subject
     */
    public function documents()
    {
        return $this->belongsToMany(Document::class, 'document_subject')
                   ->withTimestamps();
    }

    /**
     * Scope for active subjects
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordered by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope for root subjects (no parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get breadcrumb path
     */
    public function getBreadcrumbAttribute()
    {
        $path = collect([$this->name]);
        $parent = $this->parent;
        
        while ($parent) {
            $path->prepend($parent->name);
            $parent = $parent->parent;
        }
        
        return $path->implode(' > ');
    }
}
