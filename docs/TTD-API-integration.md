# Technical Design Document (TDD) - API Design & Integration
# ILDIS - Indonesian Legal Documentation Information System
## Laravel 12.x + Filament 4.x Implementation

---

## 1. API Overview & Architecture

### 1.1 Migration Context from Existing System

#### **1.1.1 Current API Analysis (harris-sontanu/jdih-cms)**
**Existing Implementation**: Basic JDIHN integration in Laravel 10 system  
**Current Endpoints**: Limited document feed API for JDIHN compliance  
**Enhancement Requirements**: Modern RESTful API with comprehensive functionality  
**Migration Strategy**: Preserve existing JDIHN compatibility while adding modern API features  

#### **1.1.2 Enhanced API Architecture**
```
Modern ILDIS API Architecture (Laravel 12.x)
├── Public API (External Consumers)
│   ├── JDIHN Integration Feed (/api/v1/jdihn/*)
│   ├── Public Document Access (/api/v1/documents/*)
│   ├── Search & Discovery (/api/v1/search/*)
│   └── Analytics & Statistics (/api/v1/stats/*)
│
├── Admin API (Internal Management)  
│   ├── Document Management (/api/v1/admin/documents/*)
│   ├── User Management (/api/v1/admin/users/*)
│   ├── Quality Control (/api/v1/admin/quality/*)
│   └── System Management (/api/v1/admin/system/*)
│
├── Integration API (External Systems)
│   ├── Third-party Integrations (/api/v1/integrations/*)
│   ├── Webhook Endpoints (/api/v1/webhooks/*)
│   └── Bulk Operations (/api/v1/bulk/*)
│
└── Real-time API (WebSocket/Server-Sent Events)
    ├── Live Updates (/api/v1/live/*)
    ├── Sync Status Monitoring (/api/v1/monitoring/*)
    └── Notification Streams (/api/v1/notifications/*)
```

### 1.2 JDIHN Integration Architecture

#### **1.2.1 Enhanced SATU DATA HUKUM Integration**
Building on existing harris-sontanu/jdih-cms patterns with modern enhancements:

```
ILDIS ↔ JDIHN Integration Flow (Enhanced)
┌─────────────────────────────────────────────────────────────┐
│                ILDIS Application (Laravel 12.x)            │
├─────────────────┬───────────────────┬───────────────────────┤
│   Document      │  Enhanced JDIHN   │   Advanced Sync       │
│   Management    │    Service        │    Monitoring         │
│   (Filament)    │                   │                       │
└─────────────────┴───────────────────┴───────────────────────┘
                  │                   │
                  ▼                   ▼
┌─────────────────────────────────────────────────────────────┐
│              Modern API Gateway Layer                       │
├─────────────────┬───────────────────┬───────────────────────┤
│  Document Feed  │  Quality Control  │   Batch Processing    │
│  /document      │   Validation      │    & Monitoring       │
│  /abstrak       │   /validate       │    /batch /status     │
└─────────────────┴───────────────────┴───────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────────┐
│        SATU DATA HUKUM INDONESIA (JDIHN)                   │
│           National Legal Database                           │
└─────────────────────────────────────────────────────────────┘
```

#### **1.2.2 Integration Enhancement Features**
✅ **Proven Foundation**: Build on existing working JDIHN integration  
🆕 **Quality Assurance**: Automated pre-sync validation and compliance checking  
🆕 **Real-time Monitoring**: Live sync status and error tracking  
🆕 **Batch Operations**: Efficient bulk document processing  
🆕 **Retry Logic**: Intelligent retry mechanisms with exponential backoff  
🆕 **Webhook Support**: Real-time notifications for external systems  

---

## 2. Enhanced JDIHN API Endpoints

### 2.1 Document Feed API (Enhanced from Existing)

#### **2.1.1 Main Document Feed - `/api/v1/jdihn/documents`**
**Enhancement**: Building on existing `/feed/document.json` with modern Laravel 12.x patterns

```php
// app/Http/Controllers/Api/V1/JdihnFeedController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domain\Jdihn\Services\JdihnDocumentFeedService;
use App\Domain\Jdihn\Services\JdihnValidationService;
use App\Http\Resources\Jdihn\JdihnDocumentFeedResource;
use App\Http\Requests\Api\JdihnFeedRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'JDIHN Integration', description: 'SATU DATA HUKUM INDONESIA Integration APIs')]
class JdihnFeedController extends Controller
{
    public function __construct(
        private JdihnDocumentFeedService $feedService,
        private JdihnValidationService $validationService
    ) {}

    #[OA\Get(
        path: '/api/v1/jdihn/documents',
        summary: 'Generate JDIHN compliant document feed',
        description: 'Enhanced document feed for SATU DATA HUKUM INDONESIA with validation and monitoring',
        tags: ['JDIHN Integration'],
        parameters: [
            new OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 1000, default: 100)),
            new OA\Parameter(name: 'offset', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 0, default: 0)),
            new OA\Parameter(name: 'updated_since', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\Parameter(name: 'document_type', in: 'query', schema: new OA\Schema(type: 'string', enum: ['peraturan', 'putusan', 'monografi', 'artikel'])),
            new OA\Parameter(name: 'region_code', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'year', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1945)),
            new OA\Parameter(name: 'compliance_only', in: 'query', schema: new OA\Schema(type: 'boolean', default: true))
        ]
    )]
    #[OA\Response(response: 200, description: 'JDIHN compliant document feed')]
    public function documents(JdihnFeedRequest $request): JsonResponse
    {
        // Enhanced validation with compliance checking
        $parameters = $request->validated();
        
        // Pre-validate compliance if requested
        if ($parameters['compliance_only'] ?? true) {
            $complianceReport = $this->validationService->validateBatchCompliance($parameters);
            if ($complianceReport['has_issues']) {
                return response()->json([
                    'error' => 'Compliance issues detected',
                    'compliance_report' => $complianceReport,
                    'suggestion' => 'Fix compliance issues or set compliance_only=false'
                ], 422);
            }
        }

        // Generate feed with enhanced features
        $feedResult = $this->feedService->generateEnhancedFeed($parameters);

        return response()->json([
            'meta' => [
                'version' => '2.0',
                'generated_at' => now()->toISOString(),
                'total_records' => $feedResult['total'],
                'offset' => $parameters['offset'],
                'limit' => $parameters['limit'],
                'compliance_checked' => $parameters['compliance_only'] ?? true,
                'data_format' => 'JDIHN-2024',
                'sync_status' => $feedResult['sync_status']
            ],
            'data' => JdihnDocumentFeedResource::collection($feedResult['documents']),
            'links' => $this->generatePaginationLinks($feedResult, $parameters)
        ], 200, [
            'Content-Type' => 'application/json; charset=utf-8',
            'X-JDIHN-Compliance' => 'verified',
            'X-Last-Sync' => $feedResult['last_sync_at'] ?? null,
            'Cache-Control' => 'public, max-age=180', // 3 minutes cache
        ]);
    }

    #[OA\Get(
        path: '/api/v1/jdihn/documents/{id}',
        summary: 'Get single document for JDIHN',
        tags: ['JDIHN Integration']
    )]
    public function document(string $id): JsonResponse
    {
        $document = $this->feedService->getDocumentForJdihn($id);
        
        if (!$document) {
            return response()->json(['error' => 'Document not found'], 404);
        }

        // Real-time compliance validation
        $validation = $this->validationService->validateSingleDocument($document);

        return response()->json([
            'meta' => [
                'version' => '2.0',
                'generated_at' => now()->toISOString(),
                'compliance_score' => $validation['score'],
                'validation_status' => $validation['status']
            ],
            'data' => new JdihnDocumentFeedResource($document),
            'compliance' => $validation
        ]);
    }
}
```

#### **2.1.2 Abstract Feed - `/api/v1/jdihn/abstracts`**
```php
    #[OA\Get(
        path: '/api/v1/jdihn/abstracts',
        summary: 'Generate JDIHN abstract feed',
        tags: ['JDIHN Integration']
    )]
    public function abstracts(JdihnFeedRequest $request): JsonResponse
    {
        $parameters = $request->validated();
        $abstractFeed = $this->feedService->generateAbstractFeed($parameters);

        return response()->json([
            'meta' => [
                'version' => '2.0',
                'type' => 'abstract_feed',
                'generated_at' => now()->toISOString(),
                'total_records' => $abstractFeed['total']
            ],
            'data' => $abstractFeed['data']
        ], 200, [
            'Content-Type' => 'application/json; charset=utf-8',
            'X-Feed-Type' => 'abstract',
        ]);
    }
```

### 2.2 Quality Control & Validation APIs (New)

#### **2.2.1 Compliance Validation - `/api/v1/jdihn/validate`**
```php
// app/Http/Controllers/Api/V1/JdihnValidationController.php
namespace App\Http\Controllers\Api\V1;

class JdihnValidationController extends Controller
{
    #[OA\Post(
        path: '/api/v1/jdihn/validate/document',
        summary: 'Validate document JDIHN compliance',
        description: 'Real-time validation of document compliance with JDIHN standards',
        tags: ['JDIHN Integration', 'Quality Control']
    )]
    public function validateDocument(Request $request): JsonResponse
    {
        $request->validate([
            'document_id' => 'required|exists:documents,id',
            'validation_type' => 'required|in:metadata,content,format,full',
            'strict_mode' => 'boolean'
        ]);

        $validation = $this->validationService->validateDocument(
            $request->document_id,
            $request->validation_type,
            $request->boolean('strict_mode', false)
        );

        return response()->json([
            'validation_result' => [
                'status' => $validation['status'], // valid, invalid, warning
                'score' => $validation['score'], // 0-100
                'issues' => $validation['issues'],
                'recommendations' => $validation['recommendations'],
                'auto_fixable' => $validation['auto_fixable_issues']
            ],
            'metadata' => [
                'validated_at' => now()->toISOString(),
                'validation_rules_version' => $validation['rules_version'],
                'processing_time_ms' => $validation['processing_time']
            ]
        ]);
    }

    #[OA\Post(
        path: '/api/v1/jdihn/validate/batch',
        summary: 'Batch validate multiple documents',
        tags: ['JDIHN Integration', 'Quality Control']
    )]
    public function validateBatch(Request $request): JsonResponse
    {
        $request->validate([
            'document_ids' => 'required|array|min:1|max:100',
            'document_ids.*' => 'exists:documents,id',
            'validation_type' => 'required|in:metadata,content,format,full'
        ]);

        $batchValidation = $this->validationService->validateBatch(
            $request->document_ids,
            $request->validation_type
        );

        return response()->json([
            'batch_result' => [
                'total_documents' => count($request->document_ids),
                'valid_count' => $batchValidation['valid_count'],
                'invalid_count' => $batchValidation['invalid_count'],
                'warning_count' => $batchValidation['warning_count'],
                'average_score' => $batchValidation['average_score']
            ],
            'results' => $batchValidation['individual_results'],
            'summary' => $batchValidation['summary']
        ]);
    }
}
```

### 2.3 Sync Status & Monitoring APIs (New)

