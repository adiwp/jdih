<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Laravel\Scout\Searchable;

class Document extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, LogsActivity, InteractsWithMedia, Searchable;

    protected $fillable = [
        'title',
        'abstract',
        'document_number',
        'call_number',
        'teu_number',
        'document_type_id',
        'document_status_id',
        'created_by',
        'updated_by',
        'language',
        'content',
        'note',
        'source',
        'location',
        'jdihn_metadata',
        'jdihn_last_sync',
        'jdihn_status',
        'jdihn_id',
        'published_date',
        'effective_date',
        'expired_date',
        'slug',
        'meta_description',
        'keywords',
        'is_featured',
        'view_count',
        'download_count',
    ];

    protected $casts = [
        'jdihn_metadata' => 'json',
        'jdihn_last_sync' => 'datetime',
        'published_date' => 'date',
        'effective_date' => 'date',
        'expired_date' => 'date',
        'is_featured' => 'boolean',
        'view_count' => 'integer',
        'download_count' => 'integer',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'document_number', 'document_status_id', 'is_featured'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'abstract' => $this->abstract,
            'content' => $this->content,
            'document_number' => $this->document_number,
            'document_type' => $this->documentType->name ?? null,
            'authors' => $this->authors->pluck('name')->toArray(),
            'subjects' => $this->subjects->pluck('name')->toArray(),
        ];
    }

    // Relationships
    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function documentStatus()
    {
        return $this->belongsTo(DocumentStatus::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function authors()
    {
        return $this->belongsToMany(Author::class, 'document_author')
                   ->withPivot('sort_order', 'role')
                   ->withTimestamps()
                   ->orderByPivot('sort_order');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'document_subject')
                   ->withTimestamps();
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->whereHas('documentStatus', function ($q) {
            $q->where('is_published', true);
        });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->whereHas('documentType', function ($q) use ($type) {
            $q->where('slug', $type);
        });
    }

    // Media Collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
            
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png']);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->performOnCollections('images');
    }

    // Accessors & Mutators
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getExcerptAttribute()
    {
        return \Str::limit(strip_tags($this->abstract), 150);
    }

    public function getIsPublishedAttribute()
    {
        return $this->documentStatus && $this->documentStatus->is_published;
    }

    // Helper methods
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function incrementDownloadCount()
    {
        $this->increment('download_count');
    }
}
