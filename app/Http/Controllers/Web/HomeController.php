<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Subject;
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
            'this_month' => Document::whereMonth('created_at', now()->month)->count(),
        ];

        $documentTypes = DocumentType::withCount('documents')->take(8)->get();
        $featuredDocuments = Document::with(['documentType', 'authors', 'subjects'])->where('is_featured', true)->take(6)->get();
        $latestDocuments = Document::with(['documentType', 'authors', 'subjects'])->orderBy('created_at', 'desc')->take(10)->get();

        return view('web.home', compact('stats', 'documentTypes', 'featuredDocuments', 'latestDocuments'));
    }

    public function search(Request $request)
    {
        $query = Document::with(['documentType', 'authors', 'subjects']);

        if ($request->filled('q')) {
            $searchTerm = $request->get('q');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('content', 'like', "%{$searchTerm}%")
                  ->orWhere('excerpt', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('document_type')) {
            $query->where('document_type_id', $request->get('document_type'));
        }

        if ($request->filled('subject')) {
            $query->whereHas('subjects', function ($q) use ($request) {
                $q->where('subjects.id', $request->get('subject'));
            });
        }

        $documents = $query->orderBy('is_featured', 'desc')->paginate(20);
        $documentTypes = DocumentType::withCount('documents')->get();
        $subjects = Subject::withCount('documents')->get();
        $years = Document::selectRaw('YEAR(published_date) as year')->groupBy('year')->pluck('year');

        return view('web.search', compact('documents', 'documentTypes', 'subjects', 'years'));
    }

    public function document($typeSlug, $documentSlug)
    {
        $documentType = DocumentType::where('slug', $typeSlug)->firstOrFail();
        $document = Document::with(['documentType', 'documentStatus', 'authors', 'subjects'])
            ->where('slug', $documentSlug)
            ->where('document_type_id', $documentType->id)
            ->firstOrFail();

        $relatedDocuments = Document::with(['documentType'])
            ->where('id', '!=', $document->id)
            ->where('document_type_id', $document->document_type_id)
            ->take(5)->get();

        $document->increment('view_count');

        return view('web.document.show', compact('document', 'relatedDocuments'));
    }

    public function download($typeSlug, $documentSlug)
    {
        $documentType = DocumentType::where('slug', $typeSlug)->firstOrFail();
        $document = Document::where('slug', $documentSlug)
            ->where('document_type_id', $documentType->id)
            ->firstOrFail();

        if (/Users/adiwahyu/development/jdih/app/Http/Controllers/Web/HomeController.phpdocument->file_path) {
            abort(404, 'File not found');
        }

        $document->increment('download_count');
        return response()->download(storage_path('app/' . $document->file_path));
    }
}
