@extends('admin.layouts.template')

@section('page_title')
    Riset Kata Kunci - Admin Dashboard
@endsection

@section('content')
    <!-- Include Tailwind CSS & Alpine.js CDNs specifically for this view -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Custom CSS for clean UI tweaks -->
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    <div class="container-xxl flex-grow-1 container-p-y max-w-7xl" x-data="keywordResearchApp()">
        <!-- Header Section -->
        <div
            class="mb-8 bg-gradient-to-r from-blue-50 via-indigo-50/30 to-transparent p-6 rounded-2xl border border-blue-100/50">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Keyword Research Tool
            </h1>
            <p class="mt-2 text-sm text-slate-500 max-w-2xl leading-relaxed">
                Discover rich auto-suggestions, search volume, CPC, and competition level directly extracted from Google
                API. Uncover valuable SEO opportunities.
            </p>
        </div>

        <!-- Search and Config Section -->
        <div
            class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-8 hover:shadow-md transition-shadow duration-300">
            <form @submit.prevent="performSearch" class="flex flex-col gap-4">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-grow relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" x-model="searchQuery" @input.debounce.300ms="fetchSuggestions"
                            @keydown.escape="showDropdown = false" placeholder="Type a base keyword... (e.g. roster beton)"
                            class="block w-full pl-11 pr-4 py-3.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm text-slate-700 bg-slate-50/50 hover:bg-slate-50 transition-colors"
                            required />

                        <!-- Autocomplete Dropdown -->
                        <div x-show="showDropdown && autocompleteSuggestions.length > 0" @click.away="showDropdown = false"
                            class="absolute z-50 w-full mt-2 bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden max-h-60 overflow-y-auto custom-scrollbar">
                            <ul class="divide-y divide-slate-150">
                                <template x-for="item in autocompleteSuggestions" :key="item.keyword">
                                    <li @click="selectSuggestion(item.keyword)"
                                        class="px-4 py-3 hover:bg-blue-50/80 cursor-pointer flex justify-between items-center transition-colors">
                                        <span class="text-sm font-medium text-slate-800" x-text="item.keyword"></span>
                                        <span
                                            class="text-xs text-blue-600 bg-blue-50/60 px-2.5 py-1 rounded-full font-semibold flex items-center gap-1">
                                            <svg class="h-3.5 w-3.5 text-blue-500" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z" />
                                            </svg>
                                            <span x-text="formatNumber(item.search_volume)"></span>
                                        </span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                    <button type="submit" :disabled="loading"
                        class="inline-flex items-center justify-center px-6 py-3.5 border border-transparent text-sm font-semibold rounded-xl text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-850 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-md shadow-blue-200 min-w-[160px]">
                        <!-- Loading Spinner -->
                        <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span x-text="loading ? 'Analyzing...' : 'Search Keywords'"></span>
                    </button>
                </div>

                <div class="flex items-center gap-3 mt-1">
                    <input type="checkbox" id="fetchMetrics" x-model="fetchMetrics"
                        class="w-4.5 h-4.5 text-blue-600 border-slate-300 rounded focus:ring-blue-500 cursor-pointer">
                    <label for="fetchMetrics" class="text-sm font-medium text-slate-800 cursor-pointer select-none">
                        Fetch exact Search Volume & CPC <span
                            class="text-xs text-indigo-500 bg-indigo-50/80 px-2 py-0.5 rounded ml-1 font-semibold">Uses
                            DataForSeo Credits</span>
                    </label>
                </div>
            </form>

            <!-- Error Alerts -->
            <template x-if="errorMessage">
                <div class="mt-4 p-4 bg-red-50 rounded-xl border border-red-100 flex items-start gap-3">
                    <svg class="h-5 w-5 text-red-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="text-sm text-red-700 font-medium" x-text="errorMessage"></div>
                </div>
            </template>
        </div>

        <!-- Data Display Section -->

        <div class="mt-6">
            <template x-if="results.length >= 0">
                <div
                    class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
                    <!-- Table Header Control Buttons -->
                    <div
                        class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50/50">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                Research Findings
                                <span class="px-2.5 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full"
                                    x-text="results.length"></span>
                            </h2>
                            <p class="text-xs text-slate-500 mt-1"
                                x-text="'Source: ' + (dataSource === 'cache' ? 'Cached Database Records' : 'Live Google Autocomplete API Request')">
                            </p>
                        </div>
                        <button @click="exportCSV"
                            class="inline-flex items-center justify-center px-4 py-2 border border-slate-200 text-xs font-semibold rounded-lg text-slate-700 bg-white hover:bg-slate-50 shadow-sm transition-colors cursor-pointer">
                            <svg class="h-4 w-4 text-slate-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export CSV
                        </button>
                    </div>

                    <div class="flex flex-col gap-4">
                        <!-- Data Table -->
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left border-collapse min-w-[700px]">
                                <thead>
                                    <tr
                                        class="border-b border-slate-100 text-xs font-bold uppercase tracking-wider text-slate-400 bg-slate-50/30">
                                        <th class="px-6 py-4 font-semibold text-slate-600">Keyword</th>
                                        <th class="px-6 py-4 font-semibold cursor-pointer select-none hover:text-slate-900 transition-colors text-slate-600 group"
                                            @click="toggleSort">
                                            <div class="flex items-center gap-2">
                                                Monthly Search Volume
                                                <!-- Sort Icon -->
                                                <svg class="h-4 w-4 transition-transform duration-250 text-blue-500"
                                                    :class="{'rotate-180': sortOrder === 'asc'}" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                        d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </th>
                                        <th class="px-6 py-4 font-semibold text-slate-600">CPC (USD)</th>
                                        <th class="px-6 py-4 font-semibold text-slate-600">Competition</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="item in paginatedResults" :key="item.id">
                                        <tr class="hover:bg-slate-50/50 transition-colors group">
                                            <td class="px-6 py-4 text-sm font-semibold text-slate-900 group-hover:text-blue-600 transition-colors"
                                                x-text="item.keyword"></td>
                                            <td class="px-6 py-4 text-sm font-medium text-slate-600"
                                                x-text="formatNumber(item.search_volume)"></td>
                                            <td class="px-6 py-4 text-sm font-medium text-emerald-600"
                                                x-text="item.cpc !== null && item.cpc !== undefined ? '$' + parseFloat(item.cpc).toFixed(2) : '-'">
                                            </td>
                                            <td class="px-6 py-4 text-sm font-semibold">
                                                <template
                                                    x-if="item.competition !== null && item.competition !== undefined">
                                                    <span
                                                        class="px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wider inline-flex items-center"
                                                        :class="{
                                                                                'bg-red-50 text-red-700 border border-red-150': item.competition.toLowerCase() === 'high',
                                                                                'bg-amber-50 text-amber-700 border border-amber-150': item.competition.toLowerCase() === 'medium',
                                                                                'bg-emerald-50 text-emerald-700 border border-emerald-150': item.competition.toLowerCase() === 'low',
                                                                                'bg-slate-50 text-slate-700 border border-slate-150': !['high', 'medium', 'low'].includes(item.competition.toLowerCase())
                                                                            }" x-text="item.competition"></span>
                                                </template>
                                                <template
                                                    x-if="item.competition === null || item.competition === undefined">
                                                    <span class="text-slate-400 font-normal">-</span>
                                                </template>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Controls -->
                        <div
                            class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4 bg-slate-50/50">
                            <div class="text-xs text-slate-500 font-medium">
                                Showing <span class="font-bold text-slate-700" x-text="startIndex"></span> to
                                <span class="font-bold text-slate-700" x-text="endIndex"></span> of
                                <span class="font-bold text-slate-700" x-text="results.length"></span> keywords
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="prevPage" :disabled="currentPage === 1"
                                    class="inline-flex items-center justify-center px-3.5 py-1.5 border border-slate-200 text-xs font-semibold rounded-lg text-slate-700 bg-white hover:bg-slate-50 hover:border-slate-300 shadow-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed select-none cursor-pointer">
                                    Prev
                                </button>
                                <div class="text-xs text-slate-500 font-medium px-2 select-none">
                                    Page <span class="font-bold text-slate-700" x-text="currentPage"></span> of
                                    <span class="font-bold text-slate-700" x-text="totalPages"></span>
                                </div>
                                <button @click="nextPage" :disabled="currentPage === totalPages"
                                    class="inline-flex items-center justify-center px-3.5 py-1.5 border border-slate-200 text-xs font-semibold rounded-lg text-slate-700 bg-white hover:bg-slate-50 hover:border-slate-300 shadow-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed select-none cursor-pointer">
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <script>
        function keywordResearchApp() {
            return {
                searchQuery: '',
                loading: false,
                fetchMetrics: false,
                errorMessage: '',
                dataSource: '',
                results: [],
                sortOrder: 'desc',
                autocompleteSuggestions: [],
                showDropdown: false,
                currentPage: 1,
                pageSize: 50,

                async fetchSuggestions() {
                    const query = this.searchQuery.trim();
                    if (query.length < 2) {
                        this.autocompleteSuggestions = [];
                        this.showDropdown = false;
                        return;
                    }

                    try {
                        const response = await fetch("{{ route('admin.keywords.autocomplete') }}?query=" + encodeURIComponent(query), {
                            method: "GET",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.autocompleteSuggestions = data;
                            this.showDropdown = true;
                        }
                    } catch (error) {
                        console.error("Autocomplete fetch error: ", error);
                    }
                },

                selectSuggestion(keyword) {
                    this.searchQuery = keyword;
                    this.showDropdown = false;
                    this.performSearch();
                },

                async performSearch() {
                    if (!this.searchQuery) return;
                    this.loading = true;
                    this.errorMessage = '';
                    this.results = [];
                    this.currentPage = 1;

                    try {
                        const response = await fetch('{{ route("admin.keywords.search") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                query: this.searchQuery,
                                fetch_metrics: this.fetchMetrics
                            })
                        });

                        const data = await response.json();
                        if (data.success) {
                            this.results = data.data;
                            this.dataSource = data.source;
                        } else {
                            this.errorMessage = data.message || 'Terjadi kesalahan saat memproses data.';
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.errorMessage = 'Gagal menghubungi server.';
                    } finally {
                        this.loading = false;
                    }
                },

                get sortedKeywords() {
                    return [...this.results].sort((a, b) => {
                        const volA = parseInt(a.search_volume) || 0;
                        const volB = parseInt(b.search_volume) || 0;
                        return this.sortOrder === 'asc' ? volA - volB : volB - volA;
                    });
                },

                get paginatedResults() {
                    const start = (this.currentPage - 1) * this.pageSize;
                    return this.sortedKeywords.slice(start, start + this.pageSize);
                },

                get totalPages() {
                    return Math.ceil(this.results.length / this.pageSize) || 1;
                },

                get startIndex() {
                    return this.results.length === 0 ? 0 : (this.currentPage - 1) * this.pageSize + 1;
                },

                get endIndex() {
                    return Math.min(this.currentPage * this.pageSize, this.results.length);
                },

                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.currentPage++;
                    }
                },

                prevPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                    }
                },

                toggleSort() {
                    this.sortOrder = this.sortOrder === 'desc' ? 'asc' : 'desc';
                    this.currentPage = 1;
                },

                formatNumber(num) {
                    if (num === null || num === undefined) return '-';
                    return new Intl.NumberFormat('id-ID').format(num);
                },

                exportCSV() {
                    let csv = 'Keyword,Search Volume,CPC,Competition\n';
                    this.sortedKeywords.forEach(item => {
                        const vol = item.search_volume !== null ? item.search_volume : '';
                        const cpc = item.cpc !== null ? item.cpc : '';
                        const comp = item.competition !== null ? item.competition : '';
                        csv += `"${item.keyword}",${vol},${cpc},"${comp}"\n`;
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