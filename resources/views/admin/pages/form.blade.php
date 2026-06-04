@extends('admin.layout')

@section('title', $page ? 'Edit Page' : 'New Page')
@section('heading', $page ? 'Edit Page' : 'New Page')

@section('header-actions')
    <div class="flex items-center gap-3">
        @if($page)
            <a href="{{ route('admin.pages.builder', $page) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                </svg>
                Page Builder
            </a>
        @endif
        <button type="button" id="use-tmpl-btn"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 hover:border-gray-400 text-gray-700 text-sm font-medium rounded-lg transition-colors {{ $templates->isEmpty() ? 'opacity-40 cursor-not-allowed' : '' }}"
                {{ $templates->isEmpty() ? 'disabled' : '' }}
                title="{{ $templates->isEmpty() ? 'No templates saved yet' : 'Start from a saved template' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
            </svg>
            Use from Template
        </button>
        <a href="{{ route('admin.pages.index') }}" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
            &larr; Back to Pages
        </a>
    </div>
@endsection

@section('content')
<form method="POST"
      action="{{ $page ? route('admin.pages.update', $page) : route('admin.pages.store') }}"
      class="max-w-3xl space-y-6">
    @csrf
    @if($page) @method('PUT') @endif

    <input type="hidden" name="template_id" id="template_id" value="">

    {{-- Basic Info --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Page Info</h2>

        <div class="grid grid-cols-2 gap-5">
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Page Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $page?->name) }}"
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                              {{ $errors->has('name') ? 'border-red-400' : 'border-gray-300' }}">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug <span class="text-red-500">*</span></label>
                <div class="flex items-center border rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500
                            {{ $errors->has('slug') ? 'border-red-400' : 'border-gray-300' }}">
                    <span class="px-3 py-2 bg-gray-50 text-gray-400 text-sm border-r border-gray-300">/</span>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $page?->slug) }}"
                           class="flex-1 px-3 py-2 text-sm focus:outline-none font-mono">
                </div>
                @error('slug') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex gap-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_published" value="0">
                <input type="checkbox" name="is_published" value="1" class="rounded border-gray-300 text-blue-600"
                       {{ old('is_published', $page?->is_published) ? 'checked' : '' }}>
                <span class="text-sm text-gray-700">Published</span>
            </label>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_indexed" value="0">
                <input type="checkbox" name="is_indexed" value="1" class="rounded border-gray-300 text-blue-600"
                       {{ old('is_indexed', $page === null ? true : $page->is_indexed) ? 'checked' : '' }}>
                <span class="text-sm text-gray-700">Allow search engines to index</span>
            </label>
        </div>
    </div>

    {{-- SEO --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">SEO</h2>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
            <input type="text" name="meta_title" value="{{ old('meta_title', $page?->meta_title) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
            <textarea name="meta_description" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('meta_description', $page?->meta_description) }}</textarea>
        </div>
    </div>

    {{-- Head Section --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-3">
        <div>
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">&lt;/head&gt; Section</h2>
            <p class="text-xs text-gray-400 mt-0.5">CSS or scripts injected before &lt;/head&gt;</p>
        </div>
        <textarea name="head_section" rows="6" spellcheck="false"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y">{{ old('head_section', $page?->head_section) }}</textarea>
    </div>

    {{-- Body Section --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-3">
        <div>
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">&lt;/body&gt; Section</h2>
            <p class="text-xs text-gray-400 mt-0.5">Scripts injected before &lt;/body&gt;</p>
        </div>
        <textarea name="body_section" rows="6" spellcheck="false"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y">{{ old('body_section', $page?->body_section) }}</textarea>
    </div>

    <div class="flex items-center gap-4 flex-wrap">
        <button type="submit"
                class="px-5 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors">
            {{ $page ? 'Update Page' : 'Create Page' }}
        </button>
        <a href="{{ route('admin.pages.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
        @if(!$page)
        <span id="tmpl-badge" class="hidden items-center gap-1.5 px-3 py-1.5 bg-blue-50 border border-blue-200 text-blue-700 text-xs font-medium rounded-lg">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
            </svg>
            Template: <span id="tmpl-badge-name" class="font-semibold"></span>
            <button type="button" id="tmpl-badge-clear" class="ml-1 text-blue-400 hover:text-blue-700 leading-none">&times;</button>
        </span>
        @endif
    </div>
</form>

{{-- ─── USE FROM TEMPLATE MODAL ─── --}}
<div id="form-tmpl-overlay"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;width:460px;max-height:540px;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,.2);overflow:hidden;">

        {{-- Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 20px;border-bottom:1px solid #e5e7eb;flex-shrink:0;">
            <h2 style="font-size:15px;font-weight:600;color:#111827;margin:0;">Use from Template</h2>
            <button id="form-tmpl-close" style="background:transparent;border:none;cursor:pointer;color:#9ca3af;padding:4px;border-radius:4px;display:flex;align-items:center;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Select-all row --}}
        @if($templates->isNotEmpty())
        <div style="display:flex;align-items:center;gap:10px;padding:9px 14px 9px 18px;border-bottom:1px solid #f3f4f6;background:#f9fafb;flex-shrink:0;">
            <input type="checkbox" id="form-tmpl-select-all" title="Select all"
                   style="width:15px;height:15px;cursor:pointer;flex-shrink:0;accent-color:#ef4444;">
            <span style="font-size:12px;font-weight:500;color:#6b7280;flex:1;">Select all</span>
            <button type="button" id="form-tmpl-delete-btn" disabled
                    style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:6px;border:1px solid #fca5a5;background:#fff5f5;color:#dc2626;font-size:12px;font-weight:600;cursor:pointer;opacity:.4;transition:opacity .12s;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span id="form-tmpl-delete-label">Delete</span>
            </button>
        </div>
        @endif

        {{-- Template list --}}
        <div id="form-tmpl-list" style="flex:1;overflow-y:auto;padding:6px 8px;">
            @forelse($templates as $tpl)
            <div class="form-tmpl-row" data-id="{{ $tpl->id }}" data-name="{{ $tpl->name }}"
                 style="display:flex;align-items:center;gap:6px;padding:3px 6px 3px 10px;border-radius:9px;">
                <input type="checkbox" class="form-tmpl-checkbox" data-id="{{ $tpl->id }}"
                       style="width:15px;height:15px;cursor:pointer;flex-shrink:0;accent-color:#ef4444;">
                <button type="button" class="form-tmpl-item" data-id="{{ $tpl->id }}" data-name="{{ $tpl->name }}"
                        style="flex:1;display:flex;align-items:center;gap:11px;padding:8px 10px;border-radius:8px;border:1.5px solid transparent;background:transparent;cursor:pointer;text-align:left;transition:all .12s;">
                    <div style="width:30px;height:30px;background:#eff6ff;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="15" height="15" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                        </svg>
                    </div>
                    <span style="font-size:14px;font-weight:500;color:#374151;flex:1;">{{ $tpl->name }}</span>
                    <svg class="form-tmpl-use-check" width="15" height="15" fill="none" stroke="#3b82f6" stroke-width="2.5" viewBox="0 0 24 24"
                         style="display:none;flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
            </div>
            @empty
            <p style="text-align:center;color:#9ca3af;font-size:13px;padding:32px 0;">No templates saved yet.</p>
            @endforelse
        </div>

        {{-- Footer: normal state --}}
        <div id="form-tmpl-footer-normal" style="padding:14px 20px;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end;gap:8px;flex-shrink:0;">
            <button type="button" id="form-tmpl-cancel"
                    style="padding:8px 16px;border-radius:7px;border:1px solid #d1d5db;background:#fff;color:#374151;font-size:13px;font-weight:500;cursor:pointer;">
                Cancel
            </button>
            @if($page)
            <form method="POST" action="{{ route('admin.pages.use-template', $page) }}" id="form-tmpl-apply-form">
                @csrf
                <input type="hidden" name="template_id" id="form-tmpl-apply-id">
                <button type="submit" id="form-tmpl-apply" disabled
                        style="padding:8px 18px;border-radius:7px;border:none;background:#3b82f6;color:#fff;font-size:13px;font-weight:600;cursor:pointer;opacity:.4;">
                    Apply Template
                </button>
            </form>
            @else
            <button type="button" id="form-tmpl-apply" disabled
                    style="padding:8px 18px;border-radius:7px;border:none;background:#3b82f6;color:#fff;font-size:13px;font-weight:600;cursor:pointer;opacity:.4;">
                Use Template
            </button>
            @endif
        </div>

        {{-- Footer: delete confirmation state (hidden by default) --}}
        <div id="form-tmpl-footer-confirm" style="display:none;padding:14px 20px;border-top:1px solid #fee2e2;background:#fff5f5;gap:12px;align-items:center;flex-shrink:0;">
            <svg width="16" height="16" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <span id="form-tmpl-confirm-msg" style="font-size:13px;color:#b91c1c;font-weight:500;flex:1;"></span>
            <button type="button" id="form-tmpl-confirm-cancel"
                    style="padding:7px 14px;border-radius:7px;border:1px solid #d1d5db;background:#fff;color:#374151;font-size:13px;font-weight:500;cursor:pointer;white-space:nowrap;">
                Go back
            </button>
            <button type="button" id="form-tmpl-confirm-delete"
                    style="padding:7px 16px;border-radius:7px;border:none;background:#dc2626;color:#fff;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;">
                Yes, Delete
            </button>
        </div>

    </div>
</div>

<script>
    @if(!$page)
    document.getElementById('name').addEventListener('input', function () {
        document.getElementById('slug').value = this.value
            .toLowerCase().trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    });
    @endif

    // ─── Template modal state
    const FORM_CSRF        = '{{ csrf_token() }}';
    const DELETE_BASE      = '{{ url('admin/templates') }}';
    const formTmplOverlay  = document.getElementById('form-tmpl-overlay');
    const formTmplApplyBtn = document.getElementById('form-tmpl-apply');
    const footerNormal     = document.getElementById('form-tmpl-footer-normal');
    const footerConfirm    = document.getElementById('form-tmpl-footer-confirm');
    let selectedTmplId   = null;
    let selectedTmplName = null;

    // ─── Open / close
    function openFormTmplModal() {
        selectedTmplId = null;
        selectedTmplName = null;
        showNormalFooter();
        resetUseSelection();
        uncheckAll();
        updateDeleteBtn();
        formTmplOverlay.style.display = 'flex';
    }
    function closeFormTmplModal() {
        formTmplOverlay.style.display = 'none';
        showNormalFooter();
    }

    const useTmplBtn = document.getElementById('use-tmpl-btn');
    if (useTmplBtn) useTmplBtn.addEventListener('click', openFormTmplModal);
    document.getElementById('form-tmpl-close').addEventListener('click', closeFormTmplModal);
    document.getElementById('form-tmpl-cancel').addEventListener('click', closeFormTmplModal);
    formTmplOverlay.addEventListener('click', e => { if (e.target === formTmplOverlay) closeFormTmplModal(); });

    // ─── Row: click item button to select for use
    function resetUseSelection() {
        document.querySelectorAll('.form-tmpl-item').forEach(i => {
            i.style.background   = 'transparent';
            i.style.borderColor  = 'transparent';
            i.querySelector('.form-tmpl-use-check').style.display = 'none';
        });
        if (formTmplApplyBtn) {
            formTmplApplyBtn.disabled      = true;
            formTmplApplyBtn.style.opacity = '.4';
        }
    }

    document.querySelectorAll('.form-tmpl-item').forEach(item => {
        item.addEventListener('click', () => {
            resetUseSelection();
            item.style.background  = '#eff6ff';
            item.style.borderColor = '#93c5fd';
            item.querySelector('.form-tmpl-use-check').style.display = 'block';
            selectedTmplId   = item.dataset.id;
            selectedTmplName = item.dataset.name;
            if (formTmplApplyBtn) {
                formTmplApplyBtn.disabled      = false;
                formTmplApplyBtn.style.opacity = '1';
            }
        });
    });

    // ─── Apply / Use button
    @if($page)
    if (formTmplApplyBtn) {
        formTmplApplyBtn.addEventListener('click', () => {
            if (!selectedTmplId) return;
            if (!confirm('This will replace the current page builder content with the selected template. Continue?')) return;
            document.getElementById('form-tmpl-apply-id').value = selectedTmplId;
            document.getElementById('form-tmpl-apply-form').submit();
        });
    }
    @else
    if (formTmplApplyBtn) {
        formTmplApplyBtn.addEventListener('click', () => {
            if (!selectedTmplId) return;
            document.getElementById('template_id').value = selectedTmplId;
            document.getElementById('tmpl-badge-name').textContent = selectedTmplName;
            document.getElementById('tmpl-badge').style.display = 'inline-flex';
            closeFormTmplModal();
        });
    }
    document.getElementById('tmpl-badge-clear')?.addEventListener('click', () => {
        document.getElementById('template_id').value = '';
        document.getElementById('tmpl-badge').style.display = 'none';
    });
    @endif

    // ─── Delete: checkboxes
    function uncheckAll() {
        document.querySelectorAll('.form-tmpl-checkbox').forEach(c => c.checked = false);
        const sa = document.getElementById('form-tmpl-select-all');
        if (sa) { sa.checked = false; sa.indeterminate = false; }
    }

    function updateDeleteBtn() {
        const deleteBtn   = document.getElementById('form-tmpl-delete-btn');
        const deleteLabel = document.getElementById('form-tmpl-delete-label');
        if (!deleteBtn) return;
        const checked = document.querySelectorAll('.form-tmpl-checkbox:checked').length;
        if (checked > 0) {
            deleteBtn.disabled      = false;
            deleteBtn.style.opacity = '1';
            deleteLabel.textContent = `Delete (${checked})`;
        } else {
            deleteBtn.disabled      = true;
            deleteBtn.style.opacity = '.4';
            deleteLabel.textContent = 'Delete';
        }
        // Sync select-all state
        const total = document.querySelectorAll('.form-tmpl-checkbox').length;
        const sa    = document.getElementById('form-tmpl-select-all');
        if (sa) {
            sa.checked       = total > 0 && checked === total;
            sa.indeterminate = checked > 0 && checked < total;
        }
    }

    document.querySelectorAll('.form-tmpl-checkbox').forEach(cb => {
        cb.addEventListener('change', updateDeleteBtn);
    });

    const selectAllCb = document.getElementById('form-tmpl-select-all');
    if (selectAllCb) {
        selectAllCb.addEventListener('change', () => {
            document.querySelectorAll('.form-tmpl-checkbox').forEach(c => c.checked = selectAllCb.checked);
            updateDeleteBtn();
        });
    }

    // ─── Delete button → show confirmation footer
    const deleteBtn = document.getElementById('form-tmpl-delete-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', () => {
            const count = document.querySelectorAll('.form-tmpl-checkbox:checked').length;
            const msg   = `Delete ${count} template${count !== 1 ? 's' : ''}? This cannot be undone.`;
            document.getElementById('form-tmpl-confirm-msg').textContent = msg;
            showConfirmFooter();
        });
    }

    document.getElementById('form-tmpl-confirm-cancel')?.addEventListener('click', showNormalFooter);

    document.getElementById('form-tmpl-confirm-delete')?.addEventListener('click', async () => {
        const confirmDeleteBtn = document.getElementById('form-tmpl-confirm-delete');
        confirmDeleteBtn.disabled     = true;
        confirmDeleteBtn.textContent  = 'Deleting…';

        const checked = [...document.querySelectorAll('.form-tmpl-checkbox:checked')];
        const ids     = checked.map(c => c.dataset.id);

        for (const id of ids) {
            try {
                await fetch(`${DELETE_BASE}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': FORM_CSRF, 'Accept': 'application/json' },
                });
            } catch (_) {}
            // Remove row from DOM
            document.querySelector(`.form-tmpl-row[data-id="${id}"]`)?.remove();
            // Clear use selection if this was the selected template
            if (String(selectedTmplId) === String(id)) {
                selectedTmplId = null; selectedTmplName = null;
            }
        }

        showNormalFooter();
        updateDeleteBtn();
        uncheckAll();

        // If list is now empty, show empty state
        const remaining = document.querySelectorAll('.form-tmpl-row').length;
        if (remaining === 0) {
            document.getElementById('form-tmpl-list').innerHTML =
                '<p style="text-align:center;color:#9ca3af;font-size:13px;padding:32px 0;">No templates saved yet.</p>';
            if (formTmplApplyBtn) { formTmplApplyBtn.disabled = true; formTmplApplyBtn.style.opacity = '.4'; }
            // Disable the header button too
            const headerBtn = document.getElementById('use-tmpl-btn');
            if (headerBtn) { headerBtn.disabled = true; headerBtn.classList.add('opacity-40', 'cursor-not-allowed'); }
        }
        // Reset if apply selection was cleared
        if (!selectedTmplId) resetUseSelection();

        confirmDeleteBtn.disabled    = false;
        confirmDeleteBtn.textContent = 'Yes, Delete';
    });

    function showNormalFooter() {
        footerNormal.style.display  = 'flex';
        footerConfirm.style.display = 'none';
    }
    function showConfirmFooter() {
        footerNormal.style.display  = 'none';
        footerConfirm.style.display = 'flex';
    }
</script>
@endsection
