@extends('admin.layout')

@section('title', 'Menu Navigation')
@section('heading', 'Menu Navigation')

@section('content')

{{-- ── Unsaved-changes bar (hidden until dirty) ───────────────────────── --}}
<div id="save-bar" class="hidden -mx-8 -mt-8 mb-8 px-8 py-3 bg-amber-50 border-b border-amber-200 flex items-center justify-between">
    <p class="text-sm text-amber-800 font-medium">You have unsaved changes.</p>
    <div class="flex items-center gap-3">
        <button onclick="discardChanges()" class="text-sm text-gray-600 hover:text-gray-900 font-medium">Discard</button>
        <button onclick="saveNav()"
                class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">
            Save changes
        </button>
    </div>
</div>

<div class="max-w-2xl">

    {{-- ── Add item form ───────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Add Menu Item</h2>
        <form method="POST" action="{{ route('admin.navigation.store') }}" class="space-y-4">
            @csrf

            @if($errors->any())
                <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Label</label>
                    <input type="text" name="label" value="{{ old('label') }}" required placeholder="e.g. About"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Link to</label>
                    <select name="page_id" onchange="syncUrlRow(this)"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">— Custom URL —</option>
                        @foreach($pages as $page)
                            <option value="{{ $page->id }}" {{ old('page_id') == $page->id ? 'selected' : '' }}>
                                {{ $page->name }} (/{{ $page->slug }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="url-row">
                <label class="block text-xs font-medium text-gray-600 mb-1">Custom URL</label>
                <input type="text" name="url" value="{{ old('url') }}" placeholder="https://example.com"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            @if(!empty($flatItems))
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Parent item <span class="text-gray-400 font-normal">(optional — leave blank for top level)</span>
                </label>
                <select name="parent_id"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">— Top level —</option>
                    @foreach($flatItems as $fi)
                        @if($fi['depth'] < 3)
                            <option value="{{ $fi['item']->id }}" {{ old('parent_id') == $fi['item']->id ? 'selected' : '' }}>
                                {{ str_repeat('— ', $fi['depth']) }}{{ $fi['item']->label }}
                            </option>
                        @endif
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-400">Up to 3 levels deep supported.</p>
            </div>
            @endif

            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Item
                </button>
            </div>
        </form>
    </div>

    {{-- ── Current menu structure ──────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center gap-3">
            <h2 class="text-sm font-semibold text-gray-700 shrink-0 mr-auto">Current Navigation</h2>

            {{-- Logo position toggle --}}
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500 shrink-0">Logo</span>
                @php
                    $logoPositions = [
                        'left'   => 'Logo left',
                        'center' => 'Logo center',
                        'right'  => 'Logo right',
                    ];
                @endphp
                <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                    @foreach($logoPositions as $value => $title)
                        <button type="button"
                                data-logo-pos="{{ $value }}"
                                onclick="setLogoPosition('{{ $value }}')"
                                title="{{ $title }}"
                                class="px-2.5 py-1.5 transition-colors
                                       {{ ($navSetting->logo_position ?? 'left') === $value
                                           ? 'bg-blue-700 text-white'
                                           : 'bg-white text-gray-500 hover:bg-gray-50 hover:text-gray-800' }}
                                       {{ !$loop->last ? 'border-r border-gray-200' : '' }}">
                            <svg class="w-4 h-3.5" viewBox="0 0 24 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                @if($value === 'left')
                                    <rect x="1" y="4" width="7" height="8" rx="1.5"/>
                                    <rect x="11" y="5" width="12" height="2" rx="1" opacity=".5"/>
                                    <rect x="11" y="9" width="8"  height="2" rx="1" opacity=".5"/>
                                @elseif($value === 'center')
                                    <rect x="8.5" y="4" width="7" height="8" rx="1.5"/>
                                    <rect x="1"   y="5" width="5" height="2" rx="1" opacity=".5"/>
                                    <rect x="18"  y="5" width="5" height="2" rx="1" opacity=".5"/>
                                    <rect x="1"   y="9" width="4" height="2" rx="1" opacity=".5"/>
                                    <rect x="19"  y="9" width="4" height="2" rx="1" opacity=".5"/>
                                @else
                                    <rect x="16" y="4" width="7" height="8" rx="1.5"/>
                                    <rect x="1"  y="5" width="12" height="2" rx="1" opacity=".5"/>
                                    <rect x="1"  y="9" width="8"  height="2" rx="1" opacity=".5"/>
                                @endif
                            </svg>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Items alignment toggle --}}
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500 shrink-0">Items</span>
                <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                    @php
                        $aligns = [
                            'left'   => ['title' => 'Left',   'icon' => 'M3 6h18M3 11h12M3 16h15'],
                            'center' => ['title' => 'Center', 'icon' => 'M3 6h18M6 11h12M4.5 16h15'],
                            'right'  => ['title' => 'Right',  'icon' => 'M3 6h18M9 11h12M6 16h15'],
                        ];
                    @endphp
                    @foreach($aligns as $value => $opt)
                        <button type="button"
                                data-align="{{ $value }}"
                                onclick="setAlignment('{{ $value }}')"
                                title="{{ $opt['title'] }} align"
                                class="px-2.5 py-1.5 transition-colors
                                       {{ $navSetting->alignment === $value
                                           ? 'bg-blue-700 text-white'
                                           : 'bg-white text-gray-500 hover:bg-gray-50 hover:text-gray-800' }}
                                       {{ !$loop->last ? 'border-r border-gray-200' : '' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $opt['icon'] }}"/>
                            </svg>
                        </button>
                    @endforeach
                </div>
            </div>

            @if($items->isNotEmpty())
                <span class="text-xs text-gray-400 shrink-0">Drag to reorder</span>
            @endif
        </div>

        @if($items->isEmpty())
            <div class="p-12 text-center">
                <svg class="w-8 h-8 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <p class="text-gray-500 text-sm">No menu items yet.</p>
            </div>
        @else
            <ul id="nav-items-list" class="divide-y divide-gray-100">
                @foreach($items as $item)
                    @include('admin.navigation._item', [
                        'item'      => $item,
                        'depth'     => 0,
                        'isFirst'   => $loop->first,
                        'pages'     => $pages,
                        'flatItems' => $flatItems,
                    ])
                @endforeach
            </ul>
        @endif
    </div>

    {{-- ── Live preview ────────────────────────────────────────────────── --}}
    @if($items->isNotEmpty())
    @php $previewAlign = ['left'=>'justify-start','center'=>'justify-center','right'=>'justify-end'][$navSetting->alignment] ?? 'justify-start'; @endphp

    <style>
        /* Preview-scoped dropdown styles (pnav-*) */
        .pnav-item,.pnav-sub-item{position:relative}
        .pnav-dropdown,.pnav-sub-dropdown{position:absolute;min-width:12rem;background:#1f2937;border:1px solid rgba(255,255,255,.1);border-radius:.5rem;box-shadow:0 8px 24px rgba(0,0,0,.5);padding:.25rem 0;z-index:60}
        .pnav-dropdown{top:calc(100% + 4px);left:0}
        .pnav-sub-dropdown{top:0;left:100%;margin-left:2px}
        .pnav-sub-dropdown.opens-down{top:100%;left:0!important;right:auto!important;margin-left:0;margin-top:2px}
        #preview-desktop-nav[data-align="right"] .pnav-dropdown{left:auto;right:0}
        #preview-desktop-nav[data-align="right"] .pnav-sub-dropdown:not(.opens-down){left:auto;right:100%;margin-left:0;margin-right:2px}
        /* Mobile chevron */
        .pmob-chevron{transition:transform .2s}
        .pmob-chevron.open{transform:rotate(180deg)}
    </style>

    <div class="mt-6">
        {{-- Toggle --}}
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Preview</h2>
            <div class="flex rounded-lg border border-gray-200 overflow-hidden text-xs font-medium">
                <button type="button" id="btn-preview-desktop" onclick="setPreviewMode('desktop')"
                        class="px-3 py-1.5 bg-blue-700 text-white transition-colors">Desktop</button>
                <button type="button" id="btn-preview-mobile" onclick="setPreviewMode('mobile')"
                        class="px-3 py-1.5 bg-white text-gray-600 hover:bg-gray-50 border-l border-gray-200 transition-colors">Mobile</button>
            </div>
        </div>

        {{-- ── Desktop preview ──────────────────────────────────────────── --}}
        <div id="preview-desktop">
            <div class="rounded-xl border border-gray-200 overflow-visible shadow-sm">
                @php $logoPos = $navSetting->logo_position ?? 'left'; @endphp
                <nav id="preview-desktop-nav" data-align="{{ $navSetting->alignment }}"
                     class="bg-gray-900 px-6 rounded-xl {{ $logoPos === 'center' ? 'grid items-center' : 'flex items-center gap-1' }}"
                     style="height:52px;position:relative;z-index:10;{{ $logoPos === 'center' ? 'grid-template-columns:1fr auto 1fr;' : '' }}">

                    {{-- Shared logo element (JS moves it between zones) --}}
                    @php $logoEl = '<a href="#" id="preview-logo" class="flex items-center gap-2 shrink-0">
                        <svg width="24" height="24" viewBox="0 0 32 32" fill="none">
                            <path d="M8 4 C8 4,6 8,6 11 C6 13.5,7.5 15,9 15 L9 27" stroke="#60a5fa" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16 2 L16 27" stroke="#60a5fa" stroke-width="2.5" stroke-linecap="round"/>
                            <path d="M24 4 C24 4,26 8,26 11 C26 13.5,24.5 15,23 15 L23 27" stroke="#60a5fa" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9 15 L23 15" stroke="#60a5fa" stroke-width="2" stroke-linecap="round"/>
                            <path d="M12 27 L20 27" stroke="#60a5fa" stroke-width="2.2" stroke-linecap="round"/>
                        </svg>
                        <span class="text-white font-bold text-sm tracking-wide">Poseidon</span>
                    </a>'; @endphp

                    {{-- Left zone --}}
                    <div id="preview-logo-zone-l"
                         class="flex items-center gap-1 {{ $logoPos !== 'left' ? 'hidden' : '' }}">
                        @if($logoPos === 'left') {!! $logoEl !!} @endif
                    </div>

                    {{-- Center zone (items sit here for grid layout) --}}
                    <div id="preview-logo-zone-c"
                         class="flex items-center justify-center {{ $logoPos !== 'center' ? 'hidden' : '' }}">
                        @if($logoPos === 'center') {!! $logoEl !!} @endif
                    </div>

                    {{-- Items zone (flex-1 in flex mode, auto in grid mode) --}}
                    <div id="preview-items" class="flex items-center gap-1 {{ $logoPos === 'center' ? '' : 'flex-1' }} {{ $previewAlign }}
                                                   {{ $logoPos === 'left' ? ($navSetting->alignment === 'right' ? 'order-2' : '') : '' }}">
                        @foreach($items as $item)
                            @if($item->children->isNotEmpty())
                                <div class="pnav-item">
                                    <button onclick="pnavToggle(this,event)"
                                            class="flex items-center gap-1 px-3 py-1.5 text-gray-300 text-sm font-medium rounded hover:text-white hover:bg-white/10 transition-colors">
                                        {{ $item->label }}
                                        <svg class="w-3 h-3 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <div class="pnav-dropdown hidden">
                                        @foreach($item->children as $child)
                                            @if($child->children->isNotEmpty())
                                                <div class="pnav-sub-item">
                                                    <button onclick="pnavToggle(this,event)"
                                                            class="w-full flex items-center justify-between px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white text-left">
                                                        {{ $child->label }}
                                                        <svg class="w-3 h-3 ml-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                        </svg>
                                                    </button>
                                                    <div class="pnav-sub-dropdown hidden">
                                                        @foreach($child->children as $grandchild)
                                                            @if($grandchild->children->isNotEmpty())
                                                                <div class="pnav-sub-item">
                                                                    <button onclick="pnavToggle(this,event)"
                                                                            class="w-full flex items-center justify-between px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white text-left">
                                                                        {{ $grandchild->label }}
                                                                        <svg class="w-3 h-3 ml-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                                                        </svg>
                                                                    </button>
                                                                    <div class="pnav-sub-dropdown opens-down hidden">
                                                                        @foreach($grandchild->children as $great)
                                                                            <span class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white cursor-pointer">{{ $great->label }}</span>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <span class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white cursor-pointer">{{ $grandchild->label }}</span>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @else
                                                <span class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white cursor-pointer">{{ $child->label }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <span class="px-3 py-1.5 text-gray-300 text-sm font-medium rounded hover:text-white hover:bg-white/10 cursor-pointer transition-colors">{{ $item->label }}</span>
                            @endif
                        @endforeach
                    </div>

                    {{-- Right zone --}}
                    <div id="preview-logo-zone-r"
                         class="flex items-center justify-end gap-1 {{ $logoPos !== 'right' ? 'hidden' : '' }}">
                        @if($logoPos === 'right') {!! $logoEl !!} @endif
                    </div>

                </nav>
            </div>
            <p class="mt-2 text-xs text-gray-400">Click items to open dropdowns. Click outside to close.</p>
        </div>

        {{-- ── Mobile preview ───────────────────────────────────────────── --}}
        <div id="preview-mobile" class="hidden">
            <div class="rounded-xl border border-gray-200 overflow-hidden shadow-sm mx-auto" style="max-width:390px;">
                <nav class="bg-gray-900">
                    <div class="flex items-center px-4 h-14">
                        <a href="#" class="flex items-center gap-2 shrink-0 mr-auto">
                            <svg width="22" height="22" viewBox="0 0 32 32" fill="none">
                                <path d="M8 4 C8 4,6 8,6 11 C6 13.5,7.5 15,9 15 L9 27"  stroke="#60a5fa" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M16 2 L16 27" stroke="#60a5fa" stroke-width="2.5" stroke-linecap="round"/>
                                <path d="M24 4 C24 4,26 8,26 11 C26 13.5,24.5 15,23 15 L23 27" stroke="#60a5fa" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9 15 L23 15" stroke="#60a5fa" stroke-width="2"   stroke-linecap="round"/>
                                <path d="M12 27 L20 27" stroke="#60a5fa" stroke-width="2.2" stroke-linecap="round"/>
                            </svg>
                            <span class="text-white font-bold text-sm tracking-wide">Poseidon</span>
                        </a>
                        <button onclick="togglePreviewMobileMenu()" class="p-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-colors">
                            <svg id="pmenu-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                            <svg id="pmenu-close" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div id="preview-mobile-menu" class="hidden border-t border-gray-700/50 pb-2">
                        @foreach($items as $item)
                            @if($item->children->isNotEmpty())
                                <div>
                                    <button onclick="previewMobToggle(this)"
                                            class="w-full flex items-center justify-between px-6 py-3 text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-white transition-colors text-left">
                                        {{ $item->label }}
                                        <svg class="pmob-chevron w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <div class="hidden">
                                        @foreach($item->children as $child)
                                            @if($child->children->isNotEmpty())
                                                <div>
                                                    <button onclick="previewMobToggle(this)"
                                                            class="w-full flex items-center justify-between pl-10 pr-6 py-2.5 text-sm text-gray-400 hover:bg-gray-800 hover:text-white transition-colors text-left">
                                                        {{ $child->label }}
                                                        <svg class="pmob-chevron w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                        </svg>
                                                    </button>
                                                    <div class="hidden">
                                                        @foreach($child->children as $grandchild)
                                                            @if($grandchild->children->isNotEmpty())
                                                                <div>
                                                                    <button onclick="previewMobToggle(this)"
                                                                            class="w-full flex items-center justify-between pl-16 pr-6 py-2 text-sm text-gray-500 hover:bg-gray-800 hover:text-white transition-colors text-left">
                                                                        {{ $grandchild->label }}
                                                                        <svg class="pmob-chevron w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                                        </svg>
                                                                    </button>
                                                                    <div class="hidden">
                                                                        @foreach($grandchild->children as $great)
                                                                            <span class="block pl-20 pr-6 py-2 text-sm text-gray-500 hover:bg-gray-800 hover:text-white cursor-pointer transition-colors">{{ $great->label }}</span>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <span class="block pl-16 pr-6 py-2 text-sm text-gray-500 hover:bg-gray-800 hover:text-white cursor-pointer transition-colors">{{ $grandchild->label }}</span>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @else
                                                <span class="block pl-10 pr-6 py-2.5 text-sm text-gray-400 hover:bg-gray-800 hover:text-white cursor-pointer transition-colors">{{ $child->label }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <span class="block px-6 py-3 text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-white cursor-pointer transition-colors">{{ $item->label }}</span>
                            @endif
                        @endforeach
                    </div>
                </nav>
            </div>
            <p class="mt-2 text-xs text-gray-400">Tap the hamburger to expand the mobile menu.</p>
        </div>
    </div>
    @endif
</div>

<script>
// ── Shared helpers ───────────────────────────────────────────────────────
function toggleEdit(key) {
    var panel = document.getElementById(key + '-edit');
    if (!panel) return;
    var opening = panel.classList.contains('hidden');
    document.querySelectorAll('[id$="-edit"]').forEach(function(el) { el.classList.add('hidden'); });
    if (opening) panel.classList.remove('hidden');
}
function syncUrlRow(sel) {
    var row = sel.closest('form').querySelector('.url-row');
    if (row) row.style.display = sel.value ? 'none' : '';
}
document.querySelectorAll('select[name="page_id"]').forEach(function(s) { syncUrlRow(s); });

// ── Dirty / Save bar ─────────────────────────────────────────────────────
function markDirty() {
    document.getElementById('save-bar').classList.remove('hidden');
}
function discardChanges() { window.location.reload(); }

window.addEventListener('beforeunload', function(e) {
    if (!document.getElementById('save-bar').classList.contains('hidden')) {
        e.preventDefault(); e.returnValue = '';
    }
});

// Suppress beforeunload for deliberate form navigations (add / edit / delete)
document.querySelectorAll('form:not(#save-form)').forEach(function(f) {
    f.addEventListener('submit', function() { window.onbeforeunload = null; });
});

// ── Logo position (JS-only until Save) ───────────────────────────────────
var currentLogoPosition = '{{ $navSetting->logo_position ?? 'left' }}';

function setLogoPosition(pos) {
    currentLogoPosition = pos;
    document.querySelectorAll('[data-logo-pos]').forEach(function(btn) {
        var active = btn.dataset.logoPos === pos;
        btn.classList.toggle('bg-blue-700', active);
        btn.classList.toggle('text-white', active);
        btn.classList.toggle('bg-white', !active);
        btn.classList.toggle('text-gray-500', !active);
    });
    updatePreviewLogoPosition(pos);
    markDirty();
}

function updatePreviewLogoPosition(pos) {
    var logo     = document.getElementById('preview-logo');
    var navBar   = document.getElementById('preview-nav-bar');
    var items    = document.getElementById('preview-items');
    var logoZoneL = document.getElementById('preview-logo-zone-l');
    var logoZoneC = document.getElementById('preview-logo-zone-c');
    var logoZoneR = document.getElementById('preview-logo-zone-r');
    if (!logo || !navBar) return;

    // Reset
    navBar.classList.remove('grid');
    navBar.style.gridTemplateColumns = '';
    ['mr-auto','mr-5','ml-5'].forEach(function(c) { logo.classList.remove(c); });

    if (logoZoneL) logoZoneL.classList.add('hidden');
    if (logoZoneC) logoZoneC.classList.add('hidden');
    if (logoZoneR) logoZoneR.classList.add('hidden');

    if (pos === 'left') {
        if (logoZoneL) { logoZoneL.classList.remove('hidden'); logoZoneL.appendChild(logo); }
        logo.classList.add(currentAlignment === 'right' ? 'mr-auto' : 'mr-5');
    } else if (pos === 'center') {
        navBar.classList.add('grid');
        navBar.style.gridTemplateColumns = '1fr auto 1fr';
        if (logoZoneC) { logoZoneC.classList.remove('hidden'); logoZoneC.appendChild(logo); }
    } else {
        if (logoZoneR) { logoZoneR.classList.remove('hidden'); logoZoneR.appendChild(logo); }
        logo.classList.add('ml-5');
    }
}

// ── Alignment (JS-only until Save) ───────────────────────────────────────
var currentAlignment = '{{ $navSetting->alignment }}';
var ALIGN_JUSTIFY = { left: 'justify-start', center: 'justify-center', right: 'justify-end' };

function setAlignment(val) {
    currentAlignment = val;
    document.querySelectorAll('[data-align]').forEach(function(btn) {
        var active = btn.dataset.align === val;
        btn.classList.toggle('bg-blue-700', active);
        btn.classList.toggle('text-white', active);
        btn.classList.toggle('bg-white', !active);
        btn.classList.toggle('text-gray-500', !active);
    });
    var previewItems = document.getElementById('preview-items');
    if (previewItems) {
        Object.values(ALIGN_JUSTIFY).forEach(function(c) { previewItems.classList.remove(c); });
        previewItems.classList.add(ALIGN_JUSTIFY[val] || 'justify-start');
    }
    var logo = document.getElementById('preview-logo');
    if (logo) {
        logo.classList.toggle('mr-auto', val === 'right');
        logo.classList.toggle('mr-5',   val !== 'right');
    }
    var dNav = document.getElementById('preview-desktop-nav');
    if (dNav) dNav.setAttribute('data-align', val);
    // Re-apply logo position since mr-auto/mr-5 depends on alignment
    updatePreviewLogoPosition(currentLogoPosition);
    markDirty();
}

// ── Preview mode toggle ───────────────────────────────────────────────────
function setPreviewMode(mode) {
    var isDesktop = mode === 'desktop';
    document.getElementById('preview-desktop').classList.toggle('hidden', !isDesktop);
    document.getElementById('preview-mobile').classList.toggle('hidden', isDesktop);
    var btnD = document.getElementById('btn-preview-desktop');
    var btnM = document.getElementById('btn-preview-mobile');
    btnD.className = 'px-3 py-1.5 font-medium transition-colors ' + (isDesktop ? 'bg-blue-700 text-white' : 'bg-white text-gray-600 hover:bg-gray-50');
    btnM.className = 'px-3 py-1.5 font-medium transition-colors border-l border-gray-200 ' + (!isDesktop ? 'bg-blue-700 text-white' : 'bg-white text-gray-600 hover:bg-gray-50');
}

// ── Preview desktop click-dropdowns ──────────────────────────────────────
function pnavToggle(btn, e) {
    e.stopPropagation();
    var panel = btn.nextElementSibling;
    var wasHidden = panel.classList.contains('hidden');
    document.querySelectorAll('.pnav-dropdown, .pnav-sub-dropdown').forEach(function(d) {
        if (!d.contains(btn)) d.classList.add('hidden');
    });
    if (wasHidden) panel.classList.remove('hidden');
}
document.addEventListener('click', function() {
    document.querySelectorAll('.pnav-dropdown, .pnav-sub-dropdown').forEach(function(d) {
        d.classList.add('hidden');
    });
});

// ── Preview mobile menu ───────────────────────────────────────────────────
function togglePreviewMobileMenu() {
    var open = document.getElementById('preview-mobile-menu').classList.toggle('hidden') === false;
    document.getElementById('pmenu-icon').classList.toggle('hidden', open);
    document.getElementById('pmenu-close').classList.toggle('hidden', !open);
}
function previewMobToggle(btn) {
    var panel   = btn.nextElementSibling;
    var chevron = btn.querySelector('.pmob-chevron');
    var opening = panel.classList.toggle('hidden') === false;
    if (chevron) chevron.classList.toggle('open', opening);
}

// ── Depth helpers ────────────────────────────────────────────────────────
var PL = ['pl-6', 'pl-14', 'pl-20', 'pl-28'];

function getDepth(li) {
    var d = 0, el = li.parentElement;
    while (el && el.id !== 'nav-items-list') { if (el.tagName === 'UL') d++; el = el.parentElement; }
    return d;
}
function getSubtreeDepth(li, cur) {
    cur = cur || 0;
    var ul = li.querySelector(':scope > ul');
    if (!ul) return cur;
    var max = cur;
    Array.from(ul.children).forEach(function(c) {
        if (c.dataset && c.dataset.id) max = Math.max(max, getSubtreeDepth(c, cur + 1));
    });
    return max;
}

function updateItemStyles(li) {
    var d = getDepth(li);
    var row  = li.querySelector(':scope > div:first-child');
    var edit = li.querySelector(':scope > [id$="-edit"]');
    PL.forEach(function(c) { if (row) row.classList.remove(c); if (edit) edit.classList.remove(c); });
    var pl = PL[Math.min(d, 3)];
    if (row)  { row.classList.add(pl);  row.classList.toggle('bg-gray-50/40', d > 0); }
    if (edit) edit.classList.add(pl);
    var ul = li.querySelector(':scope > ul');
    if (ul) Array.from(ul.children).forEach(function(c) { if (c.dataset && c.dataset.id) updateItemStyles(c); });
}

function refreshButtons() {
    var list = document.getElementById('nav-items-list');
    if (!list) return;
    list.querySelectorAll('[data-id]').forEach(function(li) {
        var d = getDepth(li);
        var pul = li.parentElement;
        var sibs = Array.from(pul.children).filter(function(e) { return e.dataset && e.dataset.id; });
        var isFirst = sibs[0] === li;

        var ib = li.querySelector(':scope > div [data-action="indent"]');
        var ob = li.querySelector(':scope > div [data-action="outdent"]');
        if (ib) ib.classList.toggle('hidden', isFirst || d >= 3 || (d + 1 + getSubtreeDepth(li) > 3));
        if (ob) ob.classList.toggle('hidden', d === 0);

        var badge = li.querySelector(':scope > div .sub-count');
        if (badge) {
            var cul = li.querySelector(':scope > ul');
            var cnt = cul ? Array.from(cul.children).filter(function(c) { return c.dataset && c.dataset.id; }).length : 0;
            badge.textContent = cnt + ' sub';
            badge.classList.toggle('hidden', cnt === 0);
        }

        li.setAttribute('draggable', d === 0 ? 'true' : 'false');
    });
}

// ── Indent / Outdent ─────────────────────────────────────────────────────
function indentItem(id) {
    var list = document.getElementById('nav-items-list');
    var li   = list.querySelector('[data-id="' + id + '"]');
    var pul  = li.parentElement;
    var sibs = Array.from(pul.children).filter(function(e) { return e.dataset && e.dataset.id; });
    var idx  = sibs.indexOf(li);
    if (idx <= 0) return;

    var prev = sibs[idx - 1];
    var newD = getDepth(prev) + 1;
    if (newD > 3 || newD + getSubtreeDepth(li) > 3) return;

    var cul = prev.querySelector(':scope > ul');
    if (!cul) { cul = document.createElement('ul'); cul.className = 'border-t border-gray-50 divide-y divide-gray-50'; prev.appendChild(cul); }
    cul.appendChild(li);

    updateItemStyles(li);
    refreshButtons();
    markDirty();
}

function outdentItem(id) {
    var list    = document.getElementById('nav-items-list');
    var li      = list.querySelector('[data-id="' + id + '"]');
    var cul     = li.parentElement;
    var parentLi = cul.parentElement;
    if (!parentLi || !parentLi.dataset || !parentLi.dataset.id) return;

    var gpul = parentLi.parentElement;
    gpul.insertBefore(li, parentLi.nextSibling);
    if (cul.children.length === 0) cul.remove();

    updateItemStyles(li);
    refreshButtons();
    markDirty();
}

// ── Drag reorder (top-level, no auto-save) ───────────────────────────────
(function() {
    var list = document.getElementById('nav-items-list');
    if (!list) return;
    var dragging = null;

    list.addEventListener('dragstart', function(e) {
        var li = e.target.closest('[data-id]');
        if (!li || getDepth(li) !== 0) { e.preventDefault(); return; }
        dragging = li;
        setTimeout(function() { li.classList.add('opacity-50'); }, 0);
    });
    list.addEventListener('dragend', function() {
        if (dragging) dragging.classList.remove('opacity-50');
        dragging = null;
        refreshButtons();
        markDirty();
    });
    list.addEventListener('dragover', function(e) {
        e.preventDefault();
        if (!dragging) return;
        var target = null, el = e.target;
        while (el && el !== list) {
            if (el.parentElement === list && el.dataset && el.dataset.id) { target = el; break; }
            el = el.parentElement;
        }
        if (!target || target === dragging) return;
        var r = target.getBoundingClientRect();
        list.insertBefore(dragging, e.clientY < r.top + r.height / 2 ? target : target.nextSibling);
    });
})();

// ── Read state & Save ────────────────────────────────────────────────────
function readNavState() {
    var items = [];
    function walk(ul, parentId) {
        var order = 0;
        Array.from(ul.children).forEach(function(li) {
            if (!li.dataset || !li.dataset.id) return;
            items.push({ id: parseInt(li.dataset.id), parent_id: parentId, sort_order: order++ });
            var cul = li.querySelector(':scope > ul');
            if (cul) walk(cul, parseInt(li.dataset.id));
        });
    }
    walk(document.getElementById('nav-items-list'), null);
    return { alignment: currentAlignment, logoPosition: currentLogoPosition, items: items };
}

function saveNav() {
    var state = readNavState();
    var form  = document.createElement('form');
    form.id     = 'save-form';
    form.method = 'POST';
    form.action = '{{ route('admin.navigation.save-all') }}';

    function hidden(name, val) {
        var i = document.createElement('input');
        i.type = 'hidden'; i.name = name; i.value = val === null ? '' : val;
        form.appendChild(i);
    }
    hidden('_token', '{{ csrf_token() }}');
    hidden('alignment', state.alignment);
    hidden('logo_position', state.logoPosition);
    state.items.forEach(function(item, idx) {
        hidden('items[' + idx + '][id]',         item.id);
        hidden('items[' + idx + '][parent_id]',  item.parent_id);
        hidden('items[' + idx + '][sort_order]', item.sort_order);
    });

    window.onbeforeunload = null; // don't warn on intentional save
    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection
