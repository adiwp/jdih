<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;

class DocumentViewController extends Controller
{
    public function trackView(Document $document, Request $request)
    {
        // Track page view
        $document->increment('view_count');

        return response()->json([
            'success' => true,
            'view_count' => $document->view_count
        ]);
    }
}