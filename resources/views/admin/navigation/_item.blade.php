{{--
    Variables: $item, $depth (0–3), $isFirst (bool), $pages, $flatItems
--}}
@php
    $plMap  = ['pl-6', 'pl-14', 'pl-20', 'pl-28'];
    $pl     = $plMap[$depth] ?? 'pl-28';
    $rowBg  = $depth > 0 ? 'bg-gray-50/40' : '';
@endphp

<li data-id="{{ $item->id }}">

    {{-- ── Row ─────────────────────────────────────────────────────────── --}}
    <div class="{{ $pl }} pr-6 py-3 flex items-center gap-3 hover:bg-gray-50 group {{ $rowBg }}">

        @if($depth === 0)
            <span class="cursor-grab text-gray-300 hover:text-gray-500 drag-handle select-none shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                </svg>
            </span>
        @else
            <svg class="w-3 h-3 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        @endif

        <div class="flex-1 min-w-0">
            <span class="{{ $depth === 0 ? 'text-sm font-medium text-gray-900' : 'text-sm text-gray-700' }}">{{ $item->label }}</span>
            <span class="ml-2 text-xs">
                @if($item->page_id && $item->page)
                    <span class="text-blue-500">/{{ $item->page->slug }}</span>
                @elseif($item->url)
                    <span class="text-gray-400">{{ $item->url }}</span>
                @else
                    <span class="text-gray-300">no link</span>
                @endif
            </span>
            <span class="sub-count ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500 {{ $item->children->isEmpty() ? 'hidden' : '' }}">
                {{ $item->children->count() }} sub
            </span>
        </div>

        <div class="flex items-center gap-3 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">

            {{-- Outdent ← --}}
            <button type="button"
                    data-action="outdent"
                    onclick="outdentItem({{ $item->id }})"
                    title="Move up one level"
                    class="text-gray-400 hover:text-blue-600 transition-colors leading-none {{ $depth === 0 ? 'hidden' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 6v12"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H7m0 0 4-4m-4 4 4 4"/>
                </svg>
            </button>

            {{-- Indent → --}}
            <button type="button"
                    data-action="indent"
                    onclick="indentItem({{ $item->id }})"
                    title="Nest under item above"
                    class="text-gray-400 hover:text-blue-600 transition-colors leading-none {{ $isFirst || $depth >= 3 ? 'hidden' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6v12"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h13m0 0-4-4m4 4-4 4"/>
                </svg>
            </button>

            <button type="button" onclick="toggleEdit('item-{{ $item->id }}')"
                    class="text-xs text-blue-600 hover:text-blue-800 font-medium">Edit</button>

            <form method="POST" action="{{ route('admin.navigation.destroy', $item) }}"
                  onsubmit="return confirm('Remove \'{{ addslashes($item->label) }}\'?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">Remove</button>
            </form>
        </div>
    </div>

    {{-- ── Inline edit form ────────────────────────────────────────────── --}}
    <div id="item-{{ $item->id }}-edit" class="hidden border-t border-gray-100 {{ $pl }} pr-6 py-5 bg-slate-50">
        <form method="POST" action="{{ route('admin.navigation.update', $item) }}" class="space-y-4">
            @csrf @method('PUT')
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Edit "{{ $item->label }}"</p>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Label</label>
                    <input type="text" name="label" value="{{ $item->label }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Link to</label>
                    <select name="page_id" onchange="syncUrlRow(this)"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">— Custom URL —</option>
                        @foreach($pages as $page)
                            <option value="{{ $page->id }}" {{ $item->page_id == $page->id ? 'selected' : '' }}>
                                {{ $page->name }} (/{{ $page->slug }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="url-row" style="{{ $item->page_id ? 'display:none' : '' }}">
                <label class="block text-xs font-medium text-gray-600 mb-1">Custom URL</label>
                <input type="text" name="url" value="{{ $item->url }}" placeholder="https://example.com"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Parent item <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <select name="parent_id"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">— Top level —</option>
                    @foreach($flatItems as $fi)
                        @if($fi['item']->id !== $item->id)
                            <option value="{{ $fi['item']->id }}" {{ $item->parent_id == $fi['item']->id ? 'selected' : '' }}>
                                {{ str_repeat('— ', $fi['depth']) }}{{ $fi['item']->label }}
                            </option>
                        @endif
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-400">Depth limit of 3 is enforced on save.</p>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                        class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">
                    Save changes
                </button>
                <button type="button" onclick="toggleEdit('item-{{ $item->id }}')"
                        class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    {{-- ── Children (recursive) ────────────────────────────────────────── --}}
    @if($item->children->isNotEmpty())
        <ul class="border-t border-gray-50 divide-y divide-gray-50">
            @foreach($item->children as $child)
                @include('admin.navigation._item', [
                    'item'      => $child,
                    'depth'     => $depth + 1,
                    'isFirst'   => $loop->first,
                    'pages'     => $pages,
                    'flatItems' => $flatItems,
                ])
            @endforeach
        </ul>
    @endif

</li>
