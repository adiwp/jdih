<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $document->title }} - {{ config('app.name') }}</title>
    
    <!-- Meta tags for SEO -->
    <meta name="description" content="{{ $document->excerpt }}">
    <meta name="keywords" content="{{ $document->keywords }}">
    <meta name="author" content="{{ $document->authors->pluck('name')->join(', ') }}">
    
    <!-- Open Graph -->
    <meta property="og:title" content="{{ $document->title }}">
    <meta property="og:description" content="{{ $document->excerpt }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url()->current() }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <a href="{{ route('home') }}" class="text-xl font-semibold text-gray-900">
                            {{ config('app.name') }}
                        </a>
                        <span class="ml-2 text-sm text-gray-500">
                            Indonesian Legal Documentation Information System
                        </span>
                    </div>
                    
                    <nav class="hidden md:flex space-x-8">
                        <a href="{{ route('home') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">
                            Beranda
                        </a>
                        <a href="{{ route('search') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">
                            Pencarian
                        </a>
                        <a href="/admin" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Admin
                        </a>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Breadcrumb -->
        <nav class="bg-white border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
                <div class="flex items-center space-x-2 text-sm">
                    <a href="{{ route('home') }}" class="text-blue-600 hover:text-blue-800">Beranda</a>
                    <span class="text-gray-400">→</span>
                    <a href="{{ route('search', ['document_type' => $document->documentType->id]) }}" class="text-blue-600 hover:text-blue-800">
                        {{ $document->documentType->name }}
                    </a>
                    <span class="text-gray-400">→</span>
                    <span class="text-gray-600 truncate">{{ $document->title }}</span>
                </div>
            </div>
        </nav>

        <!-- Document Header -->
        <section class="bg-white py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-3 lg:gap-8">
                    <!-- Main Content -->
                    <div class="lg:col-span-2">
                        <!-- Document Type & Status -->
                        <div class="flex flex-wrap items-center mb-4 space-x-2">
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                                {{ $document->documentType->name }}
                            </span>
                            
                            @if($document->documentStatus)
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                                {{ $document->documentStatus->name }}
                            </span>
                            @endif
                            
                            @if($document->is_featured)
                            <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">
                                Featured
                            </span>
                            @endif
                            
                            @if($document->jdihn_id)
                            <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-medium">
                                JDIHN Compliant
                            </span>
                            @endif
                        </div>

                        <!-- Title -->
                        <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $document->title }}</h1>
                        
                        <!-- Document Number -->
                        @if($document->document_number)
                        <div class="bg-gray-50 border-l-4 border-blue-500 p-4 mb-6">
                            <div class="flex">
                                <div>
                                    <p class="text-sm text-gray-600">Nomor Dokumen</p>
                                    <p class="font-mono font-semibold text-gray-900">{{ $document->document_number }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Document Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 text-sm">
                            @if($document->published_date)
                            <div>
                                <span class="font-medium text-gray-900">Tanggal Terbit:</span>
                                <span class="text-gray-600">{{ $document->published_date->format('d F Y') }}</span>
                            </div>
                            @endif
                            
                            @if($document->effective_date)
                            <div>
                                <span class="font-medium text-gray-900">Tanggal Berlaku:</span>
                                <span class="text-gray-600">{{ $document->effective_date->format('d F Y') }}</span>
                            </div>
                            @endif
                            
                            <div>
                                <span class="font-medium text-gray-900">Dilihat:</span>
                                <span class="text-gray-600">{{ number_format($document->view_count) }} kali</span>
                            </div>
                            
                            <div>
                                <span class="font-medium text-gray-900">Diunduh:</span>
                                <span class="text-gray-600">{{ number_format($document->download_count) }} kali</span>
                            </div>
                        </div>

                        <!-- Authors -->
                        @if($document->authors->count() > 0)
                        <div class="mb-6">
                            <h3 class="font-medium text-gray-900 mb-2">Penulis/Lembaga:</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($document->authors as $author)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-800">
                                    {{ $author->name }}
                                    @if($author->title)
                                        <span class="ml-1 text-xs text-gray-600">({{ $author->title }})</span>
                                    @endif
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Subjects -->
                        @if($document->subjects->count() > 0)
                        <div class="mb-6">
                            <h3 class="font-medium text-gray-900 mb-2">Bidang Hukum:</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($document->subjects as $subject)
                                <a href="{{ route('search', ['subject' => $subject->id]) }}" 
                                   class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 text-green-800 hover:bg-green-200 transition-colors">
                                    {{ $subject->name }}
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Keywords -->
                        @if($document->keywords)
                        <div class="mb-6">
                            <h3 class="font-medium text-gray-900 mb-2">Kata Kunci:</h3>
                            <div class="flex flex-wrap gap-1">
                                @foreach(explode(',', $document->keywords) as $keyword)
                                <span class="inline-block px-2 py-1 text-xs bg-blue-50 text-blue-700 rounded">
                                    {{ trim($keyword) }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Abstract/Excerpt -->
                        @if($document->excerpt)
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Abstrak</h3>
                            <div class="prose prose-gray max-w-none">
                                <p class="text-gray-700 leading-relaxed">{{ $document->excerpt }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Full Content -->
                        @if($document->content)
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Isi Dokumen</h3>
                            <div class="prose prose-gray max-w-none bg-white border rounded-lg p-6">
                                {!! nl2br(e($document->content)) !!}
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Sidebar -->
                    <div class="mt-8 lg:mt-0">
                        <!-- Actions -->
                        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                            <h3 class="font-medium text-gray-900 mb-4">Aksi</h3>
                            <div class="space-y-3">
                                @if($document->file_path)
                                <a href="{{ route('document.download', $document->id) }}" 
                                   class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Download PDF
                                </a>
                                @endif
                                
                                <button onclick="shareDocument()" 
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                                    </svg>
                                    Bagikan
                                </button>
                                
                                <button onclick="printDocument()" 
                                        class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    Cetak
                                </button>
                            </div>
                        </div>

                        <!-- Document Details -->
                        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                            <h3 class="font-medium text-gray-900 mb-4">Detail Dokumen</h3>
                            <dl class="space-y-3 text-sm">
                                <div>
                                    <dt class="font-medium text-gray-900">ID Dokumen</dt>
                                    <dd class="text-gray-600">{{ $document->id }}</dd>
                                </div>
                                
                                @if($document->jdihn_id)
                                <div>
                                    <dt class="font-medium text-gray-900">ID JDIHN</dt>
                                    <dd class="text-gray-600 font-mono">{{ $document->jdihn_id }}</dd>
                                </div>
                                @endif
                                
                                <div>
                                    <dt class="font-medium text-gray-900">Tanggal Input</dt>
                                    <dd class="text-gray-600">{{ $document->created_at->format('d M Y H:i') }}</dd>
                                </div>
                                
                                <div>
                                    <dt class="font-medium text-gray-900">Terakhir Update</dt>
                                    <dd class="text-gray-600">{{ $document->updated_at->format('d M Y H:i') }}</dd>
                                </div>
                                
                                @if($document->file_size)
                                <div>
                                    <dt class="font-medium text-gray-900">Ukuran File</dt>
                                    <dd class="text-gray-600">{{ number_format($document->file_size / 1024 / 1024, 2) }} MB</dd>
                                </div>
                                @endif
                                
                                @if($document->language)
                                <div>
                                    <dt class="font-medium text-gray-900">Bahasa</dt>
                                    <dd class="text-gray-600">{{ ucfirst($document->language) }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>

                        <!-- JDIHN Compliance -->
                        @if($document->jdihn_id)
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-6 mb-6">
                            <div class="flex items-center mb-3">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="font-medium text-purple-900">JDIHN Compliant</h3>
                            </div>
                            <p class="text-sm text-purple-700">
                                Dokumen ini telah memenuhi standar metadata SATU DATA HUKUM INDONESIA
                            </p>
                            <div class="mt-3">
                                <a href="/api/v1/jdihn/documents/{{ $document->jdihn_id }}" 
                                   class="text-sm text-purple-600 hover:text-purple-800 font-medium">
                                    Lihat API JDIHN →
                                </a>
                            </div>
                        </div>
                        @endif

                        <!-- Related Documents -->
                        @if($relatedDocuments->count() > 0)
                        <div class="bg-white rounded-lg shadow-sm border p-6">
                            <h3 class="font-medium text-gray-900 mb-4">Dokumen Terkait</h3>
                            <div class="space-y-3">
                                @foreach($relatedDocuments as $related)
                                <div class="border-b border-gray-100 last:border-0 pb-3 last:pb-0">
                                    <h4 class="text-sm font-medium">
                                        <a href="{{ route('document.show', [$related->documentType->slug, $related->slug]) }}" 
                                           class="text-blue-600 hover:text-blue-800 line-clamp-2">
                                            {{ $related->title }}
                                        </a>
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $related->documentType->name }} • {{ $related->published_date?->format('Y') }}
                                    </p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white py-8 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h4 class="text-lg font-semibold mb-2">{{ config('app.name') }}</h4>
                    <p class="text-gray-400 mb-4">
                        Indonesian Legal Documentation Information System
                    </p>
                    <p class="text-sm text-gray-500">
                        Sistem dokumentasi hukum terintegrasi dengan standar SATU DATA HUKUM INDONESIA (JDIHN)
                    </p>
                    
                    <div class="mt-6 pt-6 border-t border-gray-700">
                        <div class="flex items-center justify-center space-x-6 text-sm">
                            <a href="/api/v1/jdihn/documents" class="text-gray-400 hover:text-white">
                                API JDIHN
                            </a>
                            <a href="/admin" class="text-gray-400 hover:text-white">
                                Admin Panel
                            </a>
                            <span class="text-gray-500">
                                © {{ date('Y') }} ILDIS
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script>
        function shareDocument() {
            if (navigator.share) {
                navigator.share({
                    title: '{{ $document->title }}',
                    text: '{{ $document->excerpt }}',
                    url: window.location.href
                });
            } else {
                // Fallback: copy URL to clipboard
                navigator.clipboard.writeText(window.location.href).then(function() {
                    alert('URL copied to clipboard');
                });
            }
        }

        function printDocument() {
            window.print();
        }

        // Track page view
        fetch('/api/v1/documents/{{ $document->id }}/view', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
    </script>
</body>
</html>