#### **2.3.1 Sync Status - `/api/v1/jdihn/sync/status`**
```php
// app/Http/Controllers/Api/V1/JdihnSyncController.php
namespace App\Http\Controllers\Api\V1;

class JdihnSyncController extends Controller
{
    #[OA\Get(
        path: '/api/v1/jdihn/sync/status',
        summary: 'Get JDIHN synchronization status',
        description: 'Real-time sync status monitoring with detailed metrics',
        tags: ['JDIHN Integration', 'Monitoring']
    )]
    public function status(): JsonResponse
    {
        $syncStatus = $this->jdihnService->getSyncStatus();

        return response()->json([
            'sync_overview' => [
                'overall_status' => $syncStatus['status'], // synced, pending, error
                'last_sync_at' => $syncStatus['last_sync_at'],
                'next_sync_at' => $syncStatus['next_sync_at'],
                'total_documents' => $syncStatus['total_documents'],
                'synced_documents' => $syncStatus['synced_documents'],
                'pending_documents' => $syncStatus['pending_documents'],
                'failed_documents' => $syncStatus['failed_documents']
            ],
            'performance_metrics' => [
                'sync_success_rate' => $syncStatus['success_rate'],
                'average_sync_time_ms' => $syncStatus['avg_sync_time'],
                'total_sync_attempts' => $syncStatus['total_attempts'],
                'failed_attempts' => $syncStatus['failed_attempts']
            ],
            'recent_activity' => $syncStatus['recent_logs'],
            'queue_status' => [
                'pending_jobs' => $syncStatus['queue']['pending'],
                'processing_jobs' => $syncStatus['queue']['processing'],
                'failed_jobs' => $syncStatus['queue']['failed']
            ]
        ]);
    }

    #[OA\Post(
        path: '/api/v1/jdihn/sync/trigger',
        summary: 'Trigger manual JDIHN synchronization',
        tags: ['JDIHN Integration', 'Operations']
    )]
    public function triggerSync(Request $request): JsonResponse
    {
        $request->validate([
            'document_ids' => 'nullable|array|max:1000',
            'document_ids.*' => 'exists:documents,id',
            'sync_type' => 'required|in:full,incremental,selective',
            'priority' => 'in:low,normal,high,urgent'
        ]);

        $syncJob = $this->jdihnService->triggerSync([
            'document_ids' => $request->document_ids,
            'sync_type' => $request->sync_type,
            'priority' => $request->get('priority', 'normal'),
            'requested_by' => auth()->id()
        ]);

        return response()->json([
            'sync_initiated' => true,
            'job_id' => $syncJob['job_id'],
            'estimated_completion' => $syncJob['estimated_completion'],
            'status_check_url' => route('api.jdihn.sync.job-status', $syncJob['job_id'])
        ], 202);
    }

    #[OA\Get(
        path: '/api/v1/jdihn/sync/jobs/{jobId}/status',
        summary: 'Get sync job status',
        tags: ['JDIHN Integration', 'Monitoring']
    )]
    public function jobStatus(string $jobId): JsonResponse
    {
        $jobStatus = $this->jdihnService->getSyncJobStatus($jobId);

        if (!$jobStatus) {
            return response()->json(['error' => 'Job not found'], 404);
        }

        return response()->json([
            'job_info' => [
                'id' => $jobId,
                'status' => $jobStatus['status'], // queued, processing, completed, failed
                'progress' => $jobStatus['progress'], // 0-100
                'started_at' => $jobStatus['started_at'],
                'completed_at' => $jobStatus['completed_at'],
                'estimated_completion' => $jobStatus['estimated_completion']
            ],
---

## 3. Public Document API (New Feature)

### 3.1 Public Document Access

#### **3.1.1 Document Listing - `/api/v1/documents`**
```php
// app/Http/Controllers/Api/V1/DocumentController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domain\Document\Services\PublicDocumentService;
use App\Http\Resources\DocumentResource;
use App\Http\Requests\Api\DocumentListRequest;
use Illuminate\Http\JsonResponse;

class DocumentController extends Controller
{
    public function __construct(
        private PublicDocumentService $documentService
    ) {}

    #[OA\Get(
        path: '/api/v1/documents',
        summary: 'List published documents',
        description: 'Retrieve paginated list of published legal documents',
        tags: ['Public API', 'Documents'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100)),
            new OA\Parameter(name: 'type', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'year', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'sort', in: 'query', schema: new OA\Schema(type: 'string', enum: ['created_at', 'title', 'published_date', 'view_count'])),
            new OA\Parameter(name: 'direction', in: 'query', schema: new OA\Schema(type: 'string', enum: ['asc', 'desc']))
        ]
    )]
    #[OA\Response(response: 200, description: 'List of documents')]
    public function index(DocumentListRequest $request): JsonResponse
    {
        $documents = $this->documentService->getPublishedDocuments(
            $request->validated()
        );

        return response()->json([
            'data' => DocumentResource::collection($documents->items()),
            'meta' => [
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
                'per_page' => $documents->perPage(),
                'total' => $documents->total(),
                'from' => $documents->firstItem(),
                'to' => $documents->lastItem()
            ],
            'links' => [
                'first' => $documents->url(1),
                'last' => $documents->url($documents->lastPage()),
                'prev' => $documents->previousPageUrl(),
                'next' => $documents->nextPageUrl()
            ]
        ]);
    }

    #[OA\Get(
        path: '/api/v1/documents/{id}',
        summary: 'Get single document',
        tags: ['Public API', 'Documents']
    )]
    public function show(string $id): JsonResponse
    {
        $document = $this->documentService->getPublishedDocument($id);

        if (!$document) {
            return response()->json(['error' => 'Document not found'], 404);
        }

        // Track view for analytics
        $this->documentService->incrementViewCount($document);

        return response()->json([
            'data' => new DocumentResource($document),
            'related_documents' => DocumentResource::collection(
                $this->documentService->getRelatedDocuments($document, 5)
            )
        ]);
    }

    #[OA\Get(
        path: '/api/v1/documents/{id}/download',
        summary: 'Download document file',
        tags: ['Public API', 'Documents']
    )]
    public function download(string $id): mixed
    {
        $document = $this->documentService->getPublishedDocument($id);

        if (!$document || !$document->primaryAttachment) {
            return response()->json(['error' => 'Document or file not found'], 404);
        }

        // Track download for analytics
        $this->documentService->incrementDownloadCount($document);

        return response()->download(
            storage_path('app/' . $document->primaryAttachment->file_path),
            $document->primaryAttachment->original_filename
        );
    }
}
```

### 3.2 Advanced Search API

#### **3.2.1 Full-Text Search - `/api/v1/search`**
```php
// app/Http/Controllers/Api/V1/SearchController.php
namespace App\Http\Controllers\Api\V1;

class SearchController extends Controller
{
    public function __construct(
        private SearchService $searchService
    ) {}

    #[OA\Get(
        path: '/api/v1/search',
        summary: 'Search documents with advanced filters',
        description: 'Full-text search with faceted filtering and AI-powered suggestions',
        tags: ['Public API', 'Search'],
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filters', in: 'query', schema: new OA\Schema(type: 'object')),
            new OA\Parameter(name: 'facets', in: 'query', schema: new OA\Schema(type: 'array', items: new OA\Schema(type: 'string'))),
            new OA\Parameter(name: 'sort', in: 'query', schema: new OA\Schema(type: 'string', enum: ['relevance', 'date', 'title', 'popularity'])),
            new OA\Parameter(name: 'highlight', in: 'query', schema: new OA\Schema(type: 'boolean', default: true))
        ]
    )]
    public function search(SearchRequest $request): JsonResponse
    {
        $searchParams = $request->validated();
        $results = $this->searchService->search($searchParams);

        return response()->json([
            'query' => [
                'term' => $searchParams['q'],
                'filters' => $searchParams['filters'] ?? [],
                'sort' => $searchParams['sort'] ?? 'relevance'
            ],
            'results' => [
                'data' => DocumentResource::collection($results['documents']),
                'total' => $results['total'],
                'max_score' => $results['max_score'],
                'processing_time_ms' => $results['processing_time']
            ],
            'facets' => $results['facets'],
            'suggestions' => [
                'spelling' => $results['spelling_suggestions'],
                'related_queries' => $results['related_queries'],
                'auto_complete' => $results['auto_complete']
            ],
            'meta' => [
                'page' => $results['page'],
                'per_page' => $results['per_page'],
                'total_pages' => $results['total_pages']
            ]
        ]);
    }

    #[OA\Get(
        path: '/api/v1/search/suggestions',
        summary: 'Get search suggestions',
        tags: ['Public API', 'Search']
    )]
    public function suggestions(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:2|max:100']);

        $suggestions = $this->searchService->getSuggestions($request->q);

        return response()->json([
            'query' => $request->q,
            'suggestions' => [
                'terms' => $suggestions['terms'],
                'documents' => DocumentResource::collection($suggestions['documents']),
                'subjects' => $suggestions['subjects'],
                'authors' => $suggestions['authors']
            ]
        ]);
    }

    #[OA\Get(
        path: '/api/v1/search/facets',
        summary: 'Get available search facets',
        tags: ['Public API', 'Search']
    )]
    public function facets(): JsonResponse
    {
        $facets = $this->searchService->getAvailableFacets();

        return response()->json([
            'facets' => $facets,
            'cached_at' => now()->toISOString()
        ]);
    }
}
```

### 3.3 Analytics & Statistics API

#### **3.3.1 Public Statistics - `/api/v1/stats`**
```php
// app/Http/Controllers/Api/V1/StatsController.php
namespace App\Http\Controllers\Api\V1;

class StatsController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}

    #[OA\Get(
        path: '/api/v1/stats/overview',
        summary: 'Get system overview statistics',
        description: 'Public statistics about document collection and usage',
        tags: ['Public API', 'Statistics']
    )]
    public function overview(): JsonResponse
    {
        $stats = $this->analyticsService->getPublicOverview();

        return response()->json([
            'document_counts' => [
                'total_published' => $stats['total_documents'],
                'by_type' => $stats['documents_by_type'],
                'by_year' => $stats['documents_by_year'],
                'recent_additions' => $stats['recent_count']
            ],
            'usage_statistics' => [
                'total_downloads' => $stats['total_downloads'],
                'total_views' => $stats['total_views'],
                'popular_documents' => DocumentResource::collection($stats['popular_documents']),
                'trending_searches' => $stats['trending_searches']
            ],
            'content_distribution' => [
                'top_subjects' => $stats['top_subjects'],
                'top_authors' => $stats['top_authors'],
                'legal_fields' => $stats['legal_fields_distribution']
            ],
            'system_health' => [
                'last_update' => $stats['last_document_update'],
                'jdihn_sync_status' => $stats['jdihn_status'],
                'data_freshness' => $stats['data_freshness_score']
            ],
            'generated_at' => now()->toISOString()
        ], 200, [
            'Cache-Control' => 'public, max-age=3600' // Cache for 1 hour
        ]);
    }

    #[OA\Get(
        path: '/api/v1/stats/popular',
        summary: 'Get popular content statistics',
        tags: ['Public API', 'Statistics']
    )]
    public function popular(Request $request): JsonResponse
    {
        $request->validate([
            'period' => 'in:day,week,month,year',
            'type' => 'in:documents,searches,downloads,subjects',
            'limit' => 'integer|min:1|max:100'
        ]);

        $popularStats = $this->analyticsService->getPopularContent([
            'period' => $request->get('period', 'month'),
            'type' => $request->get('type', 'documents'),
            'limit' => $request->get('limit', 10)
        ]);

        return response()->json([
            'period' => $request->get('period', 'month'),
            'type' => $request->get('type', 'documents'),
            'data' => $popularStats['data'],
            'trends' => $popularStats['trends'],
            'generated_at' => now()->toISOString()
        ]);
    }
}
```

## 4. Admin Management API (Internal)

### 4.1 Document Management API

#### **4.1.1 Admin Document Operations - `/api/v1/admin/documents`**
```php
// app/Http/Controllers/Api/V1/Admin/AdminDocumentController.php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Document\Services\DocumentManagementService;
use App\Http\Resources\Admin\AdminDocumentResource;
use App\Http\Requests\Api\Admin\StoreDocumentRequest;
use App\Http\Requests\Api\Admin\UpdateDocumentRequest;
use Illuminate\Http\JsonResponse;

