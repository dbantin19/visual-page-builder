@extends('admin.layout')

@section('title', 'Pages')
@section('heading', 'Pages')

@section('header-actions')
    <a href="{{ route('admin.pages.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Page
    </a>
@endsection

@section('content')
    @if($pages->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <svg class="w-10 h-10 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-500 text-sm">No pages yet.</p>
            <a href="{{ route('admin.pages.create') }}" class="mt-3 inline-block text-sm text-blue-700 hover:underline">Create your first page</a>
        </div>
    @else
        <div class="mb-4">
            <div class="relative max-w-xs">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
                <input id="page-search" type="text" placeholder="Search pages…"
                       class="w-full pl-9 pr-3 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-gray-400">
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table id="pages-table" class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer select-none hover:text-gray-700" data-col="0">
                            <span class="inline-flex items-center gap-1">Name<span class="sort-icon text-gray-300">⇅</span></span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer select-none hover:text-gray-700" data-col="1">
                            <span class="inline-flex items-center gap-1">Slug<span class="sort-icon text-gray-300">⇅</span></span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer select-none hover:text-gray-700" data-col="2">
                            <span class="inline-flex items-center gap-1">Published<span class="sort-icon text-gray-300">⇅</span></span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer select-none hover:text-gray-700" data-col="3">
                            <span class="inline-flex items-center gap-1">Indexed<span class="sort-icon text-gray-300">⇅</span></span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer select-none hover:text-gray-700" data-col="4">
                            <span class="inline-flex items-center gap-1">Updated<span class="sort-icon text-gray-300">⇅</span></span>
                        </th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($pages as $page)
                        <tr class="hover:bg-gray-50" data-updated="{{ $page->updated_at->timestamp }}">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $page->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 font-mono">/{{ $page->slug }}</td>
                            <td class="px-6 py-4">
                                @if($page->is_published)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Published</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">Draft</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($page->is_indexed)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Indexed</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">No-index</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $page->updated_at->diffForHumans() }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('pages.show', $page->slug) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                        View
                                    </a>
                                    <a href="{{ route('admin.pages.builder', $page) }}"
                                       class="inline-flex items-center gap-1.5 text-sm text-violet-600 hover:text-violet-800 font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                                        </svg>
                                        Builder
                                    </a>
                                    <a href="{{ route('admin.pages.edit', $page) }}"
                                       class="text-sm text-blue-700 hover:underline">Details</a>
                                    <form method="POST" action="{{ route('admin.pages.destroy', $page) }}"
                                          onsubmit="return confirm('Delete this page?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 hover:underline">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <script>
    (function () {
        const table = document.getElementById('pages-table');
        if (!table) return;
        const tbody = table.querySelector('tbody');
        const search = document.getElementById('page-search');
        let sortCol = null, sortAsc = true;

        function rows() { return Array.from(tbody.querySelectorAll('tr')); }

        function cellText(row, col) {
            const cell = row.cells[col];
            if (!cell) return '';
            if (col === 4) return row.dataset.updated || '';
            return cell.textContent.trim().toLowerCase();
        }

        function applySort(col) {
            const sorted = rows().sort((a, b) => {
                const av = cellText(a, col), bv = cellText(b, col);
                return sortAsc ? av.localeCompare(bv, undefined, {numeric: true}) : bv.localeCompare(av, undefined, {numeric: true});
            });
            sorted.forEach(r => tbody.appendChild(r));
        }

        function applySearch(q) {
            rows().forEach(row => {
                const text = Array.from(row.cells).slice(0, 5).map(c => c.textContent.trim().toLowerCase()).join(' ');
                row.style.display = text.includes(q) ? '' : 'none';
            });
        }

        table.querySelectorAll('thead th[data-col]').forEach(th => {
            th.addEventListener('click', () => {
                const col = parseInt(th.dataset.col, 10);
                if (sortCol === col) { sortAsc = !sortAsc; } else { sortCol = col; sortAsc = true; }
                table.querySelectorAll('thead th[data-col] .sort-icon').forEach(icon => {
                    icon.textContent = '⇅'; icon.classList.remove('text-gray-700'); icon.classList.add('text-gray-300');
                });
                const icon = th.querySelector('.sort-icon');
                icon.textContent = sortAsc ? '↑' : '↓';
                icon.classList.remove('text-gray-300'); icon.classList.add('text-gray-700');
                applySort(col);
            });
        });

        search.addEventListener('input', () => applySearch(search.value.trim().toLowerCase()));
    })();
    </script>
@endsection
