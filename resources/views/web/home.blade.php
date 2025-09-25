<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Sistem Informasi Dokumentasi Hukum</title>
    
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
                        <h1 class="text-xl font-semibold text-gray-900">
                            {{ config('app.name') }}
                        </h1>
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

        <!-- Search Section -->
        <section class="bg-blue-600 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold text-white mb-4">
                    Portal Dokumentasi Hukum Indonesia
                </h2>
                <p class="text-xl text-blue-100 mb-8">
                    Akses mudah ke peraturan perundang-undangan, putusan pengadilan, dan literatur hukum
                </p>
                
                <form action="{{ route('search') }}" method="GET" class="max-w-2xl mx-auto">
                    <div class="flex">
                        <input 
                            type="text" 
                            name="q" 
                            placeholder="Cari dokumen hukum..."
                            class="flex-1 px-4 py-3 rounded-l-lg border-0 focus:ring-2 focus:ring-blue-300"
                            value="{{ request('q') }}"
                        >
                        <button 
                            type="submit"
                            class="bg-yellow-500 hover:bg-yellow-400 text-black px-6 py-3 rounded-r-lg font-medium transition-colors"
                        >
                            Cari
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Statistics -->
        <section class="py-12 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                    <div>
                        <div class="text-3xl font-bold text-blue-600">{{ number_format($stats['total_documents']) }}</div>
                        <div class="text-gray-600">Total Dokumen</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-green-600">{{ number_format($stats['total_types']) }}</div>
                        <div class="text-gray-600">Jenis Dokumen</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-purple-600">{{ number_format($stats['total_subjects']) }}</div>
                        <div class="text-gray-600">Bidang Hukum</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-orange-600">{{ number_format($stats['this_month']) }}</div>
                        <div class="text-gray-600">Dokumen Bulan Ini</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Document Types -->
        <section class="py-12 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-8 text-center">
                    Jenis Dokumen Hukum
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($documentTypes as $type)
                    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                        <div class="text-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-900 mb-2">{{ $type->name }}</h4>
                            <p class="text-sm text-gray-600 mb-3">{{ $type->description }}</p>
                            <div class="text-sm font-medium text-blue-600">
                                {{ number_format($type->documents_count) }} dokumen
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Featured Documents -->
        @if($featuredDocuments->count() > 0)
        <section class="py-12 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-8">
                    Dokumen Unggulan
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($featuredDocuments as $document)
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center mb-3">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">
                                {{ $document->documentType->name }}
                            </span>
                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-medium ml-2">
                                Featured
                            </span>
                        </div>
                        
                        <h4 class="font-semibold text-gray-900 mb-2 line-clamp-2">
                            <a href="{{ route('document.show', [$document->documentType->slug, $document->slug]) }}" class="hover:text-blue-600">
                                {{ $document->title }}
                            </a>
                        </h4>
                        
                        @if($document->document_number)
                        <p class="text-sm text-gray-600 mb-2">{{ $document->document_number }}</p>
                        @endif
                        
                        <p class="text-sm text-gray-700 mb-3 line-clamp-3">{{ $document->excerpt }}</p>
                        
                        @if($document->authors->count() > 0)
                        <p class="text-xs text-gray-500 mb-3">
                            Penulis: {{ $document->authors->pluck('name')->join(', ') }}
                        </p>
                        @endif
                        
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>{{ $document->published_date?->format('d M Y') }}</span>
                            <span>{{ number_format($document->view_count) }} views</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        <!-- Latest Documents -->
        <section class="py-12 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-bold text-gray-900">
                        Dokumen Terbaru
                    </h3>
                    <a href="{{ route('search') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                        Lihat Semua →
                    </a>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    @foreach($latestDocuments as $document)
                    <div class="border-b border-gray-200 last:border-0 p-6 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-medium">
                                        {{ $document->documentType->name }}
                                    </span>
                                    <span class="text-gray-500 ml-3 text-sm">
                                        {{ $document->published_date?->format('d M Y') }}
                                    </span>
                                </div>
                                
                                <h4 class="font-semibold text-gray-900 mb-2">
                                    <a href="{{ route('document.show', [$document->documentType->slug, $document->slug]) }}" class="hover:text-blue-600">
                                        {{ $document->title }}
                                    </a>
                                </h4>
                                
                                @if($document->document_number)
                                <p class="text-sm text-gray-600 mb-2">{{ $document->document_number }}</p>
                                @endif
                                
                                <p class="text-sm text-gray-700 line-clamp-2">{{ $document->excerpt }}</p>
                                
                                @if($document->authors->count() > 0)
                                <p class="text-xs text-gray-500 mt-2">
                                    Penulis: {{ $document->authors->pluck('name')->join(', ') }}
                                </p>
                                @endif
                            </div>
                            
                            <div class="ml-4 text-right text-xs text-gray-500">
                                <div>{{ number_format($document->view_count) }} views</div>
                                <div>{{ number_format($document->download_count) }} downloads</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white py-8">
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
</body>
</html>