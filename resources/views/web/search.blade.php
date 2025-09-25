<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pencarian - {{ config('app.name') }}</title>
    
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
                        <a href="{{ route('search') }}" class="text-blue-600 px-3 py-2 text-sm font-medium">
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
        <section class="bg-blue-600 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-white mb-4 text-center">
                    Pencarian Dokumen Hukum
                </h2>
                
                <form action="{{ route('search') }}" method="GET" class="max-w-4xl mx-auto">
                    <div class="bg-white rounded-lg p-6 shadow-lg">
                        <!-- Main Search -->
                        <div class="flex mb-4">
                            <input 
                                type="text" 
                                name="q" 
                                placeholder="Masukkan kata kunci pencarian..."
                                class="flex-1 px-4 py-3 rounded-l-lg border border-gray-300 focus:ring-2 focus:ring-blue-300 focus:border-transparent"
                                value="{{ request('q') }}"
                            >
                            <button 
                                type="submit"
                                class="bg-yellow-500 hover:bg-yellow-400 text-black px-6 py-3 rounded-r-lg font-medium transition-colors"
                            >
                                Cari
                            </button>
                        </div>
                        
                        <!-- Advanced Filters -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <select name="document_type" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-300">
                                <option value="">Semua Jenis Dokumen</option>
                                @foreach($documentTypes as $type)
                                <option value="{{ $type->id }}" {{ request('document_type') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                                @endforeach
                            </select>
                            
                            <select name="subject" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-300">
                                <option value="">Semua Bidang Hukum</option>
                                @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                                @endforeach
                            </select>
                            
                            <select name="year" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-300">
                                <option value="">Semua Tahun</option>
                                @foreach($years as $year)
                                <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Sort Options -->
                        <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200">
                            <div class="flex items-center space-x-4">
                                <label class="text-sm text-gray-600">Urutkan:</label>
                                <select name="sort" class="px-3 py-1 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-300">
                                    <option value="relevance" {{ request('sort', 'relevance') == 'relevance' ? 'selected' : '' }}>Relevansi</option>
                                    <option value="date_desc" {{ request('sort') == 'date_desc' ? 'selected' : '' }}>Tanggal Terbaru</option>
                                    <option value="date_asc" {{ request('sort') == 'date_asc' ? 'selected' : '' }}>Tanggal Terlama</option>
                                    <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>Judul A-Z</option>
                                    <option value="views" {{ request('sort') == 'views' ? 'selected' : '' }}>Paling Dilihat</option>
                                </select>
                            </div>
                            
                            @if(request()->hasAny(['q', 'document_type', 'subject', 'year']))
                            <a href="{{ route('search') }}" class="text-sm text-blue-600 hover:text-blue-800">
                                Reset Filter
                            </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <!-- Search Results -->
        <section class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @if(request('q') || request()->hasAny(['document_type', 'subject', 'year']))
                    <!-- Search Info -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    @if(request('q'))
                                        Hasil pencarian untuk: "{{ request('q') }}"
                                    @else
                                        Hasil pencarian
                                    @endif
                                </h3>
                                <p class="text-sm text-gray-600">
                                    Menampilkan {{ number_format($documents->firstItem()) }}-{{ number_format($documents->lastItem()) }} 
                                    dari {{ number_format($documents->total()) }} dokumen
                                    @if($documents->total() > 0)
                                        ({{ number_format($documents->total() / $documents->perPage(), 2) }} detik)
                                    @endif
                                </p>
                            </div>
                            
                            <div class="text-sm text-gray-500">
                                {{ $documents->perPage() }} per halaman
                            </div>
                        </div>
                    </div>

                    <!-- Active Filters -->
                    @if(request()->hasAny(['document_type', 'subject', 'year']))
                    <div class="mb-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm text-gray-600">Filter aktif:</span>
                            
                            @if(request('document_type'))
                                @php $selectedType = $documentTypes->find(request('document_type')) @endphp
                                @if($selectedType)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                                    Jenis: {{ $selectedType->name }}
                                    <a href="{{ request()->fullUrlWithQuery(['document_type' => null]) }}" class="ml-2 text-blue-600 hover:text-blue-800">×</a>
                                </span>
                                @endif
                            @endif
                            
                            @if(request('subject'))
                                @php $selectedSubject = $subjects->find(request('subject')) @endphp
                                @if($selectedSubject)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 text-green-800">
                                    Bidang: {{ $selectedSubject->name }}
                                    <a href="{{ request()->fullUrlWithQuery(['subject' => null]) }}" class="ml-2 text-green-600 hover:text-green-800">×</a>
                                </span>
                                @endif
                            @endif
                            
                            @if(request('year'))
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-purple-100 text-purple-800">
                                Tahun: {{ request('year') }}
                                <a href="{{ request()->fullUrlWithQuery(['year' => null]) }}" class="ml-2 text-purple-600 hover:text-purple-800">×</a>
                            </span>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Results -->
                    @if($documents->count() > 0)
                        <div class="space-y-6">
                            @foreach($documents as $document)
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-3">
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">
                                                {{ $document->documentType->name }}
                                            </span>
                                            
                                            @if($document->subjects->count() > 0)
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium ml-2">
                                                {{ $document->subjects->first()->name }}
                                            </span>
                                            @endif
                                            
                                            @if($document->is_featured)
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-medium ml-2">
                                                Featured
                                            </span>
                                            @endif
                                        </div>
                                        
                                        <h4 class="text-lg font-semibold text-gray-900 mb-2">
                                            <a href="{{ route('document.show', [$document->documentType->slug, $document->slug]) }}" class="hover:text-blue-600">
                                                {{ $document->title }}
                                            </a>
                                        </h4>
                                        
                                        @if($document->document_number)
                                        <p class="text-sm text-gray-600 mb-2 font-mono">{{ $document->document_number }}</p>
                                        @endif
                                        
                                        <p class="text-gray-700 mb-3 line-clamp-3">{{ $document->excerpt }}</p>
                                        
                                        <div class="flex flex-wrap items-center text-xs text-gray-500 space-x-4">
                                            @if($document->authors->count() > 0)
                                            <span>
                                                <strong>Penulis:</strong> {{ $document->authors->pluck('name')->take(3)->join(', ') }}
                                                @if($document->authors->count() > 3)
                                                    dan {{ $document->authors->count() - 3 }} lainnya
                                                @endif
                                            </span>
                                            @endif
                                            
                                            <span><strong>Tanggal:</strong> {{ $document->published_date?->format('d M Y') }}</span>
                                            <span><strong>Views:</strong> {{ number_format($document->view_count) }}</span>
                                            <span><strong>Downloads:</strong> {{ number_format($document->download_count) }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="ml-6 flex flex-col items-end">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <a href="{{ route('document.show', [$document->documentType->slug, $document->slug]) }}" 
                                               class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                                Lihat
                                            </a>
                                            @if($document->file_path)
                                            <a href="{{ route('document.download', $document->id) }}" 
                                               class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                                                Download
                                            </a>
                                            @endif
                                        </div>
                                        
                                        <div class="text-xs text-gray-500 text-right">
                                            <div>ID: {{ $document->id }}</div>
                                            @if($document->jdihn_id)
                                            <div>JDIHN: {{ $document->jdihn_id }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-8">
                            {{ $documents->appends(request()->query())->links() }}
                        </div>
                    @else
                        <!-- No Results -->
                        <div class="text-center py-12">
                            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada dokumen ditemukan</h3>
                            <p class="text-gray-600 mb-4">
                                Coba ubah kata kunci pencarian atau filter yang digunakan
                            </p>
                            <a href="{{ route('search') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                Reset pencarian
                            </a>
                        </div>
                    @endif
                @else
                    <!-- Browse by Categories -->
                    <div class="space-y-8">
                        <!-- Document Types -->
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-6">Jelajahi Berdasarkan Jenis Dokumen</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($documentTypes as $type)
                                <a href="{{ route('search', ['document_type' => $type->id]) }}" 
                                   class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md hover:border-blue-300 transition-all">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $type->name }}</h4>
                                            <p class="text-sm text-gray-600 mt-1">{{ $type->description }}</p>
                                        </div>
                                        <div class="text-sm font-medium text-blue-600 ml-4">
                                            {{ number_format($type->documents_count) }}
                                        </div>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Legal Subjects -->
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-6">Jelajahi Berdasarkan Bidang Hukum</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                @foreach($subjects->take(12) as $subject)
                                <a href="{{ route('search', ['subject' => $subject->id]) }}" 
                                   class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md hover:border-green-300 transition-all text-center">
                                    <h4 class="font-semibold text-gray-900 mb-1">{{ $subject->name }}</h4>
                                    <div class="text-sm text-green-600 font-medium">
                                        {{ number_format($subject->documents_count) }} dokumen
                                    </div>
                                </a>
                                @endforeach
                            </div>
                            
                            @if($subjects->count() > 12)
                            <div class="text-center mt-6">
                                <button class="text-blue-600 hover:text-blue-800 font-medium" onclick="toggleMoreSubjects()">
                                    Lihat Semua Bidang Hukum ({{ $subjects->count() }})
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                @endif
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
        function toggleMoreSubjects() {
            // Implementation for showing more subjects
            alert('Feature akan segera tersedia');
        }
    </script>
</body>
</html>