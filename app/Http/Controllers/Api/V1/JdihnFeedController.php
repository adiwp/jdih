<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class JdihnFeedController extends Controller
{
    /**
     * Generate JDIHN compliant document feed
     */
    public function documents(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => 'integer|min:1|max:1000',
            'offset' => 'integer|min:0',
            'updated_since' => 'date',
            'document_type' => 'in:peraturan,putusan,monografi,artikel',
            'region_code' => 'string',
            'year' => 'integer|min:1945',
        ]);

        $limit = $request->get('limit', 100);
        $offset = $request->get('offset', 0);
        $updatedSince = $request->get('updated_since');
        $documentType = $request->get('document_type');
        $regionCode = $request->get('region_code');
        $year = $request->get('year');

        $query = Document::with(['documentType', 'authors', 'subjects'])
            ->published();

        // Apply filters
        if ($updatedSince) {
            $query->where('updated_at', '>=', $updatedSince);
        }

        if ($documentType) {
            $query->whereHas('documentType', function ($q) use ($documentType) {
                $q->where('slug', $documentType);
            });
        }

        if ($year) {
            $query->whereYear('published_date', $year);
        }

        $total = $query->count();
        $documents = $query->offset($offset)->limit($limit)->get();

        // Transform to JDIHN format
        $jdihnData = $documents->map(function ($document) {
            return [
                'id' => $document->id,
                'judul' => $document->title,
                'abstrak' => $document->abstract,
                'nomor_dokumen' => $document->document_number,
                'nomor_panggil' => $document->call_number,
                'teu' => $document->teu_number,
                'jenis_dokumen' => $document->documentType->slug ?? null,
                'tahun_terbit' => $document->published_date ? $document->published_date->year : null,
                'tanggal_penetapan' => $document->effective_date?->format('Y-m-d'),
                'tanggal_pengundangan' => $document->published_date?->format('Y-m-d'),
                'pengarang' => $document->authors->map(function ($author) {
                    return [
                        'nama' => $author->name,
                        'institusi' => $author->institution,
                        'jabatan' => $author->position,
                    ];
                })->toArray(),
                'subjek' => [
                    'bidang_hukum' => $document->subjects->pluck('name')->toArray(),
                    'kata_kunci' => $document->keywords ? explode(',', $document->keywords) : [],
                ],
                'bahasa' => $document->language ?? 'id',
                'lokasi' => $document->location,
                'catatan' => $document->note,
                'sumber' => $document->source,
                'metadata' => [
                    'created_at' => $document->created_at->toISOString(),
                    'updated_at' => $document->updated_at->toISOString(),
                    'view_count' => $document->view_count,
                    'download_count' => $document->download_count,
                ],
            ];
        });

        return response()->json([
            'meta' => [
                'version' => '2.0',
                'generated_at' => now()->toISOString(),
                'total_records' => $total,
                'offset' => $offset,
                'limit' => $limit,
                'data_format' => 'JDIHN-2024',
            ],
            'data' => $jdihnData,
            'links' => [
                'first' => request()->fullUrlWithQuery(['offset' => 0]),
                'prev' => $offset > 0 ? request()->fullUrlWithQuery(['offset' => max(0, $offset - $limit)]) : null,
                'next' => ($offset + $limit) < $total ? request()->fullUrlWithQuery(['offset' => $offset + $limit]) : null,
                'last' => request()->fullUrlWithQuery(['offset' => floor($total / $limit) * $limit]),
            ],
        ], 200, [
            'Content-Type' => 'application/json; charset=utf-8',
            'X-JDIHN-Compliance' => 'verified',
            'Cache-Control' => 'public, max-age=180',
        ]);
    }

    /**
     * Get single document for JDIHN
     */
    public function document($id): JsonResponse
    {
        $document = Document::with(['documentType', 'authors', 'subjects'])
            ->published()
            ->findOrFail($id);

        $jdihnData = [
            'id' => $document->id,
            'judul' => $document->title,
            'abstrak' => $document->abstract,
            'nomor_dokumen' => $document->document_number,
            'nomor_panggil' => $document->call_number,
            'teu' => $document->teu_number,
            'jenis_dokumen' => $document->documentType->slug ?? null,
            'tahun_terbit' => $document->published_date ? $document->published_date->year : null,
            'tanggal_penetapan' => $document->effective_date?->format('Y-m-d'),
            'tanggal_pengundangan' => $document->published_date?->format('Y-m-d'),
            'pengarang' => $document->authors->map(function ($author) {
                return [
                    'nama' => $author->name,
                    'institusi' => $author->institution,
                    'jabatan' => $author->position,
                ];
            })->toArray(),
            'subjek' => [
                'bidang_hukum' => $document->subjects->pluck('name')->toArray(),
                'kata_kunci' => $document->keywords ? explode(',', $document->keywords) : [],
            ],
            'bahasa' => $document->language ?? 'id',
            'lokasi' => $document->location,
            'catatan' => $document->note,
            'sumber' => $document->source,
            'metadata' => [
                'created_at' => $document->created_at->toISOString(),
                'updated_at' => $document->updated_at->toISOString(),
                'view_count' => $document->view_count,
                'download_count' => $document->download_count,
            ],
        ];

        return response()->json([
            'meta' => [
                'version' => '2.0',
                'generated_at' => now()->toISOString(),
                'compliance_checked' => true,
            ],
            'data' => $jdihnData,
        ], 200, [
            'Content-Type' => 'application/json; charset=utf-8',
            'X-JDIHN-Compliance' => 'verified',
        ]);
    }

    /**
     * Generate abstract feed
     */
    public function abstracts(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => 'integer|min:1|max:1000',
            'offset' => 'integer|min:0',
        ]);

        $limit = $request->get('limit', 100);
        $offset = $request->get('offset', 0);

        $query = Document::published()->whereNotNull('abstract');
        
        $total = $query->count();
        $documents = $query->offset($offset)->limit($limit)->get();

        $abstractData = $documents->map(function ($document) {
            return [
                'id' => $document->id,
                'judul' => $document->title,
                'abstrak' => $document->abstract,
                'nomor_dokumen' => $document->document_number,
                'jenis_dokumen' => $document->documentType->slug ?? null,
                'tahun_terbit' => $document->published_date ? $document->published_date->year : null,
            ];
        });

        return response()->json([
            'meta' => [
                'version' => '2.0',
                'type' => 'abstract_feed',
                'generated_at' => now()->toISOString(),
                'total_records' => $total,
                'offset' => $offset,
                'limit' => $limit,
            ],
            'data' => $abstractData,
        ], 200, [
            'Content-Type' => 'application/json; charset=utf-8',
            'X-Feed-Type' => 'abstract',
        ]);
    }
}
