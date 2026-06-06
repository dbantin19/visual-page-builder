@extends('admin.layout')

@section('title', 'Footer')
@section('heading', 'Footer')

@section('content')
@php
    $oldCoupons = old('coupons');
    $couponRows = is_array($oldCoupons)
        ? array_values($oldCoupons)
        : $coupons->map(fn($coupon) => [
            'kicker' => $coupon->kicker,
            'headline' => $coupon->headline,
            'description' => $coupon->description,
            'fine_print' => $coupon->fine_print,
            'expires_enabled' => $coupon->expires_enabled,
            'expires_end_of_month' => $coupon->expires_end_of_month,
            'expires_at' => $coupon->expires_at?->format('Y-m-d'),
        ])->toArray();

    $oldOfficeLocations = old('office_locations');
    $officeLocationRows = is_array($oldOfficeLocations)
        ? array_values($oldOfficeLocations)
        : $officeLocations->map(fn($officeLocation) => [
            'name' => $officeLocation->name,
            'address_line_1' => $officeLocation->address_line_1,
            'address_line_2' => $officeLocation->address_line_2,
            'city' => $officeLocation->city,
            'region' => $officeLocation->region,
            'postal_code' => $officeLocation->postal_code,
            'phone' => $officeLocation->phone,
            'link_url' => $officeLocation->link_url,
        ])->toArray();

    $couponsEnabled = filter_var(old('coupons_enabled', $footerSetting->coupons_enabled), FILTER_VALIDATE_BOOLEAN);
    $locationEnabled = filter_var(old('location_enabled', $footerSetting->location_enabled), FILTER_VALIDATE_BOOLEAN);
    $currentMonthEndLabel = now()->endOfMonth()->format('F j, Y');
    $oldSectionOrder = old('section_order');
    $sectionOrderSource = is_array($oldSectionOrder) ? $oldSectionOrder : $footerSetting->normalizedSectionOrder();
    $sectionOrder = collect($sectionOrderSource)
        ->filter(fn($section) => is_string($section) && in_array($section, \App\Models\FooterSetting::DEFAULT_SECTION_ORDER, true))
        ->unique()
        ->merge(collect(\App\Models\FooterSetting::DEFAULT_SECTION_ORDER)->diff($sectionOrderSource))
        ->values()
        ->all();
    $oldSectionAlignments = old('section_alignments');
    $sectionAlignmentsSource = is_array($oldSectionAlignments) ? $oldSectionAlignments : $footerSetting->normalizedSectionAlignments();
    $sectionAlignments = collect(\App\Models\FooterSetting::DEFAULT_SECTION_ALIGNMENTS)
        ->map(function ($default, $section) use ($sectionAlignmentsSource) {
            $alignment = $sectionAlignmentsSource[$section] ?? $default;

            return in_array($alignment, \App\Models\FooterSetting::ALIGNMENTS, true) ? $alignment : $default;
        })
        ->all();
    $oldSectionContentAlignments = old('section_content_alignments');
    $sectionContentAlignmentsSource = is_array($oldSectionContentAlignments) ? $oldSectionContentAlignments : $footerSetting->normalizedSectionContentAlignments();
    $sectionContentAlignments = collect(\App\Models\FooterSetting::DEFAULT_SECTION_CONTENT_ALIGNMENTS)
        ->map(function ($default, $section) use ($sectionContentAlignmentsSource) {
            $alignment = $sectionContentAlignmentsSource[$section] ?? $default;

            return in_array($alignment, \App\Models\FooterSetting::ALIGNMENTS, true) ? $alignment : $default;
        })
        ->all();
    $alignmentOptions = [
        'left' => 'Left',
        'center' => 'Center',
        'right' => 'Right',
    ];
    $sectionMeta = [
        'main_location' => [
            'title' => 'Main location address',
            'description' => 'Primary address shown in the footer',
        ],
        'office_locations' => [
            'title' => 'Office locations',
            'description' => 'Additional addresses for multiple offices',
        ],
        'coupons' => [
            'title' => 'Coupons',
            'description' => 'Printable offers in the public footer',
        ],
    ];

    $errorKeys = $errors->getBag('default')->keys();
    $hasCouponErrors = collect($errorKeys)->contains(fn(string $key) => str_starts_with($key, 'coupons'));
    $hasLocationErrors = collect($errorKeys)->contains(fn(string $key) => str_starts_with($key, 'location_'));
    $hasOfficeErrors = collect($errorKeys)->contains(fn(string $key) => str_starts_with($key, 'office_locations'));

    $initialSection = 'coupons';
    if ($hasOfficeErrors) {
        $initialSection = 'office_locations';
    } elseif ($hasLocationErrors) {
        $initialSection = 'main_location';
    } elseif (! $couponsEnabled && $locationEnabled) {
        $initialSection = 'main_location';
    } elseif (! $couponsEnabled && ! $locationEnabled && count($officeLocationRows) > 0) {
        $initialSection = 'office_locations';
    }
@endphp

