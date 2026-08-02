@extends('admin.layouts.template')

@section('page_title')
Riset Kata Kunci - Admin Dashboard
@endsection

@section('content')
<!-- Include Tailwind CSS & Alpine.js CDNs specifically for this view -->
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div class="container mx-auto px-4 py-8 max-w-7xl" x-data="keywordResearchApp()">
    <!-- Header Section -->
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Keyword Research Tool</h1>
        <p class="mt-2 text-sm text-gray-500">Discover related keywords, monthly search volume, CPC, and competition level from Google Autocomplete.</p>
    </div>

    <!-- Search Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
        <form @submit.prevent="performSearch" class="flex flex-col md:flex-row gap-4">
            <div class="flex-grow relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input 
                    type="text" 
                    x-model="searchQuery" 
                    placeholder="Masukkan kata kunci dasar (contoh: roster beton)..." 
                    class="block w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm text-gray-900 placeholder-gray-400 bg-gray-50/50"
                    required
                />
            </div>
            <button 
                type="submit" 
                :disabled="loading" 
                class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-sm font-semibold rounded-xl text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <!-- Loading Spinner -->
                <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="loading ? 'Menganalisis...' : 'Cari Kata Kunci'"></span>
            </button>
        </form>
        
        <!-- Error Alerts -->
        <template x-if="errorMessage">
            <div class="mt-4 p-4 bg-red-50 rounded-xl border border-red-100 flex items-start gap-3">
                <svg class="h-5 w-5 text-red-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="text-sm text-red-700 font-medium" x-text="errorMessage"></div>
            </div>
        </template>
    </div>

    <!-- Data Display Section -->
    <template x-if="keywordsList.length > 0">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Table Header Control Buttons -->
            <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gray-50/50">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Hasil Riset</h2>
                    <p class="text-xs text-gray-500 mt-1" x-text="'Ditemukan ' + keywordsList.length + ' kata kunci terkait. Sumber data: ' + (dataSource === 'cache' ? 'Cache (14 Hari Terakhir)' : 'API Live Autocomplete')"></p>
                </div>
                <button 
                    @click="exportCSV" 
                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-200 text-xs font-semibold rounded-lg text-gray-700 bg-white hover:bg-gray-50 shadow-sm transition-colors"
                >
                    <svg class="h-4 w-4 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Unduh CSV
                </button>
            </div>

            <!-- Data Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-bold uppercase tracking-wider text-gray-400 bg-gray-50/30">
                            <th class="px-6 py-4 font-semibold">Kata Kunci</th>
                            <th class="px-6 py-4 font-semibold cursor-pointer select-none hover:text-gray-900 transition-colors" @click="toggleSort">
                                <div class="flex items-center gap-2">
                                    Volume Pencarian Bulanan
                                    <!-- Sort Icon -->
                                    <svg class="h-4 w-4 text-gray-400" :class="{'rotate-180 text-blue-500': sortOrder === 'asc', 'text-blue-500': sortOrder === 'desc'}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </th>
                            <th class="px-6 py-4 font-semibold">CPC (USD)</th>
                            <th class="px-6 py-4 font-semibold">Kompetisi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <template x-for="item in sortedKeywords" :key="item.id">
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900" x-text="item.keyword"></td>
                                <td class="px-6 py-4 text-sm text-gray-500" x-text="formatNumber(item.search_volume)"></td>
                                <td class="px-6 py-4 text-sm text-gray-500" x-text="'$' + parseFloat(item.cpc).toFixed(2)"></td>
                                <td class="px-6 py-4 text-sm">
                                    <span 
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wide border"
                                        :class="{
                                            'bg-green-50 text-green-700 border-green-200': item.competition === 'low',
                                            'bg-yellow-50 text-yellow-700 border-yellow-200': item.competition === 'medium',
                                            'bg-red-50 text-red-700 border-red-200': item.competition === 'high'
                                        }"
                                        x-text="item.competition"
                                    ></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </template>
</div>

<script>
    function keywordResearchApp() {
        return {
            searchQuery: '',
            loading: false,
            errorMessage: '',
            dataSource: '',
            keywordsList: [],
            sortOrder: 'desc', // 'asc' or 'desc'

            async performSearch() {
                this.loading = true;
                this.errorMessage = '';
                this.keywordsList = [];

                try {
                    const response = await fetch("{{ route('admin.keywords.search') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ query: this.searchQuery })
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        this.keywordsList = result.data;
                        this.dataSource = result.source;
                    } else {
                        this.errorMessage = result.message || 'Terjadi kesalahan saat memproses data.';
                    }
                } catch (error) {
                    this.errorMessage = 'Gagal menghubungi server. Periksa koneksi internet Anda.';
                } finally {
                    this.loading = false;
                }
            },

            get sortedKeywords() {
                return [...this.keywordsList].sort((a, b) => {
                    const volA = parseInt(a.search_volume) || 0;
                    const volB = parseInt(b.search_volume) || 0;
                    return this.sortOrder === 'asc' ? volA - volB : volB - volA;
                });
            },

            toggleSort() {
                this.sortOrder = this.sortOrder === 'desc' ? 'asc' : 'desc';
            },

            formatNumber(num) {
                return new Intl.NumberFormat('id-ID').format(num);
            },

            exportCSV() {
                let csv = 'Keyword,Search Volume,CPC,Competition\n';
                this.sortedKeywords.forEach(item => {
                    csv += `"${item.keyword}",${item.search_volume},${item.cpc},"${item.competition}"\n`;
                });

                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement("a");
                link.setAttribute("href", url);
                link.setAttribute("download", `keyword_research_${this.searchQuery.replace(/\s+/g, '_')}.csv`);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        };
    }
</script>
@endsection
