<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Subject;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $stats = [
            'total_documents' => Document::count(),
            'total_types' => DocumentType::count(),
            'total_subjects' => Subject::count(),
            'this_month' => Document::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        $documentTypes = DocumentType::withCount('documents')
            ->orderBy('documents_count', 'desc')
            ->take(8)
            ->get();

        $featuredDocuments = Document::with(['documentType', 'authors', 'subjects'])
            ->where('is_featured', true)
            ->orderBy('published_date', 'desc')
            ->take(6)
            ->get();

        $latestDocuments = Document::with(['documentType', 'authors', 'subjects'])
            ->orderBy('published_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('web.home', compact(
            'stats',
            'documentTypes',
            'featuredDocuments',
            'latestDocuments'
        ));
    }

    public function search(Request $request)
    {
        $query = Document::with(['documentType', 'authors', 'subjects']);

        // Text search
        if ($request->filled('q')) {
            $searchTerm = $request->get('q');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('content', 'like', "%{$searchTerm}%")
                  ->orWhere('excerpt', 'like', "%{$searchTerm}%")
                  ->orWhere('keywords', 'like', "%{$searchTerm}%")
                  ->orWhere('document_number', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by document type
        if ($request->filled('document_type')) {
            $query->where('document_type_id', $request->get('document_type'));
        }

        // Filter by subject
        if ($request->filled('subject')) {
            $query->whereHas('subjects', function ($q) use ($request) {
                $q->where('subjects.id', $request->get('subject'));
            });
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->whereYear('published_date', $request->get('year'));
        }

        // Sorting
        switch ($request->get('sort', 'relevance')) {
            case 'date_desc':
                $query->orderBy('published_date', 'desc');
                break;
            case 'date_asc':
                $query->orderBy('published_date', 'asc');
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            case 'views':
                $query->orderBy('view_count', 'desc');
                break;
            default: // relevance
                $query->orderBy('is_featured', 'desc')
                      ->orderBy('published_date', 'desc');
                break;
        }

        $documents = $query->paginate(20);

        // Get filter options
        $documentTypes = DocumentType::withCount('documents')
            ->orderBy('name')
            ->get();

        $subjects = Subject::withCount('documents')
            ->orderBy('name')
            ->get();

        $years = Document::select(DB::raw('YEAR(published_date) as year'))
            ->whereNotNull('published_date')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter();

        return view('web.search', compact(
            'documents',
            'documentTypes',
            'subjects',
            'years'
        ));
    }

    public function showDocument($typeSlug, $documentSlug)
    {
        $documentType = DocumentType::where('slug', $typeSlug)->firstOrFail();
        
        $document = Document::with(['documentType', 'documentStatus', 'authors', 'subjects'])
            ->where('slug', $documentSlug)
            ->where('document_type_id', $documentType->id)
            ->firstOrFail();

        // Find related documents (same type or same subjects)
        $relatedDocuments = Document::with(['documentType'])
            ->where('id', '!=', $document->id)
            ->where(function ($query) use ($document) {
                $query->where('document_type_id', $document->document_type_id)
                      ->orWhereHas('subjects', function ($q) use ($document) {
                          $q->whereIn('subjects.id', $document->subjects->pluck('id'));
                      });
            })
            ->orderBy('published_date', 'desc')
            ->take(5)
            ->get();

        // Increment view count
        $document->increment('view_count');

        return view('web.document.show', compact('document', 'relatedDocuments'));
    }

    public function downloadDocument(Document $document)
    {
        if (!$document->file_path || !file_exists(storage_path('app/' . $document->file_path))) {
            abort(404, 'File not found');
        }

        // Increment download count
        $document->increment('download_count');

        return response()->download(
            storage_path('app/' . $document->file_path),
            $document->title . '.pdf'
        );
    }
}