class AdminDocumentController extends Controller
{
    public function __construct(
        private DocumentManagementService $documentService
    ) {
        $this->middleware('auth:sanctum');
        $this->middleware('can:manage-documents');
    }

    #[OA\Get(
        path: '/api/v1/admin/documents',
        summary: 'List all documents (admin)',
        description: 'Admin access to all documents including drafts and unpublished',
        tags: ['Admin API', 'Documents'],
        security: [['bearerAuth' => []]]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Document::class);

        $documents = $this->documentService->getAdminDocumentList([
            'status' => $request->get('status'),
            'type' => $request->get('type'),
            'author' => $request->get('author'),
            'date_range' => $request->get('date_range'),
            'compliance_score' => $request->get('compliance_score'),
            'jdihn_status' => $request->get('jdihn_status'),
            'per_page' => $request->get('per_page', 25),
            'sort' => $request->get('sort', 'updated_at'),
            'direction' => $request->get('direction', 'desc')
        ]);

        return response()->json([
            'data' => AdminDocumentResource::collection($documents->items()),
            'meta' => [
                'current_page' => $documents->currentPage(),
                'total' => $documents->total(),
                'per_page' => $documents->perPage(),
                'last_page' => $documents->lastPage()
            ],
            'filters' => $this->documentService->getAvailableFilters(),
            'bulk_actions' => $this->getAvailableBulkActions()
        ]);
    }

    #[OA\Post(
        path: '/api/v1/admin/documents',
        summary: 'Create new document',
        tags: ['Admin API', 'Documents'],
        security: [['bearerAuth' => []]]
    )]
    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $this->authorize('create', Document::class);

        $document = $this->documentService->createDocument(
            $request->validated(),
            auth()->user()
        );

        return response()->json([
            'message' => 'Document created successfully',
            'data' => new AdminDocumentResource($document)
        ], 201);
    }

    #[OA\Put(
        path: '/api/v1/admin/documents/{id}',
        summary: 'Update document',
        tags: ['Admin API', 'Documents'],
        security: [['bearerAuth' => []]]
    )]
    public function update(string $id, UpdateDocumentRequest $request): JsonResponse
    {
        $document = $this->documentService->findDocument($id);
        $this->authorize('update', $document);

        $updatedDocument = $this->documentService->updateDocument(
            $document,
            $request->validated(),
            auth()->user()
        );

        return response()->json([
            'message' => 'Document updated successfully',
            'data' => new AdminDocumentResource($updatedDocument)
        ]);
    }

    #[OA\Post(
        path: '/api/v1/admin/documents/bulk',
        summary: 'Bulk operations on documents',
        tags: ['Admin API', 'Documents'],
        security: [['bearerAuth' => []]]
    )]
    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:publish,unpublish,delete,sync_jdihn,update_status',
            'document_ids' => 'required|array|min:1|max:1000',
            'document_ids.*' => 'exists:documents,id',
            'parameters' => 'sometimes|array'
        ]);

        $this->authorize('bulkAction', Document::class);

        $result = $this->documentService->performBulkAction(
            $request->action,
            $request->document_ids,
            $request->get('parameters', []),
            auth()->user()
        );

        return response()->json([
            'message' => 'Bulk action completed',
            'results' => [
                'total_processed' => $result['total'],
                'successful' => $result['successful'],
                'failed' => $result['failed'],
                'errors' => $result['errors']
            ],
            'job_id' => $result['job_id'] // For tracking long-running operations
        ]);
    }
}
```

### 4.2 Quality Control API

#### **4.2.1 Quality Control Dashboard - `/api/v1/admin/quality`**
```php
// app/Http/Controllers/Api/V1/Admin/QualityControlController.php
namespace App\Http\Controllers\Api\V1\Admin;

class QualityControlController extends Controller
{
    public function __construct(
        private QualityControlService $qualityService,
        private JdihnValidationService $validationService
    ) {
        $this->middleware('auth:sanctum');
        $this->middleware('can:manage-quality');
    }

    #[OA\Get(
        path: '/api/v1/admin/quality/dashboard',
        summary: 'Quality control dashboard data',
        description: 'Comprehensive quality metrics and compliance status',
        tags: ['Admin API', 'Quality Control'],
        security: [['bearerAuth' => []]]
    )]
    public function dashboard(): JsonResponse
    {
        $qualityMetrics = $this->qualityService->getDashboardMetrics();

        return response()->json([
            'overview' => [
                'total_documents' => $qualityMetrics['total_documents'],
                'compliant_documents' => $qualityMetrics['compliant_documents'],
                'non_compliant_documents' => $qualityMetrics['non_compliant_documents'],
                'pending_review' => $qualityMetrics['pending_review'],
                'average_compliance_score' => $qualityMetrics['avg_compliance_score']
            ],
            'compliance_trends' => [
                'monthly_scores' => $qualityMetrics['monthly_trends'],
                'improvement_rate' => $qualityMetrics['improvement_rate'],
                'declining_rate' => $qualityMetrics['declining_rate']
            ],
            'issue_breakdown' => [
                'critical_issues' => $qualityMetrics['critical_issues'],
                'major_issues' => $qualityMetrics['major_issues'],
                'minor_issues' => $qualityMetrics['minor_issues'],
                'most_common_issues' => $qualityMetrics['common_issues']
            ],
            'jdihn_status' => [
                'sync_success_rate' => $qualityMetrics['jdihn_success_rate'],
                'failed_syncs' => $qualityMetrics['failed_syncs'],
                'pending_syncs' => $qualityMetrics['pending_syncs'],
                'last_sync' => $qualityMetrics['last_sync']
            ],
            'recommendations' => $qualityMetrics['recommendations']
        ]);
    }

    #[OA\Get(
        path: '/api/v1/admin/quality/issues',
        summary: 'Get quality issues list',
        tags: ['Admin API', 'Quality Control'],
        security: [['bearerAuth' => []]]
    )]
    public function issues(Request $request): JsonResponse
    {
        $request->validate([
            'severity' => 'in:critical,major,minor',
            'type' => 'in:metadata,content,format,compliance',
            'status' => 'in:open,resolved,ignored',
            'assignee' => 'exists:users,id'
        ]);

        $issues = $this->qualityService->getQualityIssues($request->all());

        return response()->json([
            'data' => $issues['data'],
            'meta' => $issues['meta'],
            'filters' => [
                'severity_counts' => $issues['severity_counts'],
                'type_counts' => $issues['type_counts'],
                'status_counts' => $issues['status_counts']
            ]
        ]);
    }

    #[OA\Post(
        path: '/api/v1/admin/quality/auto-fix',
        summary: 'Trigger automatic issue fixing',
        tags: ['Admin API', 'Quality Control'],
        security: [['bearerAuth' => []]]
    )]
    public function autoFix(Request $request): JsonResponse
    {
        $request->validate([
            'document_ids' => 'sometimes|array|max:100',
            'document_ids.*' => 'exists:documents,id',
            'issue_types' => 'sometimes|array',
            'issue_types.*' => 'in:metadata,format,structure',
            'dry_run' => 'boolean'
        ]);

        $fixResults = $this->qualityService->performAutoFix([
            'document_ids' => $request->get('document_ids'),
            'issue_types' => $request->get('issue_types', ['metadata', 'format']),
            'dry_run' => $request->boolean('dry_run', false),
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'fix_results' => [
                'total_documents' => $fixResults['total_documents'],
                'fixed_issues' => $fixResults['fixed_issues'],
                'remaining_issues' => $fixResults['remaining_issues'],
                'manual_intervention_required' => $fixResults['manual_required']
            ],
            'details' => $fixResults['details'],
            'dry_run' => $request->boolean('dry_run', false)
        ]);
    }
}
```

### 4.3 User Management API

#### **4.3.1 User Management - `/api/v1/admin/users`**
```php
// app/Http/Controllers/Api/V1/Admin/UserController.php
namespace App\Http\Controllers\Api\V1\Admin;

class UserController extends Controller
{
    public function __construct(
        private UserManagementService $userService
    ) {
        $this->middleware('auth:sanctum');
        $this->middleware('can:manage-users');
    }

    #[OA\Get(
        path: '/api/v1/admin/users',
        summary: 'List all users',
        tags: ['Admin API', 'Users'],
        security: [['bearerAuth' => []]]
    )]
    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->getUsers([
            'role' => $request->get('role'),
            'status' => $request->get('status'),
            'search' => $request->get('search'),
            'per_page' => $request->get('per_page', 25)
        ]);

        return response()->json([
            'data' => UserResource::collection($users->items()),
            'meta' => [
                'current_page' => $users->currentPage(),
                'total' => $users->total(),
                'per_page' => $users->perPage()
            ],
            'role_statistics' => $this->userService->getRoleStatistics()
        ]);
    }

## 5. API Resource & Data Transformation

### 5.1 JDIHN Resource Transformers

#### **5.1.1 JDIHN Document Feed Resource**
```php
// app/Http/Resources/Jdihn/JdihnDocumentFeedResource.php
namespace App\Http\Resources\Jdihn;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Domain\Jdihn\Services\JdihnMappingService;

class JdihnDocumentFeedResource extends JsonResource
{
    /**
     * Transform document to JDIHN compliant format
     * Based on existing harris-sontanu/jdih-cms patterns with enhancements
     */
    public function toArray($request): array
    {
        return [
            // Core JDIHN Fields (maintaining compatibility)
            'id' => $this->jdihnMapping?->jdihn_id ?? $this->id,
            'title' => $this->title,
            'abstract' => $this->abstract,
            'document_number' => $this->document_number,
            'document_type' => $this->transformDocumentType(),
            
            // Enhanced Metadata
            'metadata' => [
                'publication_date' => $this->published_date?->format('Y-m-d'),
                'enactment_date' => $this->enacted_date?->format('Y-m-d'),
                'language' => $this->language?->code ?? 'id',
                'region' => $this->getRegionCode(),
                'legal_field' => $this->transformLegalFields(),
                'classification' => $this->getJdihnClassification()
            ],
            
            // Content Information
            'content' => [
                'has_fulltext' => !empty($this->content),
                'page_count' => $this->page_count,
                'file_format' => $this->primaryAttachment?->mime_type,
                'file_size' => $this->primaryAttachment?->file_size
            ],
            
            // Authors & Contributors
            'authors' => $this->authors->map(function ($author) {
                return [
                    'name' => $author->name,
                    'institution' => $author->institution,
                    'role' => $author->pivot->contribution_type ?? 'author'
                ];
            }),
            
            // Subject Classification
            'subjects' => $this->subjects->map(function ($subject) {
                return [
                    'name' => $subject->name,
                    'code' => $subject->code,
                    'type' => $subject->pivot->subject_type ?? 'primary'
                ];
            }),
            
            // Quality & Compliance
            'compliance' => [
                'score' => $this->compliance_score,
                'status' => $this->getComplianceStatus(),
                'last_validated' => $this->latestValidation?->validated_at,
                'issues_count' => $this->getComplianceIssuesCount()
            ],
            
            // Synchronization Data
            'sync_info' => [
                'last_sync' => $this->last_jdihn_sync,
                'sync_status' => $this->jdihn_status,
                'version_hash' => $this->getVersionHash(),
                'needs_update' => $this->needsJdihnUpdate()
            ],
            
            // Access Information
            'access' => [
                'public_url' => route('documents.show', $this->slug),
                'download_url' => $this->primaryAttachment ? 
                    route('documents.download', $this->id) : null,
                'api_url' => route('api.documents.show', $this->id)
            ],
            
            // Statistics
            'statistics' => [
                'view_count' => $this->view_count,
                'download_count' => $this->download_count,
                'last_accessed' => $this->last_accessed_at
            ],
            
            // Audit Trail
            'audit' => [
                'created_at' => $this->created_at->toISOString(),
                'updated_at' => $this->updated_at->toISOString(),
                'created_by' => $this->creator?->name,
                'last_modified_by' => $this->updater?->name
            ]
        ];
    }
    
    /**
     * Transform document type to JDIHN format
     */
    private function transformDocumentType(): array
    {
        return [
            'code' => $this->documentType->jdihn_type_code ?? $this->documentType->slug,
            'name' => $this->documentType->name,
            'category' => $this->getJdihnCategory()
        ];
    }
    
    /**
     * Transform legal fields for JDIHN compliance
     */
    private function transformLegalFields(): array
    {
        return $this->legalFields->map(function ($field) {
            return [
                'code' => $field->code,
                'name' => $field->name,
                'level' => $field->level,
                'relevance' => $field->pivot->relevance_score ?? 1.0
            ];
        })->toArray();
    }
}
```

