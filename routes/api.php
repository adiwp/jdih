<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\JdihnFeedController;
use App\Http\Controllers\Api\DocumentViewController;

// JDIHN Integration API Routes (SATU DATA HUKUM INDONESIA)
Route::prefix('v1/jdihn')->name('jdihn.')->group(function () {
    Route::get('documents', [JdihnFeedController::class, 'documents'])->name('documents');
    Route::get('documents/{id}', [JdihnFeedController::class, 'document'])->name('document');
    Route::get('abstracts', [JdihnFeedController::class, 'abstracts'])->name('abstracts');
});

// Public API Routes
Route::prefix('v1')->middleware('api')->group(function () {
    // View tracking
    Route::post('documents/{document}/view', [DocumentViewController::class, 'trackView'])->name('document.track-view');
    
    // Document search and retrieval
    Route::get('documents', function (Request $request) {
        $documents = \App\Models\Document::with(['documentType', 'authors'])
            ->when($request->q, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('excerpt', 'like', "%{$search}%");
            })
            ->when($request->type, function ($query, $type) {
                $query->whereHas('documentType', function ($q) use ($type) {
                    $q->where('slug', $type);
                });
            })
            ->latest('published_date')
            ->paginate(20);
            
        return response()->json($documents);
    });
    
    Route::get('documents/{id}', function ($id) {
        $document = \App\Models\Document::with(['documentType', 'authors', 'subjects'])
            ->published()
            ->findOrFail($id);
            
        return response()->json($document);
    });
    
    // Document types
    Route::get('document-types', function () {
        $types = \App\Models\DocumentType::active()
            ->withCount('documents')
            ->ordered()
            ->get();
            
        return response()->json($types);
    });
    
    // Subjects
    Route::get('subjects', function () {
        $subjects = \App\Models\Subject::active()
            ->root()
            ->with('children')
            ->withCount('documents')
            ->ordered()
            ->get();
            
        return response()->json($subjects);
    });
    
    // Authors
    Route::get('authors', function () {
        $authors = \App\Models\Author::active()
            ->withCount('documents')
            ->orderBy('name')
            ->paginate(50);
            
        return response()->json($authors);
    });
    
    // Statistics
    Route::get('statistics', function () {
        return response()->json([
            'total_documents' => \App\Models\Document::published()->count(),
            'total_types' => \App\Models\DocumentType::active()->count(),
            'total_subjects' => \App\Models\Subject::active()->count(),
            'total_authors' => \App\Models\Author::active()->count(),
            'documents_by_type' => \App\Models\DocumentType::active()
                ->withCount(['documents' => function ($query) {
                    $query->published();
                }])
                ->get()
                ->map(function ($type) {
                    return [
                        'type' => $type->name,
                        'slug' => $type->slug,
                        'count' => $type->documents_count,
                    ];
                }),
        ]);
    });
});