<form method="POST" action="{{ route('admin.footer.save') }}" class="max-w-6xl space-y-6">
    @csrf

    @if($errors->any())
        <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Footer content</h2>
            <p class="mt-1 text-sm text-gray-500">Choose a footer block to edit.</p>

            <div id="footer_section_order" class="mt-5 space-y-3">
                @foreach($sectionOrder as $section)
                    <div data-footer-section-item data-section="{{ $section }}" class="flex items-stretch gap-2">
                        <input type="hidden" data-section-order-input name="section_order[]" value="{{ $section }}">

                        <button type="button" data-footer-drag-handle draggable="true"
                                title="Drag {{ $sectionMeta[$section]['title'] }}" aria-label="Drag {{ $sectionMeta[$section]['title'] }}"
                                class="hidden sm:inline-flex w-10 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-400 cursor-grab active:cursor-grabbing">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h.01M8 12h.01M8 17h.01M16 7h.01M16 12h.01M16 17h.01"/>
                            </svg>
                        </button>

                        <button type="button" data-footer-section-button="{{ $section }}" onclick="setActiveFooterSection('{{ $section }}')">
                            <span class="flex items-start justify-between gap-4">
                                <span>
                                    <span class="block text-sm font-semibold text-gray-800">{{ $sectionMeta[$section]['title'] }}</span>
                                    <span class="mt-1 block text-xs text-gray-500">{{ $sectionMeta[$section]['description'] }}</span>
                                </span>
                                <span id="{{ $section }}_card_status" class="shrink-0 rounded-full px-2 py-1 text-xs font-semibold"></span>
                            </span>
                        </button>

                        <div class="flex shrink-0 flex-col gap-2">
                            <button type="button" data-move-direction="-1" title="Move {{ $sectionMeta[$section]['title'] }} up" aria-label="Move {{ $sectionMeta[$section]['title'] }} up"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:border-blue-200 hover:text-blue-700 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                                </svg>
                            </button>
                            <button type="button" data-move-direction="1" title="Move {{ $sectionMeta[$section]['title'] }} down" aria-label="Move {{ $sectionMeta[$section]['title'] }} down"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:border-blue-200 hover:text-blue-700 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div data-footer-section-panel="coupons">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Coupons</p>
                    <p class="mt-1 text-sm text-gray-500">Add, remove, and publish printable coupons.</p>
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <div>
                        <p class="mb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Block position</p>
                        <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1">
                            @foreach($alignmentOptions as $value => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="section_alignments[coupons]" value="{{ $value }}"
                                           data-alignment-field="coupons" class="sr-only peer" @checked($sectionAlignments['coupons'] === $value)>
                                    <span class="block rounded-md px-3 py-1.5 text-xs font-semibold text-gray-500 transition-colors peer-checked:bg-blue-700 peer-checked:text-white">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="mb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Content alignment</p>
                        <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1">
                            @foreach($alignmentOptions as $value => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="section_content_alignments[coupons]" value="{{ $value }}"
                                           data-content-alignment-field="coupons" class="sr-only peer" @checked($sectionContentAlignments['coupons'] === $value)>
                                    <span class="block rounded-md px-3 py-1.5 text-xs font-semibold text-gray-500 transition-colors peer-checked:bg-blue-700 peer-checked:text-white">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <input type="hidden" name="coupons_enabled" value="0">
                    <label for="coupons_enabled" class="inline-flex items-center gap-3 cursor-pointer select-none">
                        <input id="coupons_enabled" type="checkbox" name="coupons_enabled" value="1"
                               class="sr-only" onchange="toggleCoupons()" @checked($couponsEnabled)>
                        <span id="coupons_check_button"
                              class="w-9 h-9 rounded-lg border flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                        <span>
                            <span class="block text-sm font-semibold text-gray-800">Show coupons</span>
                            <span class="block text-xs text-gray-500">Visible on published pages</span>
                        </span>
                    </label>

                    <button type="button" onclick="addCoupon()"
                            class="inline-flex items-center gap-2 px-3 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add coupon
                    </button>
                </div>
            </div>

            <div id="coupon_rows" class="px-6">
                @foreach($couponRows as $index => $coupon)
                    @php
                        $expiryEnabled = filter_var($coupon['expires_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
                        $expiryEndOfMonth = filter_var($coupon['expires_end_of_month'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    @endphp
                    <div data-coupon-row class="py-5 border-b border-gray-100 last:border-b-0">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="coupon-number text-sm font-semibold text-gray-700">Coupon {{ $index + 1 }}</h3>
                            <button type="button" onclick="removeCoupon(this)" title="Remove coupon"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Small label</label>
                                <input type="text" data-field="kicker" name="coupons[{{ $index }}][kicker]"
                                       value="{{ $coupon['kicker'] ?? '' }}" placeholder="Limited time"
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Offer headline</label>
                                <input type="text" data-field="headline" name="coupons[{{ $index }}][headline]"
                                       value="{{ $coupon['headline'] ?? '' }}" placeholder="$25 OFF Any Repair"
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                                <input type="text" data-field="description" name="coupons[{{ $index }}][description]"
                                       value="{{ $coupon['description'] ?? '' }}" placeholder="Click to print this coupon"
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Fine print</label>
                                <input type="text" data-field="fine_print" name="coupons[{{ $index }}][fine_print]"
                                       value="{{ $coupon['fine_print'] ?? '' }}" placeholder="Not valid with other offers."
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>

                        <div class="mt-4 rounded-lg border border-gray-100 bg-gray-50 p-4">
                            <input type="hidden" data-field="expires_enabled" name="coupons[{{ $index }}][expires_enabled]" value="0">
                            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" data-field="expires_enabled" data-expiry-enabled
                                       name="coupons[{{ $index }}][expires_enabled]" value="1"
                                       onchange="toggleExpiry(this)"
                                       class="w-4 h-4 rounded border-gray-300 text-blue-700 focus:ring-blue-500"
                                       @checked($expiryEnabled)>
                                <span class="text-sm font-semibold text-gray-700">Add expiry date</span>
                            </label>

                            <div data-expiry-fields class="{{ $expiryEnabled ? '' : 'hidden' }} mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Custom expiry date</label>
                                    <input type="date" data-field="expires_at" data-expiry-date
                                           name="coupons[{{ $index }}][expires_at]"
                                           value="{{ $coupon['expires_at'] ?? '' }}"
                                           {{ $expiryEndOfMonth ? 'disabled' : '' }}
                                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-400">
                                </div>

                                <div class="flex items-center">
                                    <input type="hidden" data-field="expires_end_of_month" name="coupons[{{ $index }}][expires_end_of_month]" value="0">
                                    <label class="inline-flex items-start gap-3 cursor-pointer select-none">
                                        <input type="checkbox" data-field="expires_end_of_month" data-expiry-end-of-month
                                               name="coupons[{{ $index }}][expires_end_of_month]" value="1"
                                               onchange="toggleEndOfMonth(this)"
                                               class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-700 focus:ring-blue-500"
                                               @checked($expiryEndOfMonth)>
                                        <span>
                                            <span class="block text-sm font-semibold text-gray-700">Use end of current month</span>
                                            <span class="block text-xs text-gray-500">Right now this resolves to {{ $currentMonthEndLabel }} and updates each month.</span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="coupon_empty_state" class="hidden px-6 py-10 text-center border-t border-gray-100">
                <p class="text-sm text-gray-500">No coupons yet.</p>
            </div>
        </div>

        <div data-footer-section-panel="main_location">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Main location address</p>
                    <p class="mt-1 text-sm text-gray-500">Edit the primary address block.</p>
                </div>

                <input type="hidden" name="location_enabled" value="0">
                <div class="flex flex-wrap items-center gap-4">
                    <div>
                        <p class="mb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Block position</p>
                        <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1">
                            @foreach($alignmentOptions as $value => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="section_alignments[main_location]" value="{{ $value }}"
                                           data-alignment-field="main_location" class="sr-only peer" @checked($sectionAlignments['main_location'] === $value)>
                                    <span class="block rounded-md px-3 py-1.5 text-xs font-semibold text-gray-500 transition-colors peer-checked:bg-blue-700 peer-checked:text-white">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="mb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Content alignment</p>
                        <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1">
                            @foreach($alignmentOptions as $value => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="section_content_alignments[main_location]" value="{{ $value }}"
                                           data-content-alignment-field="main_location" class="sr-only peer" @checked($sectionContentAlignments['main_location'] === $value)>
                                    <span class="block rounded-md px-3 py-1.5 text-xs font-semibold text-gray-500 transition-colors peer-checked:bg-blue-700 peer-checked:text-white">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <label for="location_enabled" class="inline-flex items-center gap-3 cursor-pointer select-none">
                        <input id="location_enabled" type="checkbox" name="location_enabled" value="1"
                               class="sr-only" onchange="toggleLocation()" @checked($locationEnabled)>
                        <span id="location_check_button"
                              class="w-9 h-9 rounded-lg border flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                        <span>
                            <span class="block text-sm font-semibold text-gray-800">Show main address</span>
                            <span class="block text-xs text-gray-500">Visible on published pages</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="px-6 py-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Location name</label>
                        <input type="text" data-location-field name="location_name"
                               value="{{ old('location_name', $footerSetting->location_name) }}"
                               placeholder="Poseidon Garage Doors"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Street address</label>
                        <input type="text" data-location-field name="location_address_line_1"
                               value="{{ old('location_address_line_1', $footerSetting->location_address_line_1) }}"
                               placeholder="123 Ocean Avenue"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Address line 2</label>
                        <input type="text" data-location-field name="location_address_line_2"
                               value="{{ old('location_address_line_2', $footerSetting->location_address_line_2) }}"
                               placeholder="Suite 200"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Phone number</label>
                        <input type="text" data-location-field name="location_phone"
                               value="{{ old('location_phone', $footerSetting->location_phone) }}"
                               placeholder="(800) 000-0000"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">City</label>
                            <input type="text" data-location-field name="location_city"
                                   value="{{ old('location_city', $footerSetting->location_city) }}"
                                   placeholder="Miami"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">State</label>
                            <input type="text" data-location-field name="location_region"
                                   value="{{ old('location_region', $footerSetting->location_region) }}"
                                   placeholder="FL"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">ZIP</label>
                            <input type="text" data-location-field name="location_postal_code"
                                   value="{{ old('location_postal_code', $footerSetting->location_postal_code) }}"
                                   placeholder="33101"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div data-footer-section-panel="office_locations">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Office locations</p>
                    <p class="mt-1 text-sm text-gray-500">Add one row per office.</p>
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <div>
                        <p class="mb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Block position</p>
                        <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1">
                            @foreach($alignmentOptions as $value => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="section_alignments[office_locations]" value="{{ $value }}"
                                           data-alignment-field="office_locations" class="sr-only peer" @checked($sectionAlignments['office_locations'] === $value)>
                                    <span class="block rounded-md px-3 py-1.5 text-xs font-semibold text-gray-500 transition-colors peer-checked:bg-blue-700 peer-checked:text-white">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="mb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Content alignment</p>
                        <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1">
                            @foreach($alignmentOptions as $value => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="section_content_alignments[office_locations]" value="{{ $value }}"
                                           data-content-alignment-field="office_locations" class="sr-only peer" @checked($sectionContentAlignments['office_locations'] === $value)>
                                    <span class="block rounded-md px-3 py-1.5 text-xs font-semibold text-gray-500 transition-colors peer-checked:bg-blue-700 peer-checked:text-white">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <button type="button" onclick="addOfficeLocation()"
                            class="inline-flex items-center gap-2 px-3 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add office
                    </button>
                </div>
            </div>

            <div id="office_location_rows" class="px-6">
                @foreach($officeLocationRows as $index => $officeLocation)
                    <div data-office-location-row class="py-5 border-b border-gray-100 last:border-b-0">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="office-location-number text-sm font-semibold text-gray-700">Office {{ $index + 1 }}</h3>
                            <button type="button" onclick="removeOfficeLocation(this)" title="Remove office"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Office name</label>
                                <input type="text" data-office-field="name" name="office_locations[{{ $index }}][name]"
                                       value="{{ $officeLocation['name'] ?? '' }}" placeholder="North office"
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Street address</label>
                                <input type="text" data-office-field="address_line_1" name="office_locations[{{ $index }}][address_line_1]"
                                       value="{{ $officeLocation['address_line_1'] ?? '' }}" placeholder="456 Marina Road"
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Address line 2</label>
                                <input type="text" data-office-field="address_line_2" name="office_locations[{{ $index }}][address_line_2]"
                                       value="{{ $officeLocation['address_line_2'] ?? '' }}" placeholder="Suite 300"
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Phone number</label>
                                <input type="text" data-office-field="phone" name="office_locations[{{ $index }}][phone]"
                                       value="{{ $officeLocation['phone'] ?? '' }}" placeholder="(800) 000-0000"
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Office block link</label>
                                <input type="text" data-office-field="link_url" name="office_locations[{{ $index }}][link_url]"
                                       value="{{ $officeLocation['link_url'] ?? '' }}" placeholder="/north-office"
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">City</label>
                                    <input type="text" data-office-field="city" name="office_locations[{{ $index }}][city]"
                                           value="{{ $officeLocation['city'] ?? '' }}" placeholder="Orlando"
                                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">State</label>
                                    <input type="text" data-office-field="region" name="office_locations[{{ $index }}][region]"
                                           value="{{ $officeLocation['region'] ?? '' }}" placeholder="FL"
                                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">ZIP</label>
                                    <input type="text" data-office-field="postal_code" name="office_locations[{{ $index }}][postal_code]"
                                           value="{{ $officeLocation['postal_code'] ?? '' }}" placeholder="32801"
                                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="office_location_empty_state" class="hidden px-6 py-10 text-center border-t border-gray-100">
                <p class="text-sm text-gray-500">No office locations yet.</p>
            </div>
        </div>
    </div>

    <div id="footer_preview" class="{{ $couponsEnabled || $locationEnabled || count($officeLocationRows) > 0 ? '' : 'hidden' }}">
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10 flex items-center justify-between">
                <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Footer preview</h2>
                <span class="text-xs text-gray-500">Public footer content</span>
            </div>
            <div id="footer_preview_sections" class="px-6 py-8 space-y-8">
                <div id="preview_location" data-preview-section="main_location"></div>
                <div id="preview_office_locations" data-preview-section="office_locations"></div>
                <div id="preview_coupons" data-preview-section="coupons"></div>
            </div>
        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit"
                class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">
            Save footer
        </button>
    </div>
</form>

<template id="coupon_row_template">
    <div data-coupon-row class="py-5 border-b border-gray-100 last:border-b-0">
        <div class="flex items-center justify-between mb-4">
            <h3 class="coupon-number text-sm font-semibold text-gray-700">Coupon</h3>
            <button type="button" onclick="removeCoupon(this)" title="Remove coupon"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Small label</label>
                <input type="text" data-field="kicker" name="coupons[__INDEX__][kicker]" placeholder="Limited time"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Offer headline</label>
                <input type="text" data-field="headline" name="coupons[__INDEX__][headline]" placeholder="$25 OFF Any Repair"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                <input type="text" data-field="description" name="coupons[__INDEX__][description]" placeholder="Click to print this coupon"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Fine print</label>
                <input type="text" data-field="fine_print" name="coupons[__INDEX__][fine_print]" placeholder="Not valid with other offers."
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>

        <div class="mt-4 rounded-lg border border-gray-100 bg-gray-50 p-4">
            <input type="hidden" data-field="expires_enabled" name="coupons[__INDEX__][expires_enabled]" value="0">
            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" data-field="expires_enabled" data-expiry-enabled
                       name="coupons[__INDEX__][expires_enabled]" value="1"
                       onchange="toggleExpiry(this)"
                       class="w-4 h-4 rounded border-gray-300 text-blue-700 focus:ring-blue-500">
                <span class="text-sm font-semibold text-gray-700">Add expiry date</span>
            </label>

            <div data-expiry-fields class="hidden mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Custom expiry date</label>
                    <input type="date" data-field="expires_at" data-expiry-date
                           name="coupons[__INDEX__][expires_at]"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-400">
                </div>

                <div class="flex items-center">
                    <input type="hidden" data-field="expires_end_of_month" name="coupons[__INDEX__][expires_end_of_month]" value="0">
                    <label class="inline-flex items-start gap-3 cursor-pointer select-none">
                        <input type="checkbox" data-field="expires_end_of_month" data-expiry-end-of-month
                               name="coupons[__INDEX__][expires_end_of_month]" value="1"
                               onchange="toggleEndOfMonth(this)"
                               class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-700 focus:ring-blue-500">
                        <span>
                            <span class="block text-sm font-semibold text-gray-700">Use end of current month</span>
                            <span class="block text-xs text-gray-500">This updates each month automatically.</span>
                        </span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</template>

<template id="office_location_row_template">
    <div data-office-location-row class="py-5 border-b border-gray-100 last:border-b-0">
        <div class="flex items-center justify-between mb-4">
            <h3 class="office-location-number text-sm font-semibold text-gray-700">Office</h3>
            <button type="button" onclick="removeOfficeLocation(this)" title="Remove office"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Office name</label>
                <input type="text" data-office-field="name" name="office_locations[__INDEX__][name]" placeholder="North office"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Street address</label>
                <input type="text" data-office-field="address_line_1" name="office_locations[__INDEX__][address_line_1]" placeholder="456 Marina Road"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Address line 2</label>
                <input type="text" data-office-field="address_line_2" name="office_locations[__INDEX__][address_line_2]" placeholder="Suite 300"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Phone number</label>
                <input type="text" data-office-field="phone" name="office_locations[__INDEX__][phone]" placeholder="(800) 000-0000"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Office block link</label>
                <input type="text" data-office-field="link_url" name="office_locations[__INDEX__][link_url]" placeholder="/north-office"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">City</label>
                    <input type="text" data-office-field="city" name="office_locations[__INDEX__][city]" placeholder="Orlando"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">State</label>
                    <input type="text" data-office-field="region" name="office_locations[__INDEX__][region]" placeholder="FL"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">ZIP</label>
                    <input type="text" data-office-field="postal_code" name="office_locations[__INDEX__][postal_code]" placeholder="32801"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
        </div>
    </div>
</template>

<script>
var initialFooterSection = @json($initialSection);

function couponRows() {
    return Array.from(document.querySelectorAll('[data-coupon-row]'));
}

function officeLocationRows() {
    return Array.from(document.querySelectorAll('[data-office-location-row]'));
}

function footerSectionItems() {
    return Array.from(document.querySelectorAll('[data-footer-section-item]'));
}

function setActiveFooterSection(section) {
    document.querySelectorAll('[data-footer-section-panel]').forEach(function(panel) {
        panel.classList.toggle('hidden', panel.dataset.footerSectionPanel !== section);
    });

    document.querySelectorAll('[data-footer-section-button]').forEach(function(button) {
        var active = button.dataset.footerSectionButton === section;
        button.setAttribute('aria-pressed', active ? 'true' : 'false');
        button.className = 'group flex-1 w-full h-full text-left rounded-lg border p-4 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 ' +
            (active
                ? 'border-blue-700 bg-blue-50 shadow-sm'
                : 'border-gray-200 bg-white hover:border-blue-200 hover:bg-blue-50/40');
    });
}

function moveFooterSection(button, direction) {
    var item = button.closest('[data-footer-section-item]');
    if (!item) return;

    if (direction < 0 && item.previousElementSibling) {
        item.parentElement.insertBefore(item, item.previousElementSibling);
    }

    if (direction > 0 && item.nextElementSibling) {
        item.parentElement.insertBefore(item.nextElementSibling, item);
    }

    syncFooterSectionOrder();
    updateFooterPreview();
}

function syncFooterSectionOrder() {
    var items = footerSectionItems();
    var previewContainer = document.getElementById('footer_preview_sections');

    items.forEach(function(item, index) {
        var input = item.querySelector('[data-section-order-input]');
        if (input) input.value = item.dataset.section;

        var up = item.querySelector('[data-move-direction="-1"]');
        var down = item.querySelector('[data-move-direction="1"]');
        if (up) up.disabled = index === 0;
        if (down) down.disabled = index === items.length - 1;

        if (previewContainer) {
            var preview = previewContainer.querySelector('[data-preview-section="' + item.dataset.section + '"]');
            if (preview) previewContainer.appendChild(preview);
        }
    });
}

function getFooterSectionAfterElement(container, y) {
    return footerSectionItems()
        .filter(function(item) {
            return item.parentElement === container && !item.classList.contains('opacity-50');
        })
        .reduce(function(closest, child) {
            var box = child.getBoundingClientRect();
            var offset = y - box.top - box.height / 2;

            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            }

            return closest;
        }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
}

function setCheckButtonState(id, enabled) {
    document.getElementById(id).className = enabled
        ? 'w-9 h-9 rounded-lg border flex items-center justify-center transition-colors bg-blue-700 border-blue-700 text-white'
        : 'w-9 h-9 rounded-lg border flex items-center justify-center transition-colors bg-white border-gray-300 text-transparent';
}

function updateStatusChip(id, text, enabled) {
    var chip = document.getElementById(id);
    chip.textContent = text;
    chip.className = enabled
        ? 'shrink-0 rounded-full px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800'
        : 'shrink-0 rounded-full px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-500';
}

function couponHasContent(row) {
    return ['kicker', 'headline', 'description', 'fine_print'].some(function(field) {
        var input = row.querySelector('[data-field="' + field + '"]');
        return input && input.value.trim() !== '';
    }) || (row.querySelector('[data-expiry-enabled]') && row.querySelector('[data-expiry-enabled]').checked);
}

function officeLocationHasContent(row) {
    return Array.from(row.querySelectorAll('[data-office-field]')).some(function(input) {
        return input.value.trim() !== '';
    });
}

function locationHasContent() {
    return [
        'location_name',
        'location_address_line_1',
        'location_address_line_2',
        'location_city',
        'location_region',
        'location_postal_code',
        'location_phone'
    ].some(function(name) {
        return readLocationValue(name) !== '';
    });
}

function footerPreviewEnabled() {
    return document.getElementById('location_enabled').checked ||
        document.getElementById('coupons_enabled').checked ||
        officeLocationRows().some(officeLocationHasContent);
}

function syncFooterPreviewVisibility() {
    document.getElementById('footer_preview').classList.toggle('hidden', !footerPreviewEnabled());
}

function syncSectionStatuses() {
    var couponCount = couponRows().filter(couponHasContent).length;
    var couponsEnabled = document.getElementById('coupons_enabled').checked;
    updateStatusChip('coupons_card_status', couponsEnabled ? 'Visible' : 'Hidden', couponsEnabled);

    var locationEnabled = document.getElementById('location_enabled').checked;
    updateStatusChip('main_location_card_status', locationEnabled ? 'Visible' : 'Hidden', locationEnabled);

    var officeCount = officeLocationRows().filter(officeLocationHasContent).length;
    updateStatusChip(
        'office_locations_card_status',
        officeCount === 0 ? 'Empty' : officeCount + (officeCount === 1 ? ' office' : ' offices'),
        officeCount > 0
    );
}

function toggleLocation() {
    var enabled = document.getElementById('location_enabled').checked;
    setCheckButtonState('location_check_button', enabled);
    syncSectionStatuses();
    syncFooterPreviewVisibility();
    updateFooterPreview();
}

function toggleCoupons() {
    var enabled = document.getElementById('coupons_enabled').checked;
    setCheckButtonState('coupons_check_button', enabled);

    if (enabled && couponRows().length === 0) {
        addCoupon();
        return;
    }

    syncCouponState();
}

function addCoupon() {
    var rows = document.getElementById('coupon_rows');
    var index = couponRows().length;
    var html = document.getElementById('coupon_row_template').innerHTML.replaceAll('__INDEX__', index);
    var wrapper = document.createElement('div');
    wrapper.innerHTML = html.trim();
    rows.appendChild(wrapper.firstElementChild);
    reindexCoupons();
    setActiveFooterSection('coupons');
    syncCouponState();
}

function removeCoupon(button) {
    button.closest('[data-coupon-row]').remove();
    reindexCoupons();
    syncCouponState();
}

function addOfficeLocation() {
    var rows = document.getElementById('office_location_rows');
    var index = officeLocationRows().length;
    var html = document.getElementById('office_location_row_template').innerHTML.replaceAll('__INDEX__', index);
    var wrapper = document.createElement('div');
    wrapper.innerHTML = html.trim();
    rows.appendChild(wrapper.firstElementChild);
    reindexOfficeLocations();
    setActiveFooterSection('office_locations');
    syncOfficeLocationState();
}

function removeOfficeLocation(button) {
    button.closest('[data-office-location-row]').remove();
    reindexOfficeLocations();
    syncOfficeLocationState();
}

function toggleExpiry(control) {
    syncExpiryRow(control.closest('[data-coupon-row]'));
    syncCouponState();
}

function toggleEndOfMonth(control) {
    syncExpiryRow(control.closest('[data-coupon-row]'));
    syncCouponState();
}

function reindexCoupons() {
    couponRows().forEach(function(row, index) {
        var title = row.querySelector('.coupon-number');
        if (title) title.textContent = 'Coupon ' + (index + 1);
        row.querySelectorAll('[data-field]').forEach(function(input) {
            input.name = 'coupons[' + index + '][' + input.dataset.field + ']';
        });
    });
}

function reindexOfficeLocations() {
    officeLocationRows().forEach(function(row, index) {
        var title = row.querySelector('.office-location-number');
        if (title) title.textContent = 'Office ' + (index + 1);
        row.querySelectorAll('[data-office-field]').forEach(function(input) {
            input.name = 'office_locations[' + index + '][' + input.dataset.officeField + ']';
        });
    });
}

function syncCouponState() {
    var rows = couponRows();
    document.getElementById('coupon_empty_state').classList.toggle('hidden', rows.length > 0);
    rows.forEach(syncExpiryRow);
    syncSectionStatuses();
    syncFooterPreviewVisibility();
    updateFooterPreview();
}

function syncOfficeLocationState() {
    var rows = officeLocationRows();
    document.getElementById('office_location_empty_state').classList.toggle('hidden', rows.length > 0);
    syncSectionStatuses();
    syncFooterPreviewVisibility();
    updateFooterPreview();
}

function syncExpiryRow(row) {
    if (!row) return;

    var enabled = row.querySelector('[data-expiry-enabled]');
    var endOfMonth = row.querySelector('[data-expiry-end-of-month]');
    var fields = row.querySelector('[data-expiry-fields]');
    var date = row.querySelector('[data-expiry-date]');

    if (!enabled || !endOfMonth || !fields || !date) return;

    fields.classList.toggle('hidden', !enabled.checked);
    date.disabled = enabled.checked && endOfMonth.checked;
    date.required = enabled.checked && !endOfMonth.checked;
}

function escapeFooterText(value) {
    return String(value || '').replace(/[&<>"']/g, function(char) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[char];
    });
}

function formatCouponDate(value) {
    if (!value) return '';

    var parts = value.split('-').map(Number);
    if (parts.length !== 3 || parts.some(isNaN)) return '';

    return new Intl.DateTimeFormat('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric'
    }).format(new Date(parts[0], parts[1] - 1, parts[2]));
}

function endOfCurrentMonthLabel() {
    var now = new Date();
    var endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0);

    return new Intl.DateTimeFormat('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric'
    }).format(endOfMonth);
}

function expiryLabel(coupon) {
    if (!coupon.expiresEnabled) return '';

    if (coupon.expiresEndOfMonth) {
        return endOfCurrentMonthLabel();
    }

    return formatCouponDate(coupon.expiresAt) || 'Choose expiry date';
}

function readLocationValue(name) {
    var field = document.querySelector('[name="' + name + '"]');
    return field ? field.value.trim() : '';
}

function phoneHref(value) {
    var phone = String(value || '').trim();
    if (!phone) return '';

    var prefix = phone.startsWith('+') ? '+' : '';
    var digits = phone.replace(/\D+/g, '');

    return digits ? 'tel:' + prefix + digits : '';
}

function officeLinkHref(value) {
    var link = String(value || '').trim();

    return link && !/^\s*javascript:/i.test(link) ? link : '';
}

function sectionAlignment(section) {
    var field = document.querySelector('[name="section_alignments[' + section + ']"]:checked');
    return field ? field.value : 'left';
}

function sectionContentAlignment(section) {
    var field = document.querySelector('[name="section_content_alignments[' + section + ']"]:checked');
    return field ? field.value : 'left';
}

function previewBlockAlignmentClasses(section) {
    var alignment = sectionAlignment(section);

    return {
        block: alignment === 'center' ? 'max-w-xl mx-auto' : (alignment === 'right' ? 'max-w-xl ml-auto' : 'max-w-xl'),
        group: alignment === 'center' ? 'justify-center' : (alignment === 'right' ? 'justify-end' : 'justify-start')
    };
}

function previewContentAlignmentClasses(section) {
    var alignment = sectionContentAlignment(section);

    return {
        text: alignment === 'center' ? 'text-center' : (alignment === 'right' ? 'text-right' : 'text-left'),
        items: alignment === 'center' ? 'items-center' : (alignment === 'right' ? 'items-end' : 'items-start')
    };
}

function officeLocationData(row) {
    var data = {};
    row.querySelectorAll('[data-office-field]').forEach(function(input) {
        data[input.dataset.officeField] = input.value.trim();
    });

    var cityRegion = [data.city, data.region].filter(Boolean).join(', ');
    data.cityLine = [cityRegion, data.postal_code].filter(Boolean).join(' ');
    data.phoneHref = phoneHref(data.phone);
    data.linkHref = officeLinkHref(data.link_url);

    return data;
}

function updateLocationPreview() {
    var preview = document.getElementById('preview_location');
    var enabled = document.getElementById('location_enabled').checked;
    var blockAlignment = previewBlockAlignmentClasses('main_location');
    var contentAlignment = previewContentAlignmentClasses('main_location');

    if (!enabled) {
        preview.innerHTML = '';
        return;
    }

    var name = readLocationValue('location_name');
    var line1 = readLocationValue('location_address_line_1');
    var line2 = readLocationValue('location_address_line_2');
    var cityRegion = [readLocationValue('location_city'), readLocationValue('location_region')]
        .filter(Boolean)
        .join(', ');
    var cityLine = [cityRegion, readLocationValue('location_postal_code')]
        .filter(Boolean)
        .join(' ');
    var phone = readLocationValue('location_phone');
    var locationPhoneHref = phoneHref(phone);

    if (!line1 && !line2 && !cityLine && !name && !phone) {
        preview.innerHTML = '<div class="text-sm text-gray-500">Add an address to preview the main location.</div>';
        return;
    }

    preview.innerHTML =
        '<div class="' + blockAlignment.block + ' ' + contentAlignment.text + '">' +
            '<p class="text-xs font-semibold uppercase tracking-wider text-blue-300 mb-2">Main location</p>' +
            (name ? '<p class="text-lg font-bold text-white">' + escapeFooterText(name) + '</p>' : '') +
            '<address class="mt-2 not-italic text-sm leading-6 text-gray-300">' +
                (line1 ? '<span class="block">' + escapeFooterText(line1) + '</span>' : '') +
                (line2 ? '<span class="block">' + escapeFooterText(line2) + '</span>' : '') +
                (cityLine ? '<span class="block">' + escapeFooterText(cityLine) + '</span>' : '') +
            '</address>' +
            (locationPhoneHref ? '<a href="' + escapeFooterText(locationPhoneHref) + '" class="mt-2 inline-block text-sm font-semibold text-blue-200 hover:text-white">' + escapeFooterText(phone) + '</a>' : '') +
        '</div>';
}

function updateOfficeLocationsPreview() {
    var preview = document.getElementById('preview_office_locations');
    var blockAlignment = previewBlockAlignmentClasses('office_locations');
    var contentAlignment = previewContentAlignmentClasses('office_locations');
    var locations = officeLocationRows()
        .map(officeLocationData)
        .filter(function(location) {
            return location.name || location.address_line_1 || location.address_line_2 || location.cityLine || location.phone || location.link_url;
        });

    if (locations.length === 0) {
        preview.innerHTML = '';
        return;
    }

    preview.innerHTML =
        '<div class="' + contentAlignment.text + '">' +
            '<p class="text-xs font-semibold uppercase tracking-wider text-blue-300 mb-4">Office locations</p>' +
            '<div class="flex flex-wrap gap-4 ' + blockAlignment.group + '">' +
                locations.map(function(location) {
                    var cardTag = location.linkHref ? 'a' : 'div';
                    var hrefAttribute = location.linkHref ? ' href="' + escapeFooterText(location.linkHref) + '"' : '';

                    return '<' + cardTag + hrefAttribute + ' class="block w-full max-w-sm rounded-lg border border-white/10 bg-white/5 p-5 transition-colors hover:bg-white/10 ' + contentAlignment.text + '">' +
                        (location.name ? '<p class="text-sm font-semibold text-blue-200">' + escapeFooterText(location.name) + '</p>' : '') +
                        '<address class="' + (location.name ? 'mt-3 ' : '') + 'not-italic text-sm leading-6 text-gray-300">' +
                            (location.address_line_1 ? '<span class="block">' + escapeFooterText(location.address_line_1) + '</span>' : '') +
                            (location.address_line_2 ? '<span class="block">' + escapeFooterText(location.address_line_2) + '</span>' : '') +
                            (location.cityLine ? '<span class="block">' + escapeFooterText(location.cityLine) + '</span>' : '') +
                        '</address>' +
                        (location.phoneHref
                            ? (location.linkHref
                                ? '<span class="mt-3 inline-block text-sm font-semibold text-blue-200">' + escapeFooterText(location.phone) + '</span>'
                                : '<a href="' + escapeFooterText(location.phoneHref) + '" class="mt-3 inline-block text-sm font-semibold text-blue-200 hover:text-white">' + escapeFooterText(location.phone) + '</a>')
                            : '') +
                    '</' + cardTag + '>';
                }).join('') +
            '</div>' +
        '</div>';
}

function updateCouponsPreview() {
    var preview = document.getElementById('preview_coupons');
    var blockAlignment = previewBlockAlignmentClasses('coupons');
    var contentAlignment = previewContentAlignmentClasses('coupons');
    if (!document.getElementById('coupons_enabled').checked) {
        preview.innerHTML = '';
        return;
    }

    var coupons = couponRows().map(function(row) {
        return {
            kicker: row.querySelector('[data-field="kicker"]').value.trim(),
            headline: row.querySelector('[data-field="headline"]').value.trim(),
            description: row.querySelector('[data-field="description"]').value.trim(),
            finePrint: row.querySelector('[data-field="fine_print"]').value.trim(),
            expiresEnabled: row.querySelector('[data-expiry-enabled]').checked,
            expiresEndOfMonth: row.querySelector('[data-expiry-end-of-month]').checked,
            expiresAt: row.querySelector('[data-expiry-date]').value
        };
    }).filter(function(coupon) {
        return coupon.kicker || coupon.headline || coupon.description || coupon.finePrint || coupon.expiresEnabled;
    });

    if (coupons.length === 0) {
        preview.innerHTML = '<div class="text-center text-sm text-gray-500 py-6">Add a coupon to preview the footer.</div>';
        return;
    }

    preview.innerHTML =
        '<div class="' + contentAlignment.text + '">' +
            '<p class="text-xs font-semibold uppercase tracking-wider text-blue-300 mb-4">Printable offers</p>' +
            '<div class="flex flex-wrap gap-4 ' + blockAlignment.group + '">' +
                coupons.map(function(coupon) {
                    var expires = expiryLabel(coupon);

                    return '<div class="w-full max-w-sm bg-white/5 border border-white/10 rounded-xl p-3 text-left">' +
                        '<div class="border-2 border-dashed border-white/25 rounded-lg p-5 flex flex-col justify-center ' + contentAlignment.items + ' ' + contentAlignment.text + '">' +
                        (coupon.kicker ? '<p class="text-xs font-semibold uppercase tracking-wider text-blue-200 mb-2">' + escapeFooterText(coupon.kicker) + '</p>' : '') +
                        '<p class="text-2xl font-black text-white leading-tight">' + escapeFooterText(coupon.headline || 'Coupon offer') + '</p>' +
                        (coupon.description ? '<p class="mt-3 text-sm text-gray-300">' + escapeFooterText(coupon.description) + '</p>' : '') +
                        (expires ? '<p class="mt-3 text-xs font-semibold uppercase tracking-wider text-amber-200">Expires ' + escapeFooterText(expires) + '</p>' : '') +
                        (coupon.finePrint ? '<p class="mt-4 pt-4 w-full border-t border-dashed border-white/20 text-xs text-gray-500">' + escapeFooterText(coupon.finePrint) + '</p>' : '') +
                        '</div>' +
                        '</div>';
                }).join('') +
            '</div>' +
        '</div>';
}

function updateFooterPreview() {
    updateLocationPreview();
    updateOfficeLocationsPreview();
    updateCouponsPreview();
}

document.addEventListener('input', function(event) {
    if (event.target.matches('[data-field], [data-location-field], [data-office-field], [data-alignment-field], [data-content-alignment-field]')) {
        syncSectionStatuses();
        syncFooterPreviewVisibility();
        updateFooterPreview();
    }
});

document.addEventListener('change', function(event) {
    if (event.target.matches('[data-field]')) {
        syncCouponState();
    }

    if (event.target.matches('[data-location-field]')) {
        toggleLocation();
    }

    if (event.target.matches('[data-office-field]')) {
        syncOfficeLocationState();
    }

    if (event.target.matches('[data-alignment-field], [data-content-alignment-field]')) {
        updateFooterPreview();
    }
});

var draggedFooterSectionItem = null;

var footerSectionMovePointerHandledAt = 0;

function handleFooterSectionMoveEvent(event) {
    var moveButton = event.target.closest('[data-move-direction]');
    if (!moveButton || !moveButton.closest('[data-footer-section-item]') || moveButton.disabled) return;

    event.preventDefault();
    event.stopPropagation();

    if (event.type === 'click' && Date.now() - footerSectionMovePointerHandledAt < 400) {
        return;
    }

    if (event.type !== 'click') {
        footerSectionMovePointerHandledAt = Date.now();
    }

    moveFooterSection(moveButton, Number(moveButton.dataset.moveDirection));
}

document.addEventListener('pointerdown', handleFooterSectionMoveEvent, true);
document.addEventListener('click', handleFooterSectionMoveEvent, true);

document.addEventListener('keydown', function(event) {
    if (event.key !== 'Enter' && event.key !== ' ') return;

    handleFooterSectionMoveEvent(event);
}, true);

document.addEventListener('dragstart', function(event) {
    var handle = event.target.closest('[data-footer-drag-handle]');
    if (!handle) return;

    var item = handle.closest('[data-footer-section-item]');
    if (!item) return;

    draggedFooterSectionItem = item;
    item.classList.add('opacity-50');

    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', item.dataset.section);
    }
});

document.addEventListener('dragover', function(event) {
    var container = event.target.closest('#footer_section_order');
    if (!container || !draggedFooterSectionItem) return;

    event.preventDefault();
    var afterElement = getFooterSectionAfterElement(container, event.clientY);

    if (afterElement) {
        container.insertBefore(draggedFooterSectionItem, afterElement);
    } else {
        container.appendChild(draggedFooterSectionItem);
    }
});

document.addEventListener('dragend', function() {
    if (!draggedFooterSectionItem) return;

    draggedFooterSectionItem.classList.remove('opacity-50');
    draggedFooterSectionItem = null;
    syncFooterSectionOrder();
    updateFooterPreview();
});

syncFooterSectionOrder();
setActiveFooterSection(initialFooterSection);
reindexCoupons();
reindexOfficeLocations();
toggleLocation();
toggleCoupons();
syncOfficeLocationState();
</script>
@endsection