### 5.2 Public API Resources

#### **5.2.1 Public Document Resource**
```php
// app/Http/Resources/DocumentResource.php
namespace App\Http\Resources;

class DocumentResource extends JsonResource
{
    /**
     * Transform document for public API consumption
     * Optimized for public access with privacy considerations
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'abstract' => $this->abstract,
            'document_number' => $this->document_number,
            
            // Type & Classification
            'type' => [
                'name' => $this->documentType->name,
                'slug' => $this->documentType->slug,
                'icon' => $this->documentType->icon,
                'color' => $this->documentType->color
            ],
            
            // Publication Info
            'publication' => [
                'published_date' => $this->published_date?->format('Y-m-d'),
                'enacted_date' => $this->enacted_date?->format('Y-m-d'),
                'publisher' => $this->publisher?->name,
                'language' => [
                    'code' => $this->language->code,
                    'name' => $this->language->name
                ]
            ],
            
            // Authors (public info only)
            'authors' => $this->authors->map(function ($author) {
                return [
                    'name' => $author->name,
                    'institution' => $author->institution,
                    'slug' => $author->slug
                ];
            }),
            
            // Subjects & Topics
            'subjects' => $this->subjects->where('pivot.subject_type', 'primary')->map(function ($subject) {
                return [
                    'name' => $subject->name,
                    'slug' => $subject->slug,
                    'code' => $subject->code
                ];
            }),
            
            // File Information
            'file' => $this->when($this->primaryAttachment, function () {
                return [
                    'type' => $this->primaryAttachment->file_type,
                    'size' => $this->primaryAttachment->file_size,
                    'pages' => $this->page_count,
                    'download_url' => route('api.documents.download', $this->id)
                ];
            }),
            
            // Statistics
            'stats' => [
                'views' => $this->view_count,
                'downloads' => $this->download_count,
                'is_featured' => $this->is_featured
            ],
            
            // Timestamps
            'dates' => [
                'created_at' => $this->created_at->toISOString(),
                'updated_at' => $this->updated_at->toISOString()
            ],
            
            // Links
            'links' => [
                'self' => route('api.documents.show', $this->id),
                'web' => route('documents.show', $this->slug),
                'download' => $this->primaryAttachment ? 
                    route('api.documents.download', $this->id) : null
            ]
        ];
    }
}
```

### 5.3 Admin API Resources

#### **5.3.1 Admin Document Resource**
```php
// app/Http/Resources/Admin/AdminDocumentResource.php
namespace App\Http\Resources\Admin;

class AdminDocumentResource extends JsonResource
{
    /**
     * Transform document for admin API with full details
     * Includes sensitive data and management information
     */
    public function toArray($request): array
    {
        return [
            // Basic Information
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'abstract' => $this->abstract,
            'content' => $this->content, // Full content for admin
            'document_number' => $this->document_number,
            'call_number' => $this->call_number,
            
            // Status & Workflow
            'status' => [
                'current' => [
                    'id' => $this->documentStatus->id,
                    'name' => $this->documentStatus->name,
                    'color' => $this->documentStatus->color,
                    'is_published' => $this->documentStatus->is_published
                ],
                'workflow' => [
                    'can_edit' => auth()->user()->can('update', $this->resource),
                    'can_publish' => auth()->user()->can('publish', $this->resource),
                    'can_delete' => auth()->user()->can('delete', $this->resource),
                    'available_transitions' => $this->getAvailableStatusTransitions()
                ]
            ],
            
            // Quality & Compliance (Admin-specific)
            'quality' => [
                'compliance_score' => $this->compliance_score,
                'jdihn_status' => $this->jdihn_status,
                'last_jdihn_sync' => $this->last_jdihn_sync,
                'validation_status' => $this->latestValidation?->status,
                'issues_count' => [
                    'critical' => $this->getCriticalIssuesCount(),
                    'major' => $this->getMajorIssuesCount(),
                    'minor' => $this->getMinorIssuesCount()
                ],
                'auto_fixable_issues' => $this->getAutoFixableIssuesCount()
            ],
            
            // Complete Metadata
            'metadata' => array_merge(
                $this->metadata ?? [],
                [
                    'isbn' => $this->isbn,
                    'issn' => $this->issn,
                    'page_count' => $this->page_count,
                    'language' => $this->language->name,
                    'legal_fields' => $this->legalFields->pluck('name'),
                    'publisher' => $this->publisher?->name
                ]
            ),
            
            // Relationships (Full Admin Access)
            'authors' => $this->authors->map(function ($author) {
                return [
                    'id' => $author->id,
                    'name' => $author->name,
                    'email' => $author->email, // Admin can see email
                    'institution' => $author->institution,
                    'contribution_type' => $author->pivot->contribution_type,
                    'order' => $author->pivot->author_order
                ];
            }),
            
            'subjects' => $this->subjects->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'code' => $subject->code,
                    'type' => $subject->pivot->subject_type,
                    'relevance_score' => $subject->pivot->relevance_score
                ];
            }),
            
            // File Attachments (Admin view)
            'attachments' => $this->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'filename' => $attachment->filename,
                    'original_filename' => $attachment->original_filename,
                    'file_type' => $attachment->file_type,
                    'file_size' => $attachment->file_size,
                    'is_primary' => $attachment->is_primary,
                    'processing_status' => $attachment->processing_status,
                    'uploaded_by' => $attachment->uploader->name,
                    'uploaded_at' => $attachment->created_at->toISOString()
                ];
            }),
            
            // Version History (Admin-specific)
            'versions' => $this->versions->map(function ($version) {
                return [
                    'version_number' => $version->version_number,
                    'change_summary' => $version->change_summary,
                    'change_type' => $version->change_type,
                    'created_by' => $version->creator->name,
                    'created_at' => $version->created_at->toISOString()
                ];
            }),
            
            // Audit Information
            'audit' => [
                'created_by' => $this->creator->name,
                'created_at' => $this->created_at->toISOString(),
                'updated_by' => $this->updater?->name,
                'updated_at' => $this->updated_at->toISOString(),
                'total_revisions' => $this->versions_count,
                'last_activity' => $this->getLastActivityDate()
            ],
            
            // Statistics & Analytics
            'analytics' => [
                'view_count' => $this->view_count,
                'download_count' => $this->download_count,
                'is_featured' => $this->is_featured,
                'performance_score' => $this->calculatePerformanceScore(),
                'trending_rank' => $this->getTrendingRank()
            ],
            
            // Management Links
            'links' => [
                'edit' => route('admin.documents.edit', $this->id),
                'preview' => route('documents.show', $this->slug),
                'api' => route('api.admin.documents.show', $this->id),
                'jdihn_status' => route('api.admin.jdihn.document-status', $this->id)
            ]
        ];
    }
}
```

