<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->meta_title ?: $page->name }}</title>
    @if($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
    @if(!$page->is_indexed)
        <meta name="robots" content="noindex, nofollow">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .nav-item, .nav-sub-item { position: relative; }

        /* Shared panel styling */
        .nav-dropdown, .nav-sub-dropdown {
            position: absolute;
            min-width: 12rem;
            background: #1f2937;
            border: 1px solid rgba(255,255,255,.1);
            border-radius: .5rem;
            box-shadow: 0 8px 24px rgba(0,0,0,.5);
            padding: .25rem 0;
            z-index: 60;
        }

        /* Level-0 panel: drops below nav bar, anchored left by default */
        .nav-dropdown { top: calc(100% + 4px); left: 0; }

        /* Right-aligned nav: anchor level-0 panel to the right edge of its trigger */
        nav[data-align="right"] .nav-dropdown { left: auto; right: 0; }

        /* Level-1+ panel: opens to the right of its trigger row */
        .nav-sub-dropdown { top: 0; left: 100%; margin-left: 2px; }

        /* Right-aligned nav: flip side-panels to open leftward (away from edge) */
        nav[data-align="right"] .nav-sub-dropdown:not(.opens-down) { left: auto; right: 100%; margin-left: 0; margin-right: 2px; }

        /* Deepest level: drops downward instead of sideways */
        .nav-sub-dropdown.opens-down { top: 100%; left: 0 !important; right: auto !important; margin-left: 0; margin-right: 0; margin-top: 2px; }

        /* Mobile accordion chevron rotation */
        .mob-chevron { transition: transform .2s; }
        .mob-chevron.open { transform: rotate(180deg); }
    </style>
    {!! $page->head_section !!}
</head>
<body class="m-0 p-0">

@php
    $navSetting  = \App\Models\NavSetting::get();
    $alignClass  = ['left' => 'justify-start', 'center' => 'justify-center', 'right' => 'justify-end'][$navSetting->alignment] ?? 'justify-start';
    $logoPos     = $navSetting->logo_position ?? 'left';
    $navItems   = \App\Models\NavMenuItem::with([
        'page',
        'children' => fn($q) => $q->orderBy('sort_order')->with([
            'page',
            'children' => fn($q) => $q->orderBy('sort_order')->with([
                'page',
                'children' => fn($q) => $q->orderBy('sort_order')->with('page'),
            ]),
        ]),
    ])
    ->whereNull('parent_id')
    ->orderBy('sort_order')
    ->get();
@endphp

@if($navItems->isNotEmpty())
<nav class="bg-gray-900 sticky top-0 z-50 shadow-md" data-align="{{ $navSetting->alignment }}">

    {{-- ── Desktop top bar ────────────────────────────────────────────── --}}
    {{-- Logo center uses absolute positioning so items loop only appears once --}}
    <div class="hidden md:flex items-center px-6 h-14 relative">

        @if($logoPos === 'left')
            <a href="/" class="flex items-center gap-2 shrink-0 {{ $alignClass === 'justify-end' ? 'mr-auto' : 'mr-5' }}">
                <svg width="26" height="26" viewBox="0 0 32 32" fill="none">
                    <path d="M8 4 C8 4,6 8,6 11 C6 13.5,7.5 15,9 15 L9 27" stroke="#60a5fa" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 2 L16 27" stroke="#60a5fa" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M24 4 C24 4,26 8,26 11 C26 13.5,24.5 15,23 15 L23 27" stroke="#60a5fa" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 15 L23 15" stroke="#60a5fa" stroke-width="2" stroke-linecap="round"/>
                    <path d="M12 27 L20 27" stroke="#60a5fa" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
                <span class="text-white font-bold text-sm tracking-wide">Poseidon</span>
            </a>
        @elseif($logoPos === 'center')
            {{-- Absolutely centered — items take full width with padding so they don't overlap --}}
            <a href="/" style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);z-index:10;"
               class="flex items-center gap-2 shrink-0">
                <svg width="26" height="26" viewBox="0 0 32 32" fill="none">
                    <path d="M8 4 C8 4,6 8,6 11 C6 13.5,7.5 15,9 15 L9 27" stroke="#60a5fa" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 2 L16 27" stroke="#60a5fa" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M24 4 C24 4,26 8,26 11 C26 13.5,24.5 15,23 15 L23 27" stroke="#60a5fa" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 15 L23 15" stroke="#60a5fa" stroke-width="2" stroke-linecap="round"/>
                    <path d="M12 27 L20 27" stroke="#60a5fa" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
                <span class="text-white font-bold text-sm tracking-wide">Poseidon</span>
            </a>
        @endif

        {{-- Desktop nav items --}}
        <div class="flex items-center gap-1 flex-1 {{ $alignClass }} {{ $logoPos === 'center' ? 'px-40' : '' }}">
            @foreach($navItems as $item)
                @php $href = $item->resolvedUrl(); $isActive = request()->is(ltrim($href, '/')); @endphp

                @if($item->children->isNotEmpty())
                    <div class="nav-item">
                        <button onclick="navToggle(this,event)"
                                class="flex items-center gap-1 px-3 py-1.5 text-sm font-medium rounded transition-colors
                                       {{ $isActive ? 'text-white' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                            {{ $item->label }}
                            <svg class="w-3 h-3 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div class="nav-dropdown hidden">
                            @foreach($item->children as $child)
                                @php $childHref = $child->resolvedUrl(); @endphp
                                @if($child->children->isNotEmpty())
                                    <div class="nav-sub-item">
                                        <button onclick="navToggle(this,event)"
                                                class="w-full flex items-center justify-between px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white text-left">
                                            {{ $child->label }}
                                            <svg class="w-3 h-3 ml-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </button>
                                        <div class="nav-sub-dropdown hidden">
                                            @foreach($child->children as $grandchild)
                                                @php $grandHref = $grandchild->resolvedUrl(); @endphp
                                                @if($grandchild->children->isNotEmpty())
                                                    <div class="nav-sub-item">
                                                        <button onclick="navToggle(this,event)"
                                                                class="w-full flex items-center justify-between px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white text-left">
                                                            {{ $grandchild->label }}
                                                            <svg class="w-3 h-3 ml-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                                            </svg>
                                                        </button>
                                                        <div class="nav-sub-dropdown opens-down hidden">
                                                            @foreach($grandchild->children as $great)
                                                                <a href="{{ $great->resolvedUrl() }}"
                                                                   class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">
                                                                    {{ $great->label }}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @else
                                                    <a href="{{ $grandHref }}"
                                                       class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">
                                                        {{ $grandchild->label }}
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <a href="{{ $childHref }}"
                                       class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">
                                        {{ $child->label }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="{{ $href }}"
                       class="px-3 py-1.5 text-sm font-medium rounded transition-colors
                              {{ $isActive ? 'text-white border-b-2 border-blue-400' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                        {{ $item->label }}
                    </a>
                @endif
            @endforeach
        </div>

        @if($logoPos === 'right')
            <a href="/" class="flex items-center gap-2 shrink-0 ml-5">
                <svg width="26" height="26" viewBox="0 0 32 32" fill="none">
                    <path d="M8 4 C8 4,6 8,6 11 C6 13.5,7.5 15,9 15 L9 27" stroke="#60a5fa" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 2 L16 27" stroke="#60a5fa" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M24 4 C24 4,26 8,26 11 C26 13.5,24.5 15,23 15 L23 27" stroke="#60a5fa" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 15 L23 15" stroke="#60a5fa" stroke-width="2" stroke-linecap="round"/>
                    <path d="M12 27 L20 27" stroke="#60a5fa" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
                <span class="text-white font-bold text-sm tracking-wide">Poseidon</span>
            </a>
        @endif
    </div>{{-- end desktop bar --}}

    {{-- ── Mobile top bar (logo always left regardless of position setting) --}}
    <div class="flex md:hidden items-center px-6 h-14">
        <a href="/" class="flex items-center gap-2 shrink-0 mr-auto">
            <svg width="26" height="26" viewBox="0 0 32 32" fill="none">
                <path d="M8 4 C8 4,6 8,6 11 C6 13.5,7.5 15,9 15 L9 27" stroke="#60a5fa" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M16 2 L16 27" stroke="#60a5fa" stroke-width="2.5" stroke-linecap="round"/>
                <path d="M24 4 C24 4,26 8,26 11 C26 13.5,24.5 15,23 15 L23 27" stroke="#60a5fa" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M9 15 L23 15" stroke="#60a5fa" stroke-width="2" stroke-linecap="round"/>
                <path d="M12 27 L20 27" stroke="#60a5fa" stroke-width="2.2" stroke-linecap="round"/>
            </svg>
            <span class="text-white font-bold text-sm tracking-wide">Poseidon</span>
        </a>
        <button onclick="toggleMobileMenu()" aria-label="Toggle menu"
                class="p-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-colors">
            <svg id="icon-menu" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <svg id="icon-close" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- ── Mobile menu (accordion) ─────────────────────────────────────── --}}
    <div id="mobile-menu" class="hidden md:hidden border-t border-gray-700/50 pb-2">
        @foreach($navItems as $item)
            @php $href = $item->resolvedUrl(); @endphp
            @if($item->children->isNotEmpty())
                <div>
                    <button onclick="mobToggle(this)"
                            class="w-full flex items-center justify-between px-6 py-3 text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-white transition-colors text-left">
                        {{ $item->label }}
                        <svg class="mob-chevron w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div class="hidden">
                        @foreach($item->children as $child)
                            @php $childHref = $child->resolvedUrl(); @endphp
                            @if($child->children->isNotEmpty())
                                <div>
                                    <button onclick="mobToggle(this)"
                                            class="w-full flex items-center justify-between pl-10 pr-6 py-2.5 text-sm text-gray-400 hover:bg-gray-800 hover:text-white transition-colors text-left">
                                        {{ $child->label }}
                                        <svg class="mob-chevron w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <div class="hidden">
                                        @foreach($child->children as $grandchild)
                                            @php $grandHref = $grandchild->resolvedUrl(); @endphp
                                            @if($grandchild->children->isNotEmpty())
                                                <div>
                                                    <button onclick="mobToggle(this)"
                                                            class="w-full flex items-center justify-between pl-16 pr-6 py-2 text-sm text-gray-500 hover:bg-gray-800 hover:text-white transition-colors text-left">
                                                        {{ $grandchild->label }}
                                                        <svg class="mob-chevron w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                        </svg>
                                                    </button>
                                                    <div class="hidden">
                                                        @foreach($grandchild->children as $great)
                                                            <a href="{{ $great->resolvedUrl() }}"
                                                               class="block pl-20 pr-6 py-2 text-sm text-gray-500 hover:bg-gray-800 hover:text-white transition-colors">
                                                                {{ $great->label }}
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @else
                                                <a href="{{ $grandHref }}"
                                                   class="block pl-16 pr-6 py-2 text-sm text-gray-500 hover:bg-gray-800 hover:text-white transition-colors">
                                                    {{ $grandchild->label }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <a href="{{ $childHref }}"
                                   class="block pl-10 pr-6 py-2.5 text-sm text-gray-400 hover:bg-gray-800 hover:text-white transition-colors">
                                    {{ $child->label }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @else
                <a href="{{ $href }}"
                   class="block px-6 py-3 text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                    {{ $item->label }}
                </a>
            @endif
        @endforeach
    </div>

</nav>
@endif

{!! $page->content !!}
{!! $page->body_section !!}

<script>
// ── Desktop dropdowns ────────────────────────────────────────────────────
function navToggle(btn, e) {
    e.stopPropagation();
    var panel = btn.nextElementSibling;
    var wasHidden = panel.classList.contains('hidden');
    document.querySelectorAll('.nav-dropdown, .nav-sub-dropdown').forEach(function(d) {
        if (!d.contains(btn)) d.classList.add('hidden');
    });
    if (wasHidden) panel.classList.remove('hidden');
}
document.addEventListener('click', function() {
    document.querySelectorAll('.nav-dropdown, .nav-sub-dropdown').forEach(function(d) {
        d.classList.add('hidden');
    });
});

// ── Mobile menu ──────────────────────────────────────────────────────────
function toggleMobileMenu() {
    var open = document.getElementById('mobile-menu').classList.toggle('hidden') === false;
    document.getElementById('icon-menu').classList.toggle('hidden', open);
    document.getElementById('icon-close').classList.toggle('hidden', !open);
}

function mobToggle(btn) {
    var panel   = btn.nextElementSibling;
    var chevron = btn.querySelector('.mob-chevron');
    var opening = panel.classList.toggle('hidden') === false;
    if (chevron) chevron.classList.toggle('open', opening);
}
</script>
</body>
</html>