---
    {
        $user = $this->userService->createUser(
            $request->validated(),
            auth()->user()
        );

        return response()->json([
            'message' => 'User created successfully',
            'data' => new UserResource($user)
        ], 201);
    }

    #[OA\Put(
        path: '/api/v1/admin/users/{id}/roles',
        summary: 'Update user roles',
        tags: ['Admin API', 'Users'],
        security: [['bearerAuth' => []]]
    )]
    public function updateRoles(string $id, Request $request): JsonResponse
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name'
        ]);

        $user = $this->userService->updateUserRoles(
            $id,
            $request->roles,
            auth()->user()
        );

        return response()->json([
            'message' => 'User roles updated successfully',
            'data' => new UserResource($user)
## 6. API Security & Authentication

### 6.1 Authentication Strategy

#### **6.1.1 Multi-Layer Authentication**
```php
// config/auth.php - Enhanced Authentication Configuration
return [
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'api' => [
            'driver' => 'sanctum',
            'provider' => 'users',
        ],
        'jdihn' => [
            'driver' => 'custom',
            'provider' => 'jdihn_api_keys',
        ]
    ],
    
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
        'jdihn_api_keys' => [
            'driver' => 'custom',
            'model' => App\Models\ApiKey::class,
        ]
    ]
];
```

#### **6.1.2 API Token Management**
```php
// app/Http/Middleware/ApiKeyAuthentication.php
namespace App\Http\Middleware;

use Closure;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ApiKeyAuthentication
{
    public function handle(Request $request, Closure $next, string $scope = null)
    {
        $apiKey = $request->header('X-API-Key') ?? $request->get('api_key');
        
        if (!$apiKey) {
            return response()->json(['error' => 'API key required'], 401);
        }
        
        $keyModel = ApiKey::where('key', hash('sha256', $apiKey))
                         ->where('is_active', true)
                         ->where('expires_at', '>', now())
                         ->first();
                         
        if (!$keyModel) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }
        
        // Scope validation
        if ($scope && !in_array($scope, $keyModel->scopes)) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }
        
        // Rate limiting per API key
        $rateLimitKey = 'api_rate_limit:' . $keyModel->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, $keyModel->rate_limit_per_minute)) {
            return response()->json([
                'error' => 'Rate limit exceeded',
                'retry_after' => RateLimiter::availableIn($rateLimitKey)
            ], 429);
        }
        
        RateLimiter::hit($rateLimitKey, 60);
        
        // Track usage
        $keyModel->increment('usage_count');
        $keyModel->touch('last_used_at');
        
        $request->attributes->set('api_key', $keyModel);
        
        return $next($request);
    }
}
```

### 6.2 Rate Limiting & Throttling

#### **6.2.1 Dynamic Rate Limiting**
```php
// app/Http/Middleware/DynamicRateLimiter.php
namespace App\Http\Middleware;

class DynamicRateLimiter
{
    public function handle(Request $request, Closure $next, string $limiterName = 'api')
    {
        $user = $request->user();
        $apiKey = $request->attributes->get('api_key');
        
        // Different limits based on authentication method
        $limits = $this->getLimitsForRequest($request, $user, $apiKey);
        
        foreach ($limits as $limitKey => $limitValue) {
            $rateLimitKey = $limitKey . ':' . $this->getIdentifier($request, $user, $apiKey);
            
            if (RateLimiter::tooManyAttempts($rateLimitKey, $limitValue['attempts'])) {
                return $this->buildRateLimitResponse($limitKey, $rateLimitKey, $limitValue);
            }
            
            RateLimiter::hit($rateLimitKey, $limitValue['decay']);
        }
        
        return $next($request);
    }
    
    private function getLimitsForRequest($request, $user, $apiKey): array
    {
        // JDIHN endpoints - higher limits for government systems
        if ($request->is('api/v1/jdihn/*')) {
            return [
                'jdihn' => ['attempts' => 1000, 'decay' => 60] // 1000 requests per minute
            ];
        }
        
        // Admin API - based on user role
        if ($request->is('api/v1/admin/*')) {
            $roleMultiplier = match($user?->getRoleNames()?->first()) {
                'super-admin' => 5,
                'admin' => 3,
                'editor' => 2,
                default => 1
            };
            
            return [
                'admin' => ['attempts' => 100 * $roleMultiplier, 'decay' => 60]
            ];
        }
        
        // Public API - basic limits
        return [
            'public' => ['attempts' => 60, 'decay' => 60], // 60 requests per minute
            'search' => ['attempts' => 30, 'decay' => 60]  // 30 searches per minute
        ];
    }
}
```

### 6.3 Request Validation

#### **6.3.1 API Request Validation**
```php
// app/Http/Requests/Api/JdihnFeedRequest.php
namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class JdihnFeedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }
    
    public function rules(): array
    {
        return [
            'limit' => 'integer|min:1|max:1000',
            'offset' => 'integer|min:0',
            'updated_since' => 'date_format:Y-m-d\TH:i:s\Z|before:now',
            'document_type' => 'string|in:peraturan,putusan,monografi,artikel',
            'region_code' => [
                'string',
                'regex:/^[0-9]{2}(\.[0-9]{2})*$/', // Province.City.District format
                'exists:regions,code'
            ],
            'year' => 'integer|min:1945|max:' . (date('Y') + 1),
            'compliance_only' => 'boolean',
            'format' => 'in:json,xml',
            'include' => 'string|regex:/^[a-zA-Z,_]+$/' // comma-separated includes
        ];
    }
    
    public function messages(): array
    {
        return [
            'updated_since.date_format' => 'Date must be in ISO 8601 format (YYYY-MM-DDTHH:MM:SSZ)',
            'region_code.regex' => 'Region code must follow Indonesian administrative format (XX.YY.ZZ)',
            'year.min' => 'Year cannot be before Indonesian independence (1945)',
            'document_type.in' => 'Document type must be one of: peraturan, putusan, monografi, artikel'
        ];
    }
    
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            // Custom validation: limit + offset should not exceed reasonable bounds
            if (($this->get('limit', 100) + $this->get('offset', 0)) > 50000) {
                $validator->errors()->add('pagination', 'Combined limit and offset too large');
            }
            
            // Validate include parameter if provided
            if ($this->has('include')) {
                $allowedIncludes = ['authors', 'subjects', 'attachments', 'versions', 'compliance'];
                $requestedIncludes = explode(',', $this->get('include'));
                $invalidIncludes = array_diff($requestedIncludes, $allowedIncludes);
                
                if (!empty($invalidIncludes)) {
                    $validator->errors()->add('include', 
                        'Invalid includes: ' . implode(', ', $invalidIncludes));
                }
            }
        });
    }
    
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'error' => 'Validation failed',
                'message' => 'The request contains invalid parameters',
                'errors' => $validator->errors()->toArray(),
                'documentation' => config('app.api_docs_url') . '/jdihn-feed'
            ], 422)
        );
    }
}
```

### 6.4 API Response Standards

#### **6.4.1 Consistent Response Format**
```php
// app/Http/Middleware/FormatApiResponse.php
namespace App\Http\Middleware;

class FormatApiResponse
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Only format JSON API responses
        if (!$request->wantsJson() || !$response instanceof JsonResponse) {
            return $response;
        }
        
        $data = $response->getData(true);
        $statusCode = $response->getStatusCode();
        
        // Format successful responses
        if ($statusCode >= 200 && $statusCode < 300) {
            $formattedData = [
                'success' => true,
                'data' => $data['data'] ?? $data,
                'meta' => array_merge(
                    $data['meta'] ?? [],
                    [
                        'timestamp' => now()->toISOString(),
                        'version' => 'v1',
                        'request_id' => $request->header('X-Request-ID') ?? Str::uuid()
                    ]
                )
            ];
            
            // Add pagination links if present
            if (isset($data['links'])) {
                $formattedData['links'] = $data['links'];
            }
            
            // Add additional metadata for JDIHN endpoints
            if ($request->is('api/v1/jdihn/*')) {
                $formattedData['meta']['jdihn_version'] = '2024.1';
                $formattedData['meta']['compliance_checked'] = true;
                $formattedData['meta']['data_source'] = config('app.name');
            }
            
            return response()->json($formattedData, $statusCode)
                             ->withHeaders($this->getStandardHeaders($request));
        }
        
        // Format error responses
        if ($statusCode >= 400) {
            $formattedData = [
                'success' => false,
                'error' => [
                    'code' => $statusCode,
                    'message' => $data['message'] ?? $this->getDefaultErrorMessage($statusCode),
                    'details' => $data['errors'] ?? null,
                    'type' => $this->getErrorType($statusCode)
                ],
                'meta' => [
                    'timestamp' => now()->toISOString(),
                    'request_id' => $request->header('X-Request-ID') ?? Str::uuid(),
                    'documentation' => config('app.api_docs_url')
                ]
            ];
            
            return response()->json($formattedData, $statusCode)
                             ->withHeaders($this->getStandardHeaders($request));
        }
        
        return $response;
    }
    
    private function getStandardHeaders(Request $request): array
    {
        return [
            'X-API-Version' => 'v1',
            'X-Rate-Limit' => $this->getRateLimitInfo($request),
            'X-Response-Time' => round((microtime(true) - LARAVEL_START) * 1000, 2) . 'ms',
            'Cache-Control' => $this->getCacheControlHeader($request)
        ];
    }
    
    private function getCacheControlHeader(Request $request): string
    {
        // JDIHN feeds can be cached longer
        if ($request->is('api/v1/jdihn/*')) {
            return 'public, max-age=300'; // 5 minutes
        }
        
        // Admin endpoints should not be cached
        if ($request->is('api/v1/admin/*')) {
            return 'private, no-cache, no-store, must-revalidate';
        }
        
        // Public data can be cached briefly
        return 'public, max-age=60'; // 1 minute
    }
}
```

---
        return Cache::remember($cacheKey, 300, function () use ($parameters) {
            $query = Document::with([
                'documentType',
                'authors',
                'subjects', 
                'region',
                'attachments'
            ])->where('is_published', true);

            // Apply filters
            if ($parameters['updated_since']) {
                $query->where('updated_at', '>=', $parameters['updated_since']);
            }

            if ($parameters['document_type']) {
                $query->whereHas('documentType', function ($q) use ($parameters) {
                    $q->where('name', 'like', '%' . $parameters['document_type'] . '%');
                });
            }

            if ($parameters['region']) {
                $query->whereHas('region', function ($q) use ($parameters) {
                    $q->where('name', 'like', '%' . $parameters['region'] . '%');
                });
            }

            if ($parameters['year']) {
                $query->where('publication_year', $parameters['year']);
            }

            // Get total count before pagination
            $totalRecords = $query->count();

            // Apply pagination
            $documents = $query->offset($parameters['offset'])
                             ->limit($parameters['limit'])
                             ->orderBy('updated_at', 'desc')
                             ->get();

            // Transform to JDIHN format
            $feedData = $documents->map(function ($document) {
                return $this->transformDocumentToJdihnFormat($document);
            });

            return [
                'totalRecords' => $totalRecords,
                'returnedRecords' => $feedData->count(),
                'offset' => $parameters['offset'],
                'limit' => $parameters['limit'],
                'lastUpdated' => now()->toISOString(),
                'data' => $feedData->toArray(),
            ];
        });
    }

    /**
     * Generate JDIHN compliant abstrak feed
     * 
     * @param array $parameters
     * @return array
     */
    public function generateAbstrakFeed(array $parameters): array
    {
        $cacheKey = 'jdihn_abstrak_feed_' . md5(serialize($parameters));
        
        return Cache::remember($cacheKey, 300, function () use ($parameters) {
            $query = Document::with([
                'documentType',
                'subjects',
                'authors'
            ])->where('is_published', true)
              ->whereNotNull('abstract');

            // Apply filters
            if ($parameters['updated_since']) {
                $query->where('updated_at', '>=', $parameters['updated_since']);
            }

            if ($parameters['document_id']) {
                $query->where('id', $parameters['document_id']);
            }

            $totalRecords = $query->count();

            $documents = $query->offset($parameters['offset'])
                             ->limit($parameters['limit'])
                             ->orderBy('updated_at', 'desc')
                             ->get();

            $feedData = $documents->map(function ($document) {
                return $this->transformAbstrakToJdihnFormat($document);
            });

            return [
                'totalRecords' => $totalRecords,
                'returnedRecords' => $feedData->count(),
                'offset' => $parameters['offset'],
                'limit' => $parameters['limit'],
                'lastUpdated' => now()->toISOString(),
                'data' => $feedData->toArray(),
            ];
        });
    }

    /**
     * Transform document to JDIHN format
     * 
     * @param Document $document
     * @return array
     */
    private function transformDocumentToJdihnFormat(Document $document): array
    {
        return [
            // Basic Information (Required)
            'idData' => $document->id,
            'judul' => $document->title,
            'teu' => $document->teu ?? '',
            
            // Document Classification
            'jenis' => $document->regulation_type ?? $document->documentType->name ?? '',
            'tipeData' => $this->mapDocumentTypeToJdihn($document->documentType),
            
            // Numbers and Identifiers
            'noPeraturan' => $document->document_number ?? '',
            'noPanggil' => $document->call_number ?? '',
            'singkatanJenis' => $document->regulation_abbreviation ?? '',
            
            // Dates (ISO 8601 format)
            'tahunPengundangan' => $document->publication_year ?? '',
            'tanggalPenetapan' => $document->enactment_date?->format('Y-m-d') ?? '',
            'tanggalPengundangan' => $document->promulgation_date?->format('Y-m-d') ?? '',
            'tanggalDibacakan' => $document->reading_date?->format('Y-m-d') ?? '',
            
            // Publication Information
            'tempatTerbit' => $document->place_published ?? '',
            'penerbit' => $document->publisher ?? '',
            'deskripsiFisik' => $document->physical_description ?? '',
            'sumber' => $document->source ?? '',
            'isbn' => $document->isbn ?? '',
            
            // Legal Information
            'status' => $this->mapStatusToJdihn($document->status),
            'bahasa' => $document->language ?? 'id',
            'bidangHukum' => $document->legal_field ?? '',
            
            // Additional Metadata
            'abstrak' => $document->abstract ?? '',
            'urusan' => $document->government_affairs ?? '',
            'inisiatif' => $document->initiative ?? '',
            'pemrakarsa' => $document->initiator ?? '',
            
            // Authors/T.E.U Information
            'pengarang' => $this->formatAuthorsForJdihn($document->authors),
            'subjek' => $this->formatSubjectsForJdihn($document->subjects),
            
            // File Information
            'berkas' => $this->formatAttachmentsForJdihn($document->attachments),
            
            // Statistics
            'hit' => [
                'lihat' => $document->hit_view ?? 0,
                'unduh' => $document->hit_download ?? 0,
            ],
            
            // Regional Information
            'lokasi' => [
                'provinsi' => $document->region?->name ?? '',
                'kodeProvinsi' => $document->region?->code ?? '',
            ],
            
            // Timestamps
            'dibuat' => $document->created_at->toISOString(),
            'diubah' => $document->updated_at->toISOString(),
            
            // Integration Metadata
            'integrasi' => [
                'sumber' => 'ILDIS',
                'versi' => config('app.version'),
                'checksum' => $this->generateChecksum($document),
            ],
        ];
    }

    /**
     * Transform abstrak to JDIHN format
     * 
     * @param Document $document
     * @return array
     */
    private function transformAbstrakToJdihnFormat(Document $document): array
    {
        return [
            'idData' => $document->id,
            'idDokumen' => $document->id,
            'judul' => $document->title,
            'singkatan' => $document->regulation_abbreviation ?? '',
            'tahun' => $document->publication_year ?? '',
            'subjek' => $this->formatSubjectsForJdihn($document->subjects, 'string'),
            
            // Abstrak content (multi-level)
            'isiAbstrak1' => $this->extractAbstrakLevel($document->abstract, 1),
            'isiAbstrak2' => $this->extractAbstrakLevel($document->abstract, 2),
            'isiAbstrak3' => $this->extractAbstrakLevel($document->abstract, 3),
            
            // Additional notes
            'catatan1' => $document->regulation_status_notes ?? '',
            'catatan2' => '',
            'catatan3' => '',
            'catatan4' => '',
            'catatan5' => '',
            
            // Timestamps
            'dibuat' => $document->created_at->toISOString(),
            'diubah' => $document->updated_at->toISOString(),
        ];
    }

    /**
     * Map document type to JDIHN standard
     * 
     * @param $documentType
     * @return string
     */
    private function mapDocumentTypeToJdihn($documentType): string
    {
        if (!$documentType) {
            return 'lainnya';
        }

## 7. API Documentation & Testing

### 7.1 OpenAPI 3.0 Specification

#### **7.1.1 API Documentation Structure**
```yaml
# /docs/api/openapi.yaml
openapi: 3.0.3
info:
  title: ILDIS API
  description: |
    Indonesian Legal Documentation Information System API
    
    Enhanced API with JDIHN integration, quality control, and modern features.
    Built on Laravel 12.x framework with comprehensive documentation.
    
    ## Authentication
    - **API Key**: Required for JDIHN integration endpoints
    - **Bearer Token**: Required for admin operations (Laravel Sanctum)
    - **Session**: Available for web-based authentication
    
    ## Rate Limiting
    - Public API: 60 requests/minute
    - JDIHN API: 1000 requests/minute 
    - Admin API: Variable based on user role
    
    ## Data Formats
    - All dates in ISO 8601 format (YYYY-MM-DDTHH:MM:SSZ)
    - All responses include metadata and request tracking
    - JDIHN endpoints follow SATU DATA HUKUM standards
  version: '2.0'
  contact:
    name: ILDIS Development Team
    email: dev@bphndigitalservice.com
    url: 'https://bphndigitalservice.com'
  license:
    name: MIT
    url: 'https://opensource.org/licenses/MIT'

servers:
  - url: 'https://api.ildis.bphn.go.id/v1'
    description: Production API
  - url: 'https://staging-api.ildis.bphn.go.id/v1' 
    description: Staging API
  - url: 'http://localhost:8000/api/v1'
    description: Local Development

security:
  - ApiKey: []
  - BearerAuth: []

paths:
  # JDIHN Integration Endpoints
  /jdihn/documents:
    get:
      tags: [JDIHN Integration]
      summary: JDIHN Document Feed
      description: |
        Generate JDIHN compliant document feed for SATU DATA HUKUM INDONESIA.
        Enhanced version of existing feed with quality validation and monitoring.
      operationId: getJdihnDocumentFeed
      parameters:
        - name: limit
          in: query
          schema:
            type: integer
            minimum: 1
            maximum: 1000
            default: 100
        - name: offset
          in: query
          schema:
            type: integer
            minimum: 0
            default: 0
        - name: updated_since
          in: query
          schema:
            type: string
            format: date-time
            example: '2024-01-01T00:00:00Z'
        - name: compliance_only
          in: query
          schema:
            type: boolean
            default: true
      responses:
        '200':
          description: JDIHN compliant document feed
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/JdihnDocumentFeedResponse'
              example:
                success: true
                data: []
                meta:
                  version: "2.0"
                  generated_at: "2024-09-25T10:00:00Z"
                  total_records: 150
                  compliance_checked: true
                  data_format: "JDIHN-2024"
        '422':
          $ref: '#/components/responses/ValidationError'
        '429':
          $ref: '#/components/responses/RateLimitError'

components:
  securitySchemes:
    ApiKey:
      type: apiKey
      in: header
      name: X-API-Key
      description: API key for JDIHN integration
    BearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT
      description: Laravel Sanctum token for admin operations
      
  schemas:
    JdihnDocumentFeedResponse:
      type: object
      properties:
        success:
          type: boolean
          example: true
        data:
          type: array
          items:
            $ref: '#/components/schemas/JdihnDocument'
        meta:
          $ref: '#/components/schemas/FeedMetadata'
        links:
          $ref: '#/components/schemas/PaginationLinks'
          
    JdihnDocument:
      type: object
      description: JDIHN compliant document structure
      properties:
        id:
          type: string
          description: Document identifier
        title:
          type: string
          maxLength: 500
        abstract:
          type: string
          nullable: true
        document_number:
          type: string
          nullable: true
        document_type:
          $ref: '#/components/schemas/DocumentType'
        metadata:
          $ref: '#/components/schemas/DocumentMetadata'
        authors:
          type: array
          items:
            $ref: '#/components/schemas/Author'
        subjects:
          type: array
          items:
            $ref: '#/components/schemas/Subject'
        compliance:
          $ref: '#/components/schemas/ComplianceInfo'
        sync_info:
          $ref: '#/components/schemas/SyncInfo'
          
    DocumentType:
      type: object
      properties:
        code:
          type: string
          enum: [peraturan, putusan, monografi, artikel]
        name:
          type: string
        category:
          type: string
          
    ComplianceInfo:
      type: object
      properties:
        score:
          type: number
          format: float
          minimum: 0
          maximum: 1
          description: Compliance score (0.00-1.00)
        status:
          type: string
          enum: [valid, invalid, warning]
        last_validated:
          type: string
          format: date-time
        issues_count:
          type: integer
          minimum: 0

  responses:
    ValidationError:
      description: Request validation failed
      content:
        application/json:
          schema:
            type: object
            properties:
              success:
                type: boolean
                example: false
              error:
                type: object
                properties:
                  code:
                    type: integer
                    example: 422
                  message:
                    type: string
                    example: "Validation failed"
                  details:
                    type: object
                    additionalProperties:
                      type: array
                      items:
                        type: string
                  type:
                    type: string
                    example: "validation_error"

    RateLimitError:
      description: Rate limit exceeded
      content:
        application/json:
          schema:
            type: object
            properties:
              success:
                type: boolean
                example: false
              error:
                type: object
                properties:
                  code:
                    type: integer
                    example: 429
                  message:
                    type: string
                    example: "Rate limit exceeded"
                  retry_after:
                    type: integer
                    description: Seconds until reset
```

### 7.2 API Testing Strategy

#### **7.2.1 Automated API Testing**
```php
// tests/Feature/Api/JdihnIntegrationTest.php
namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Document;
use App\Models\ApiKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class JdihnIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    
    private ApiKey $apiKey;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->apiKey = ApiKey::factory()->create([
            'scopes' => ['jdihn:read', 'jdihn:write'],
            'rate_limit_per_minute' => 1000
        ]);
    }
    
    /** @test */
    public function it_returns_jdihn_compliant_document_feed()
    {
        // Arrange
        $documents = Document::factory()
            ->published()
            ->compliant()
            ->count(5)
            ->create();
            
        // Act
        $response = $this->getJson('/api/v1/jdihn/documents', [
            'X-API-Key' => $this->apiKey->key
        ]);
        
        // Assert
        $response->assertOk()
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => [
                             'id',
                             'title',
                             'abstract',
                             'document_type' => ['code', 'name'],
                             'metadata',
                             'authors',
                             'subjects',
                             'compliance' => ['score', 'status'],
                             'sync_info'
                         ]
                     ],
                     'meta' => [
                         'version',
                         'generated_at',
                         'total_records',
                         'compliance_checked'
                     ]
                 ]);
                 
        $this->assertCount(5, $response->json('data'));
        $this->assertTrue($response->json('success'));
        $this->assertEquals('2.0', $response->json('meta.version'));
    }
    
    /** @test */
    public function it_validates_request_parameters()
    {
        $response = $this->getJson('/api/v1/jdihn/documents?limit=2000', [
            'X-API-Key' => $this->apiKey->key
        ]);
        
        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => [
                         'code' => 422,
                         'type' => 'validation_error'
                     ]
                 ]);
    }
    
    /** @test */
    public function it_enforces_rate_limiting()
    {
        // Set a low rate limit for testing
        $this->apiKey->update(['rate_limit_per_minute' => 2]);
        
        // Make requests up to the limit
        for ($i = 0; $i < 2; $i++) {
            $response = $this->getJson('/api/v1/jdihn/documents', [
                'X-API-Key' => $this->apiKey->key
            ]);
            $response->assertOk();
        }
        
        // Next request should be rate limited
        $response = $this->getJson('/api/v1/jdihn/documents', [
            'X-API-Key' => $this->apiKey->key
        ]);
        
        $response->assertStatus(429)
                 ->assertJsonStructure([
                     'success',
                     'error' => [
                         'code',
                         'message'
                     ]
                 ]);
    }
    
    /** @test */
    public function it_filters_documents_by_compliance_status()
    {
        // Create compliant and non-compliant documents
        Document::factory()->compliant()->count(3)->create();
        Document::factory()->nonCompliant()->count(2)->create();
        
        // Request only compliant documents
        $response = $this->getJson('/api/v1/jdihn/documents?compliance_only=true', [
            'X-API-Key' => $this->apiKey->key
        ]);
        
        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
        
        // Verify all returned documents are compliant
        foreach ($response->json('data') as $document) {
            $this->assertGreaterThanOrEqual(0.8, $document['compliance']['score']);
            $this->assertEquals('valid', $document['compliance']['status']);
        }
    }
    
    /** @test */
    public function it_handles_document_type_filtering()
    {
        Document::factory()->type('peraturan')->count(3)->create();
        Document::factory()->type('putusan')->count(2)->create();
        
        $response = $this->getJson('/api/v1/jdihn/documents?document_type=peraturan', [
            'X-API-Key' => $this->apiKey->key
        ]);
        
        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $document) {
            $this->assertEquals('peraturan', $document['document_type']['code']);
        }
    }
    
    /** @test */
    public function it_tracks_api_usage_statistics()
    {
        $initialUsage = $this->apiKey->usage_count;
        
        $this->getJson('/api/v1/jdihn/documents', [
            'X-API-Key' => $this->apiKey->key
        ]);
        
        $this->apiKey->refresh();
        $this->assertEquals($initialUsage + 1, $this->apiKey->usage_count);
        $this->assertNotNull($this->apiKey->last_used_at);
    }
}
```

#### **7.2.2 Performance Testing**
```php
// tests/Performance/ApiPerformanceTest.php
namespace Tests\Performance;

class ApiPerformanceTest extends TestCase
{
    /** @test */
    public function jdihn_feed_performance_test()
    {
        // Create large dataset
        Document::factory()->count(1000)->create();
        
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/v1/jdihn/documents?limit=100', [
            'X-API-Key' => $this->apiKey->key
        ]);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // in milliseconds
        
        $response->assertOk();
        
        // Assert response time is under 2 seconds
        $this->assertLessThan(2000, $responseTime, 
            "API response took {$responseTime}ms, expected under 2000ms");
            
        // Check response includes performance metadata
        $this->assertArrayHasKey('X-Response-Time', $response->headers->all());
    }
    
    /** @test */
    public function search_api_performance_test()
    {
        Document::factory()->count(5000)->create();
        
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/v1/search?q=test&per_page=20');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        
        $response->assertOk();
        
        // Search should be under 500ms
        $this->assertLessThan(500, $responseTime,
            "Search response took {$responseTime}ms, expected under 500ms");
    }
}
```

---
        $mapping = [
            'published' => 'berlaku',
            'draft' => 'draft',
            'review' => 'review',
            'archived' => 'dicabut',
            'amended' => 'diubah',
        ];

        return $mapping[$status] ?? 'tidak_diketahui';
    }

    /**
     * Format authors for JDIHN
     * 
     * @param $authors
     * @return array
     */
    private function formatAuthorsForJdihn($authors): array
    {
        if (!$authors || $authors->isEmpty()) {
            return [];
        }

        return $authors->map(function ($author) {
            return [
                'nama' => $author->name,
                'tipe' => $author->pivot->author_type ?? 'pengarang',
                'jenis' => $author->pivot->author_category ?? 'orang',
            ];
        })->toArray();
    }

    /**
     * Format subjects for JDIHN
     * 
     * @param $subjects
     * @param string $format
     * @return array|string
     */
    private function formatSubjectsForJdihn($subjects, string $format = 'array')
    {
        if (!$subjects || $subjects->isEmpty()) {
            return $format === 'array' ? [] : '';
        }

        $subjectData = $subjects->map(function ($subject) {
            return [
                'nama' => $subject->name,
                'tipe' => $subject->pivot->subject_type ?? 'utama',
            ];
        });

        if ($format === 'string') {
            return $subjectData->pluck('nama')->implode(', ');
        }

        return $subjectData->toArray();
    }

    /**
     * Format attachments for JDIHN
     * 
     * @param $attachments
     * @return array
     */
    private function formatAttachmentsForJdihn($attachments): array
    {
        if (!$attachments || $attachments->isEmpty()) {
            return [];
        }

        return $attachments->map(function ($attachment) {
            return [
                'namaFile' => $attachment->original_name,
                'ukuran' => $attachment->file_size,
                'tipe' => $attachment->mime_type,
                'url' => route('documents.download', [
                    'document' => $attachment->document_id,
                    'attachment' => $attachment->id
                ]),
                'deskripsi' => $attachment->description ?? '',
            ];
        })->toArray();
    }

    /**
     * Extract abstrak by level
     * 
     * @param string|null $abstract
     * @param int $level
     * @return string
     */
    private function extractAbstrakLevel(?string $abstract, int $level): string
    {
        if (!$abstract) {
            return '';
        }

        // Split by paragraphs or sections
        $sections = explode("\n\n", $abstract);
        
        return $sections[$level - 1] ?? '';
    }

    /**
     * Generate checksum for document integrity
     * 
     * @param Document $document
     * @return string
     */
    private function generateChecksum(Document $document): string
    {
        $data = [
            $document->id,
            $document->title,
            $document->updated_at->timestamp,
        ];

        return hash('sha256', implode('|', $data));
    }

    /**
     * Get API statistics
     * 
     * @return array
     */
    public function getApiStatistics(): array
    {
        return Cache::remember('jdihn_api_stats', 3600, function () {
            return [
                'totalDocuments' => Document::where('is_published', true)->count(),
                'documentsByType' => Document::where('is_published', true)
                    ->join('document_types', 'documents.document_type_id', '=', 'document_types.id')
                    ->selectRaw('document_types.name as type, COUNT(*) as count')
                    ->groupBy('document_types.name')
                    ->pluck('count', 'type')
                    ->toArray(),
                'recentUpdates' => Document::where('is_published', true)
                    ->where('updated_at', '>=', now()->subDays(7))
                    ->count(),
                'lastSync' => now()->toISOString(),
            ];
        });
    }

    /**
     * Get JDIHN metadata schema
     * 
     * @return array
     */
    public function getMetadataSchema(): array
    {
        return [
            'version' => '2.0',
            'encoding' => 'UTF-8',
            'documentTypes' => [
                'peraturan' => [
                    'required' => ['idData', 'judul', 'jenis', 'noPeraturan'],
                    'optional' => ['teu', 'tanggalPenetapan', 'tanggalPengundangan']
                ],
                'putusan' => [
                    'required' => ['idData', 'judul', 'jenis', 'lembagaPeradilan'],
                    'optional' => ['pemohon', 'termohon', 'jenisPerkara']
                ],
                'monografi' => [
                    'required' => ['idData', 'judul', 'pengarang', 'penerbit'],
                    'optional' => ['isbn', 'tahunTerbit', 'tempatTerbit']
                ],
                'artikel' => [
                    'required' => ['idData', 'judul', 'pengarang'],
                    'optional' => ['publikasi', 'volume', 'nomor']
                ]
            ],
            'fieldDefinitions' => $this->getFieldDefinitions(),
        ];
    }

    /**
     * Get field definitions for JDIHN schema
     * 
     * @return array
     */
    private function getFieldDefinitions(): array
    {
        return [
            'idData' => [
                'type' => 'integer',
                'description' => 'Unique identifier for the document',
                'required' => true
            ],
            'judul' => [
                'type' => 'string',
                'maxLength' => 500,
                'description' => 'Document title',
                'required' => true
            ],
            'teu' => [
                'type' => 'string',
                'description' => 'Tempat, Entitas, Unit information',
                'format' => '[Tempat], [Entitas], [Unit]'
            ],
            'noPeraturan' => [
                'type' => 'string',
                'maxLength' => 100,
                'description' => 'Regulation number'
            ],
            'jenis' => [
                'type' => 'string',
                'maxLength' => 200,
                'description' => 'Document type/regulation type'
            ],
            'tanggalPenetapan' => [
                'type' => 'date',
                'format' => 'Y-m-d',
                'description' => 'Enactment date'
            ],
            'tanggalPengundangan' => [
                'type' => 'date', 
                'format' => 'Y-m-d',
                'description' => 'Promulgation date'
            ],
            'status' => [
                'type' => 'enum',
                'values' => ['berlaku', 'dicabut', 'diubah', 'draft', 'review'],
                'description' => 'Legal status of the document'
            ],
            'bahasa' => [
                'type' => 'string',
                'maxLength' => 5,
                'default' => 'id',
                'description' => 'Language code (ISO 639-1)'
            ]
        ];
    }
}
```

---

## 3. Sync Scheduler Implementation

### 3.1 Automatic Sync Jobs

```php
// app/Jobs/SyncDocumentToJdihn.php
namespace App\Jobs;

use App\Domain\Document\Models\Document;
use App\Domain\Document\Services\JdihnIntegrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class SyncDocumentToJdihn implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    public function __construct(
        private Document $document,
        private string $operation = 'update'
    ) {}

    public function handle(JdihnIntegrationService $jdihnService)
    {
        try {
            Log::info("Starting JDIHN sync for document {$this->document->id}");

            // Generate JDIHN data
            $jdihnData = $jdihnService->transformDocumentToJdihnFormat($this->document);

            // Store/Update JDIHN feed record
            $this->storeJdihnFeed($jdihnData);

            // Mark as synced
            $this->document->update([
                'last_jdihn_sync' => now(),
                'jdihn_sync_status' => 'synced'
            ]);

            Log::info("JDIHN sync completed for document {$this->document->id}");

        } catch (Exception $e) {
            Log::error("JDIHN sync failed for document {$this->document->id}: " . $e->getMessage());

            $this->document->update([
                'jdihn_sync_status' => 'failed',
                'jdihn_sync_error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    private function storeJdihnFeed(array $jdihnData)
    {
        \DB::table('jdihn_feeds')->updateOrInsert(
            [
                'document_id' => $this->document->id,
                'feed_type' => 'document'
            ],
            [
                'json_data' => json_encode($jdihnData, JSON_UNESCAPED_UNICODE),
                'last_sync_at' => now(),
                'sync_status' => 'completed',
                'updated_at' => now()
            ]
        );
    }

    public function failed(Exception $exception)
    {
        Log::error("JDIHN sync permanently failed for document {$this->document->id}: " . $exception->getMessage());

        $this->document->update([
            'jdihn_sync_status' => 'failed',
            'jdihn_sync_error' => $exception->getMessage()
        ]);
    }
}
```

### 3.2 Bulk Sync Command

```php
// app/Console/Commands/SyncJdihnBulkCommand.php
namespace App\Console\Commands;

use App\Domain\Document\Models\Document;
use App\Jobs\SyncDocumentToJdihn;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncJdihnBulkCommand extends Command
{
    protected $signature = 'jdihn:sync-bulk 
                           {--limit=100 : Number of documents to sync}
                           {--force : Force sync even if recently synced}
                           {--type= : Document type to sync}';

    protected $description = 'Bulk sync documents to JDIHN';

    public function handle()
    {
        $limit = $this->option('limit');
        $force = $this->option('force');
        $type = $this->option('type');

        $this->info("Starting bulk JDIHN sync (limit: {$limit})");

        $query = Document::where('is_published', true);

        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('last_jdihn_sync')
                  ->orWhere('last_jdihn_sync', '<', now()->subHours(1));
            });
        }

        if ($type) {
            $query->whereHas('documentType', function ($q) use ($type) {
                $q->where('name', 'like', "%{$type}%");
            });
        }

        $documents = $query->limit($limit)->get();

        $this->info("Found {$documents->count()} documents to sync");

        $bar = $this->output->createProgressBar($documents->count());
        $bar->start();

        $synced = 0;
        $failed = 0;

        foreach ($documents as $document) {
            try {
                SyncDocumentToJdihn::dispatch($document);
                $synced++;
            } catch (\Exception $e) {
                $this->error("Failed to queue sync for document {$document->id}: " . $e->getMessage());
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("Sync jobs queued - Synced: {$synced}, Failed: {$failed}");

        Log::info("JDIHN bulk sync completed", [
            'synced' => $synced,
            'failed' => $failed,
            'total' => $documents->count()
        ]);

        return 0;
    }
}
```

---

## 4. Real-time Event Listeners

### 4.1 Document Event Listeners

```php
// app/Listeners/SyncDocumentToJdihnListener.php
namespace App\Listeners;

use App\Domain\Document\Events\DocumentCreated;
use App\Domain\Document\Events\DocumentUpdated;
use App\Domain\Document\Events\DocumentPublished;
use App\Jobs\SyncDocumentToJdihn;
use Illuminate\Events\Dispatcher;

class SyncDocumentToJdihnListener
{
    public function handleDocumentCreated(DocumentCreated $event)
    {
        if ($event->document->is_published) {
            SyncDocumentToJdihn::dispatch($event->document, 'create');
        }
    }

    public function handleDocumentUpdated(DocumentUpdated $event)
    {
        if ($event->document->is_published) {
            SyncDocumentToJdihn::dispatch($event->document, 'update');
        }
    }

    public function handleDocumentPublished(DocumentPublished $event)
    {
        SyncDocumentToJdihn::dispatch($event->document, 'publish');
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            DocumentCreated::class => 'handleDocumentCreated',
            DocumentUpdated::class => 'handleDocumentUpdated',
            DocumentPublished::class => 'handleDocumentPublished',
        ];
    }
}
```

---

## 5. Middleware & Security

### 5.1 JDIHN API Security Middleware

```php
// app/Http/Middleware/JdihnApiAccess.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class JdihnApiAccess
{
    public function handle(Request $request, Closure $next)
    {
        // API Key validation
        if (!$this->validateApiAccess($request)) {
            Log::warning('Unauthorized JDIHN API access attempt', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'path' => $request->path()
            ]);

            return response()->json([
                'error' => 'Unauthorized access',
                'message' => 'Valid API credentials required'
            ], 401);
        }

        // Rate limiting
        $key = 'jdihn_api:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 1000)) { // 1000 requests per minute
            return response()->json([
                'error' => 'Rate limit exceeded',
                'message' => 'Too many requests'
            ], 429);
        }

        RateLimiter::hit($key, 60);

        // Log API access
        Log::info('JDIHN API access', [
            'ip' => $request->ip(),
            'path' => $request->path(),
            'parameters' => $request->query()
        ]);

        return $next($request);
    }

    private function validateApiAccess(Request $request): bool
    {
        // Check for API key in header or query parameter
        $apiKey = $request->header('X-API-Key') ?? $request->query('api_key');

        if (!$apiKey) {
            return false;
        }

        // Validate against configured API keys
        $validKeys = config('jdihn.api.allowed_keys', []);
        
        return in_array($apiKey, $validKeys);
    }
}
```

---

## 6. Monitoring & Analytics

### 6.1 JDIHN Sync Monitoring

```php
// app/Console/Commands/JdihnSyncStatusCommand.php
namespace App\Console\Commands;

use App\Domain\Document\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class JdihnSyncStatusCommand extends Command
{
    protected $signature = 'jdihn:status {--detailed}';
    protected $description = 'Check JDIHN synchronization status';

    public function handle()
    {
        $this->info('JDIHN Synchronization Status Report');
        $this->line(str_repeat('=', 50));

        // Overall statistics
        $totalDocs = Document::where('is_published', true)->count();
        $syncedDocs = Document::where('jdihn_sync_status', 'synced')->count();
        $failedDocs = Document::where('jdihn_sync_status', 'failed')->count();
        $pendingDocs = Document::whereNull('jdihn_sync_status')
                              ->orWhere('jdihn_sync_status', 'pending')
                              ->count();

        $this->table(['Metric', 'Count', 'Percentage'], [
            ['Total Published Documents', $totalDocs, '100%'],
            ['Successfully Synced', $syncedDocs, round(($syncedDocs/$totalDocs)*100, 2) . '%'],
            ['Failed Sync', $failedDocs, round(($failedDocs/$totalDocs)*100, 2) . '%'],
            ['Pending Sync', $pendingDocs, round(($pendingDocs/$totalDocs)*100, 2) . '%'],
        ]);

        // Recent sync activity
        $this->newLine();
        $this->info('Recent Sync Activity (Last 24 hours):');

        $recentActivity = DB::table('jdihn_feeds')
            ->where('last_sync_at', '>=', now()->subDay())
            ->selectRaw('sync_status, COUNT(*) as count')
            ->groupBy('sync_status')
            ->get();

        foreach ($recentActivity as $activity) {
            $this->line("  {$activity->sync_status}: {$activity->count}");
        }

        // Failed documents details
        if ($this->option('detailed') && $failedDocs > 0) {
            $this->newLine();
            $this->error('Failed Sync Documents:');

            $failedDetails = Document::where('jdihn_sync_status', 'failed')
                                   ->select('id', 'title', 'jdihn_sync_error', 'updated_at')
                                   ->limit(10)
                                   ->get();

            foreach ($failedDetails as $doc) {
                $this->line("  ID: {$doc->id} - {$doc->title}");
                $this->line("    Error: {$doc->jdihn_sync_error}");
                $this->line("    Last Updated: {$doc->updated_at}");
                $this->newLine();
            }
        }

        return 0;
    }
}
```

---

## 7. Configuration Files

### 7.1 JDIHN Configuration

```php
// config/jdihn.php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | JDIHN API Configuration
    |--------------------------------------------------------------------------
    */
    'api' => [
        'base_url' => env('JDIHN_API_BASE_URL', 'https://jdihn.go.id/api'),
        'version' => env('JDIHN_API_VERSION', 'v1'),
        'timeout' => env('JDIHN_API_TIMEOUT', 30),
        'retry_attempts' => env('JDIHN_API_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('JDIHN_API_RETRY_DELAY', 1000), // milliseconds
        
        // API Authentication
        'allowed_keys' => explode(',', env('JDIHN_API_ALLOWED_KEYS', '')),
        'rate_limit' => env('JDIHN_API_RATE_LIMIT', 1000), // requests per minute
    ],

    /*
    |--------------------------------------------------------------------------
    | Synchronization Settings
    |--------------------------------------------------------------------------
    */
    'sync' => [
        'enabled' => env('JDIHN_SYNC_ENABLED', true),
        'auto_sync' => env('JDIHN_AUTO_SYNC', true),
        'batch_size' => env('JDIHN_SYNC_BATCH_SIZE', 100),
        'schedule' => env('JDIHN_SYNC_SCHEDULE', '0 2 * * *'), // Daily at 2 AM
        'max_retries' => env('JDIHN_SYNC_MAX_RETRIES', 3),
        
        // Sync triggers
        'sync_on_create' => env('JDIHN_SYNC_ON_CREATE', true),
        'sync_on_update' => env('JDIHN_SYNC_ON_UPDATE', true),
        'sync_on_publish' => env('JDIHN_SYNC_ON_PUBLISH', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Field Mappings
    |--------------------------------------------------------------------------
    */
    'field_mappings' => [
        'document' => [
            // Required fields
            'idData' => 'id',
            'judul' => 'title',
            'jenis' => 'regulation_type',
            'tipeData' => 'document_type.name',
            
            // Optional fields
            'teu' => 'teu',
            'noPeraturan' => 'document_number',
            'noPanggil' => 'call_number',
            'singkatanJenis' => 'regulation_abbreviation',
            'tahunPengundangan' => 'publication_year',
            'tanggalPenetapan' => 'enactment_date',
            'tanggalPengundangan' => 'promulgation_date',
            'tanggalDibacakan' => 'reading_date',
            'tempatTerbit' => 'place_published',
            'penerbit' => 'publisher',
            'deskripsiFisik' => 'physical_description',
            'sumber' => 'source',
            'isbn' => 'isbn',
            'status' => 'status',
            'bahasa' => 'language',
            'bidangHukum' => 'legal_field',
            'abstrak' => 'abstract',
            'urusan' => 'government_affairs',
            'inisiatif' => 'initiative',
            'pemrakarsa' => 'initiator',
        ],

        'abstrak' => [
            'idData' => 'id',
            'idDokumen' => 'id',
            'judul' => 'title',
            'singkatan' => 'regulation_abbreviation',
            'tahun' => 'publication_year',
            'subjek' => 'subjects.name',
            'isiAbstrak1' => 'abstract',
            'catatan1' => 'regulation_status_notes',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Validation Rules
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'required_fields' => [
            'peraturan' => ['idData', 'judul', 'jenis', 'noPeraturan'],
            'putusan' => ['idData', 'judul', 'jenis', 'lembagaPeradilan'],
            'monografi' => ['idData', 'judul', 'pengarang', 'penerbit'],
            'artikel' => ['idData', 'judul', 'pengarang'],
        ],

        'field_lengths' => [
            'judul' => 500,
            'noPeraturan' => 100,
            'jenis' => 200,
            'bahasa' => 5,
        ],

        'date_format' => 'Y-m-d',
        'datetime_format' => 'Y-m-d\TH:i:s\Z',
        'encoding' => 'UTF-8',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('JDIHN_CACHE_ENABLED', true),
        'ttl' => env('JDIHN_CACHE_TTL', 300), // 5 minutes
        'key_prefix' => 'jdihn_',
        'tags' => ['jdihn', 'api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Settings
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => env('JDIHN_LOGGING_ENABLED', true),
        'level' => env('JDIHN_LOG_LEVEL', 'info'),
        'channel' => env('JDIHN_LOG_CHANNEL', 'stack'),
        'log_requests' => env('JDIHN_LOG_REQUESTS', true),
        'log_responses' => env('JDIHN_LOG_RESPONSES', false),
    ],
];
```

---

## 8. Testing Strategy

### 8.1 Integration Tests

```php
// tests/Feature/JdihnApiIntegrationTest.php
namespace Tests\Feature;

use Tests\TestCase;
use App\Domain\Document\Models\Document;
use App\Domain\Document\Services\JdihnIntegrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JdihnApiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private JdihnIntegrationService $jdihnService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jdihnService = app(JdihnIntegrationService::class);
    }

    public function test_can_generate_document_feed()
    {
        // Create test documents
        Document::factory(5)->create(['is_published' => true]);

        $response = $this->get('/api/feed/document.json?limit=10');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'totalRecords',
                    'returnedRecords',
                    'offset',
                    'limit',
                    'lastUpdated',
                    'data' => [
                        '*' => [
                            'idData',
                            'judul',
                            'jenis',
                            'tipeData',
                            'dibuat',
                            'diubah'
                        ]
                    ]
                ]);
    }

    public function test_can_generate_abstrak_feed()
    {
        Document::factory(3)->create([
            'is_published' => true,
            'abstract' => 'Sample abstract content'
        ]);

        $response = $this->get('/api/feed/abstrak.json?limit=10');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'totalRecords',
                    'data' => [
                        '*' => [
                            'idData',
                            'idDokumen',
                            'judul',
                            'isiAbstrak1'
                        ]
                    ]
                ]);
    }

    public function test_api_requires_authentication()
    {
        $response = $this->get('/api/feed/document.json');

        $response->assertStatus(401)
                ->assertJson([
                    'error' => 'Unauthorized access'
                ]);
    }

    public function test_api_rate_limiting()
    {
        $this->withHeaders(['X-API-Key' => 'test-api-key']);

        // Make requests beyond rate limit
        for ($i = 0; $i < 1002; $i++) {
            $response = $this->get('/api/feed/document.json');
            
            if ($i >= 1000) {
                $response->assertStatus(429);
                break;
            }
        }
    }

    public function test_document_transformation_to_jdihn_format()
    {
        $document = Document::factory()->create([
            'is_published' => true,
            'title' => 'Test Document',
            'document_number' => 'TEST/001/2025'
        ]);

        $transformed = $this->jdihnService->transformDocumentToJdihnFormat($document);

        $this->assertArrayHasKey('idData', $transformed);
        $this->assertArrayHasKey('judul', $transformed);
        $this->assertArrayHasKey('noPeraturan', $transformed);
        $this->assertEquals($document->id, $transformed['idData']);
        $this->assertEquals($document->title, $transformed['judul']);
        $this->assertEquals($document->document_number, $transformed['noPeraturan']);
    }

    public function test_field_mapping_compliance()
    {
        $document = Document::factory()->create(['is_published' => true]);
        
        $jdihnData = $this->jdihnService->transformDocumentToJdihnFormat($document);

        // Check required fields
        $requiredFields = ['idData', 'judul', 'jenis', 'tipeData'];
        
        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $jdihnData, "Missing required field: {$field}");
        }

        // Check data types
        $this->assertIsInt($jdihnData['idData']);
        $this->assertIsString($jdihnData['judul']);
        
        // Check field lengths
        if (isset($jdihnData['judul'])) {
            $this->assertLessThanOrEqual(500, strlen($jdihnData['judul']));
        }
    }
}
```

---

## 9. Error Handling & Recovery

### 9.1 Sync Error Recovery

```php
// app/Console/Commands/RecoverFailedJdihnSyncCommand.php
namespace App\Console\Commands;

use App\Domain\Document\Models\Document;
use App\Jobs\SyncDocumentToJdihn;
use Illuminate\Console\Command;

class RecoverFailedJdihnSyncCommand extends Command
{
    protected $signature = 'jdihn:recover-failed {--retry-limit=3}';
    protected $description = 'Retry failed JDIHN synchronizations';

    public function handle()
    {
        $retryLimit = $this->option('retry-limit');
        
        $failedDocs = Document::where('jdihn_sync_status', 'failed')
                            ->where('jdihn_sync_retries', '<', $retryLimit)
                            ->get();

        $this->info("Found {$failedDocs->count()} failed syncs to retry");

        foreach ($failedDocs as $document) {
            $this->line("Retrying sync for document {$document->id}: {$document->title}");
            
            // Increment retry counter
            $document->increment('jdihn_sync_retries');
            $document->update(['jdihn_sync_status' => 'pending']);
            
            // Dispatch sync job
            SyncDocumentToJdihn::dispatch($document);
        }

        $this->info('Retry jobs queued successfully');
        return 0;
    }
}
```

---

**Document Version**: 1.0  
**Last Updated**: September 25, 2025  
**Status**: Draft  
**Next Review**: October 10, 2025  
**Prepared by**: Development Team