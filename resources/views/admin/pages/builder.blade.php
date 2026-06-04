<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Builder — {{ $page->name }}</title>
    <link rel="stylesheet" href="https://unpkg.com/grapesjs@0.21.13/dist/css/grapes.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            overflow: hidden;
            background: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 13px;
            color: #e2e8f0;
        }

        /* ─── TOP BAR ─── */
        #topbar {
            position: fixed;
            inset: 0 0 auto 0;
            height: 52px;
            background: #0f172a;
            border-bottom: 1px solid #1e293b;
            display: flex;
            align-items: center;
            padding: 0 12px;
            gap: 8px;
            z-index: 9999;
            user-select: none;
        }

        .tb-back {
            display: flex; align-items: center; gap: 5px;
            color: #94a3b8; text-decoration: none; padding: 6px 10px;
            border-radius: 6px; font-size: 13px; transition: all .15s;
        }
        .tb-back:hover { background: #1e293b; color: #f1f5f9; }

        .tb-sep { width: 1px; height: 22px; background: #1e293b; flex-shrink: 0; }

        .tb-title { font-size: 14px; font-weight: 600; color: #f1f5f9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px; }

        .tb-spacer { flex: 1; }

        /* Device switcher */
        .device-group {
            display: flex; background: #1e293b; border-radius: 8px; padding: 3px; gap: 2px;
        }
        .device-btn {
            display: flex; align-items: center; gap: 5px;
            background: transparent; border: none; color: #64748b;
            padding: 5px 12px; border-radius: 6px; cursor: pointer;
            font-size: 12px; font-weight: 500; transition: all .15s;
        }
        .device-btn.active, .device-btn:hover { background: #334155; color: #f1f5f9; }

        /* Undo / Redo */
        .icon-btn {
            display: flex; align-items: center; justify-content: center;
            background: transparent; border: none; color: #64748b;
            width: 32px; height: 32px; border-radius: 6px; cursor: pointer; transition: all .15s;
        }
        .icon-btn:hover:not(:disabled) { background: #1e293b; color: #f1f5f9; }
        .icon-btn:disabled { opacity: 0.3; cursor: default; }

        /* Save group */
        #save-group { position: relative; display: flex; }
        #save-btn {
            display: flex; align-items: center; gap: 6px;
            background: #4f46e5; color: #fff; border: none;
            padding: 7px 16px; border-radius: 7px 0 0 7px;
            font-size: 13px; font-weight: 600; cursor: pointer; transition: background .15s;
            border-right: 1px solid rgba(0,0,0,.25);
        }
        #save-btn:hover:not(:disabled) { background: #4338ca; }
        #save-btn:disabled { opacity: .5; cursor: not-allowed; }
        #save-chevron {
            display: flex; align-items: center; justify-content: center;
            background: #4f46e5; color: #fff; border: none;
            padding: 7px 9px; border-radius: 0 7px 7px 0;
            cursor: pointer; transition: background .15s;
        }
        #save-chevron:hover { background: #4338ca; }
        #save-menu {
            position: absolute; top: calc(100% + 6px); right: 0;
            background: #0f172a; border: 1px solid #334155;
            border-radius: 9px; min-width: 195px;
            box-shadow: 0 8px 32px rgba(0,0,0,.5);
            overflow: hidden; display: none; z-index: 99998;
        }
        #save-menu.open { display: block; }
        .sm-item {
            display: flex; align-items: center; gap: 9px;
            padding: 10px 14px; cursor: pointer;
            font-size: 13px; color: #94a3b8;
            border: none; background: transparent; width: 100%; text-align: left;
            transition: all .12s;
        }
        .sm-item:hover { background: #1e293b; color: #f1f5f9; }
        .sm-sep { height: 1px; background: #1e293b; }

        /* ─── LAYOUT ─── */
        #app {
            display: flex;
            height: 100vh;
            padding-top: 52px;
        }

        /* ─── LEFT PANEL ─── */
        #panel-left {
            width: 244px;
            flex-shrink: 0;
            background: #0f172a;
            border-right: 1px solid #1e293b;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ─── CANVAS ─── */
        #canvas-area {
            flex: 1;
            min-width: 0;
            background: #1e293b;
            overflow: auto;
            position: relative;
        }
        #gjs {
            width: 100%;
            height: 100%;
            min-height: 0;
        }

        /* ─── RIGHT PANEL ─── */
        #panel-right {
            width: 272px;
            flex-shrink: 0;
            background: #0f172a;
            border-left: 1px solid #1e293b;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ─── PANEL TABS ─── */
        .p-tabs {
            display: flex;
            border-bottom: 1px solid #1e293b;
            flex-shrink: 0;
        }
        .p-tab {
            flex: 1; background: transparent; border: none; color: #475569;
            padding: 10px 0; cursor: pointer;
            font-size: 12px; font-weight: 500;
            border-bottom: 2px solid transparent;
            transition: all .15s; letter-spacing: .02em;
        }
        .p-tab.active { color: #818cf8; border-bottom-color: #818cf8; }
        .p-tab:hover:not(.active) { color: #94a3b8; }

        .p-body { flex: 1; overflow-y: auto; overflow-x: hidden; }
        .p-pane { display: none; }
        .p-pane.active { display: block; }

        /* ─── SCROLLBAR ─── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }

        /* ─── BLOCK MANAGER ─── */
        #blocks-wrap .gjs-block-categories { padding: 0; }
        #blocks-wrap .gjs-block-category .gjs-title {
            background: #0f172a !important;
            color: #475569 !important;
            font-size: 10px !important;
            font-weight: 700 !important;
            letter-spacing: .08em !important;
            text-transform: uppercase !important;
            padding: 10px 12px 8px !important;
            border-bottom: 1px solid #1e293b !important;
            cursor: pointer !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
        }
        #blocks-wrap .gjs-block-category .gjs-title svg {
            width: 12px !important; height: 12px !important;
            color: #475569 !important; flex-shrink: 0 !important;
        }
        #blocks-wrap .gjs-blocks-c {
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            padding: 8px;
        }
        #blocks-wrap .gjs-open .gjs-blocks-c {
            display: grid !important;
        }
        #blocks-wrap .gjs-block {
            width: auto !important;
            height: auto !important;
            min-height: 60px !important;
            background: #1e293b !important;
            border: 1px solid #1e293b !important;
            border-radius: 8px !important;
            color: #94a3b8 !important;
            font-size: 11px !important;
            font-weight: 500 !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            padding: 10px 6px !important;
            transition: all .15s !important;
            cursor: grab !important;
        }
        #blocks-wrap .gjs-block:hover {
            background: #1e3a5f !important;
            border-color: #818cf8 !important;
            color: #e0e7ff !important;
        }
        #blocks-wrap .gjs-block:active { cursor: grabbing !important; }
        #blocks-wrap .gjs-block .gjs-block-label { font-size: 11px !important; }
        #blocks-wrap .gjs-block svg { width: 20px; height: 20px; }
        #blocks-wrap .fa { font-size: 20px; }

        /* ─── LAYER MANAGER ─── */
        #layers-wrap .gjs-layer {
            background: transparent !important;
            border-color: #1e293b !important;
            color: #94a3b8 !important;
        }
        #layers-wrap .gjs-layer:hover { background: #1e293b !important; }
        #layers-wrap .gjs-layer.gjs-selected { background: #1e293b !important; color: #e0e7ff !important; }
        #layers-wrap .gjs-layer-title-c { color: #94a3b8 !important; }
        #layers-wrap .gjs-layer-count { color: #475569 !important; }

        /* ─── STYLE MANAGER ─── */
        #styles-wrap .gjs-sm-sector {
            background: transparent !important;
            border-color: #1e293b !important;
        }
        #styles-wrap .gjs-sm-sector-title {
            background: #0f172a !important;
            border-color: #1e293b !important;
            color: #475569 !important;
            font-size: 10px !important;
            font-weight: 700 !important;
            letter-spacing: .08em !important;
            text-transform: uppercase !important;
            padding: 9px 12px !important;
        }
        #styles-wrap .gjs-sm-properties { padding: 8px 10px !important; gap: 6px !important; }
        #styles-wrap .gjs-sm-property { background: transparent !important; }
        #styles-wrap .gjs-sm-label { color: #64748b !important; font-size: 11px !important; margin-bottom: 3px !important; }
        #styles-wrap .gjs-field,
        #styles-wrap .gjs-field input,
        #styles-wrap .gjs-field select,
        #styles-wrap .gjs-select {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #cbd5e1 !important;
            border-radius: 5px !important;
            font-size: 12px !important;
        }
        #styles-wrap .gjs-field:focus-within { border-color: #818cf8 !important; }
        #styles-wrap .gjs-sm-radio input:checked + .gjs-sm-radio-item { background: #4f46e5 !important; color: #fff !important; }
        #styles-wrap .gjs-sm-radio-item { background: #1e293b !important; color: #64748b !important; border-color: #334155 !important; }
        #styles-wrap .gjs-clm-tags-btn,
        #styles-wrap .gjs-field-color-picker { border-color: #334155 !important; }
        #styles-wrap .gjs-sm-units { background: #1e293b !important; border-color: #334155 !important; color: #64748b !important; }

        /* ─── TRAIT MANAGER ─── */
        #traits-wrap .gjs-trt-trait {
            border-color: #1e293b !important;
            padding: 6px 10px !important;
        }
        #traits-wrap .gjs-trt-trait__label { color: #64748b !important; font-size: 11px !important; }
        #traits-wrap .gjs-trt-trait__wrp input,
        #traits-wrap .gjs-trt-trait__wrp select,
        #traits-wrap .gjs-trt-trait__wrp textarea {
            background: #1e293b !important;
            border: 1px solid #334155 !important;
            color: #cbd5e1 !important;
            border-radius: 5px !important;
            padding: 5px 8px !important;
            font-size: 12px !important;
            width: 100% !important;
        }

        /* ─── EMPTY STATE ─── */
        .empty-panel {
            padding: 32px 16px;
            text-align: center;
            color: #334155;
            font-size: 12px;
            line-height: 1.6;
        }
        .empty-panel svg { width: 28px; height: 28px; margin: 0 auto 10px; display: block; }

        /* ─── SEARCH BOX ─── */
        .search-wrap {
            padding: 8px;
            border-bottom: 1px solid #1e293b;
            flex-shrink: 0;
        }
        .search-wrap input {
            width: 100%;
            background: #1e293b;
            border: 1px solid #334155;
            color: #cbd5e1;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            outline: none;
        }
        .search-wrap input::placeholder { color: #475569; }
        .search-wrap input:focus { border-color: #818cf8; }

        /* ─── GRAPESJS CANVAS OVERRIDES ─── */
        .gjs-editor { border: none !important; background: transparent !important; width: 100% !important; height: 100% !important; }
        .gjs-cv-canvas {
            background: #e5e7eb !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100% !important;
            height: 100% !important;
        }
        .gjs-cv-canvas__frames { width: 100% !important; height: 100% !important; }
        .gjs-pn-panels { display: none !important; }
        .gjs-toolbar { background: #4f46e5 !important; border-radius: 6px !important; }
        .gjs-toolbar-item { color: #fff !important; }
        .gjs-resizer-h { background: #818cf8 !important; border-color: #818cf8 !important; }
        .gjs-selected { outline: 2px solid #818cf8 !important; outline-offset: -2px !important; }
        .gjs-hovered { outline: 1px dashed #818cf8 !important; outline-offset: -1px !important; }
        .gjs-badge { background: #4f46e5 !important; color: #fff !important; border-radius: 4px !important; }

        /* ─── GRADIENT & OPACITY (injected into Background sector) ─── */
        .gi-sep {
            border-top: 1px solid #1e293b;
            margin: 6px 0 2px;
        }
        .gi-row {
            display: flex; align-items: center; gap: 8px;
            padding: 3px 0;
        }
        .gi-label {
            font-size: 11px; color: #64748b;
            width: 90px; flex-shrink: 0;
        }
        .gi-row select, .gi-row input[type=range] {
            flex: 1; background: #1e293b; border: 1px solid #334155;
            color: #cbd5e1; border-radius: 5px; padding: 4px 6px;
            font-size: 11px; outline: none; min-width: 0;
        }
        .gi-row select:focus { border-color: #818cf8; }
        .gi-colors { display: flex; gap: 6px; flex: 1; }
        .gi-color-wrap {
            flex: 1; display: flex; flex-direction: column; align-items: center; gap: 3px;
        }
        .gi-color-wrap span { font-size: 10px; color: #475569; }
        .gi-row input[type=color] {
            width: 100%; height: 28px; border-radius: 5px;
            border: 1px solid #334155; background: #1e293b;
            cursor: pointer; padding: 2px;
        }
        .gi-opacity-val {
            font-size: 11px; color: #64748b; width: 30px; text-align: right; flex-shrink: 0;
        }

        /* ─── HISTORY MODAL ─── */
        #history-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,.55);
            z-index: 99990; display: none; align-items: center; justify-content: center;
        }
        #history-overlay.open { display: flex; }
        #history-modal {
            background: #0f172a; border: 1px solid #1e293b; border-radius: 12px;
            width: 380px; max-height: 520px; display: flex; flex-direction: column;
            box-shadow: 0 24px 60px rgba(0,0,0,.6);
        }
        #history-modal-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 20px; border-bottom: 1px solid #1e293b; flex-shrink: 0;
        }
        #history-modal-header h2 { font-size: 14px; font-weight: 600; color: #f1f5f9; margin: 0; }
        #history-close {
            background: transparent; border: none; color: #64748b; cursor: pointer;
            padding: 4px; border-radius: 4px; display: flex; align-items: center; transition: color .15s;
        }
        #history-close:hover { color: #f1f5f9; }
        #history-list { flex: 1; overflow-y: auto; padding: 8px; }
        .hv-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 12px; border-radius: 8px; gap: 12px;
            transition: background .15s;
        }
        .hv-item:hover { background: #1e293b; }
        .hv-meta { display: flex; flex-direction: column; gap: 2px; flex: 1; min-width: 0; }
        .hv-num { font-size: 11px; font-weight: 600; color: #818cf8; }
        .hv-date { font-size: 12px; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .hv-restore {
            background: #1e293b; border: 1px solid #334155; color: #94a3b8;
            padding: 5px 12px; border-radius: 6px; font-size: 11px; font-weight: 600;
            cursor: pointer; transition: all .15s; white-space: nowrap; flex-shrink: 0;
        }
        .hv-restore:hover { background: #4f46e5; border-color: #4f46e5; color: #fff; }
        .hv-restore:disabled { opacity: .4; cursor: not-allowed; }
        #history-empty {
            padding: 40px 20px; text-align: center; color: #475569; font-size: 13px;
        }

        /* ─── TOAST ─── */
        #toast {
            position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%) translateY(12px);
            background: #1e293b; color: #e2e8f0; padding: 10px 20px;
            border-radius: 8px; font-size: 13px; font-weight: 500;
            border: 1px solid #334155;
            box-shadow: 0 4px 20px rgba(0,0,0,.4);
            z-index: 99999; opacity: 0; transition: all .2s; pointer-events: none;
            white-space: nowrap;
        }
        #toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        #toast.ok { border-color: #22c55e; color: #86efac; }
        #toast.err { border-color: #ef4444; color: #fca5a5; }

        #tmpl-overlay.open { display: flex; }
        #tmpl-name:focus { border-color: #818cf8 !important; }
    </style>
</head>
<body>

{{-- ─── TOP BAR ─── --}}
<div id="topbar">
    <a href="{{ route('admin.pages.edit', $page) }}" class="tb-back">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back
    </a>
    <div class="tb-sep"></div>
    <span class="tb-title">{{ $page->name }}</span>
    <div class="tb-spacer"></div>

    {{-- Undo / Redo --}}
    <button class="icon-btn" id="btn-undo" title="Undo (⌘Z)">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6-6M3 10l6 6"/>
        </svg>
    </button>
    <button class="icon-btn" id="btn-redo" title="Redo (⌘⇧Z)">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 10H11a8 8 0 00-8 8v2M21 10l-6-6M21 10l-6 6"/>
        </svg>
    </button>

    <div class="tb-sep"></div>

    {{-- Device switcher --}}
    <div class="device-group">
        <button class="device-btn active" data-device="Desktop" title="Desktop">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>
            </svg>
            Desktop
        </button>
        <button class="device-btn" data-device="Tablet" title="Tablet">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="4" y="2" width="16" height="20" rx="2"/><circle cx="12" cy="17.5" r=".5" fill="currentColor"/>
            </svg>
            Tablet
        </button>
        <button class="device-btn" data-device="Mobile" title="Mobile">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="7" y="2" width="10" height="20" rx="2"/><circle cx="12" cy="17.5" r=".5" fill="currentColor"/>
            </svg>
            Mobile
        </button>
    </div>

    <div class="tb-sep"></div>

    <a href="{{ route('pages.show', $page->slug) }}" target="_blank" id="view-btn"
       style="display:flex;align-items:center;gap:6px;background:transparent;color:#94a3b8;border:1px solid #1e293b;padding:6px 14px;border-radius:7px;font-size:13px;font-weight:500;text-decoration:none;transition:all .15s;"
       onmouseover="this.style.borderColor='#334155';this.style.color='#f1f5f9'"
       onmouseout="this.style.borderColor='#1e293b';this.style.color='#94a3b8'">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
        </svg>
        View Page
    </a>

    <button id="history-btn" class="icon-btn" title="Version History" style="width:auto;padding:0 12px;gap:6px;font-size:13px;font-weight:500;color:#94a3b8;border:1px solid #1e293b;border-radius:7px;height:34px;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3M3.05 11a9 9 0 1 0 .5-3M3 4v4h4"/>
        </svg>
        History
    </button>

    <div id="save-group">
        <button id="save-btn">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            Save Draft
        </button>
        <button id="save-chevron" title="More save options">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div id="save-menu">
            <button class="sm-item" data-save-action="publish">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M2 12h20M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/>
                </svg>
                Save &amp; Publish
            </button>
            <button class="sm-item" data-save-action="draft">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Save Draft
            </button>
            <div class="sm-sep"></div>
            <button class="sm-item" data-save-action="template">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                Save as Template
            </button>
        </div>
    </div>
</div>

{{-- ─── APP ─── --}}
<div id="app">

    {{-- LEFT PANEL --}}
    <div id="panel-left">
        <div class="p-tabs">
            <button class="p-tab active" data-panel="blocks">Elements</button>
            <button class="p-tab" data-panel="layers">Layers</button>
        </div>
        <div class="search-wrap" id="block-search-wrap">
            <input type="text" id="block-search" placeholder="Search elements…">
        </div>
        <div class="p-body">
            <div class="p-pane active" id="pane-blocks">
                <div id="blocks-wrap"></div>
            </div>
            <div class="p-pane" id="pane-layers">
                <div id="layers-wrap"></div>
            </div>
        </div>
    </div>

    {{-- CANVAS --}}
    <div id="canvas-area">
        <div id="gjs"></div>
    </div>

    {{-- RIGHT PANEL --}}
    <div id="panel-right">
        <div class="p-tabs">
            <button class="p-tab active" data-panel="style">Style</button>
            <button class="p-tab" data-panel="traits">Settings</button>
        </div>
        <div class="p-body">
            <div class="p-pane active" id="pane-style">
                <div id="styles-wrap"></div>
            </div>
            <div class="p-pane" id="pane-traits">
                <div id="traits-wrap">
                    <div class="empty-panel">
                        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Select an element<br>to edit its settings
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<div id="toast"></div>

{{-- ─── HISTORY MODAL ─── --}}
<div id="history-overlay">
    <div id="history-modal">
        <div id="history-modal-header">
            <h2>Version History</h2>
            <button id="history-close">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div id="history-list">
            <div id="history-empty">No saved versions yet.</div>
        </div>
    </div>
</div>

{{-- ─── SAVE AS TEMPLATE MODAL ─── --}}
<div id="tmpl-overlay" style="position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:99990;display:none;align-items:center;justify-content:center;">
    <div id="tmpl-modal" style="background:#0f172a;border:1px solid #1e293b;border-radius:12px;width:360px;box-shadow:0 24px 60px rgba(0,0,0,.6);">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #1e293b;">
            <h2 style="font-size:14px;font-weight:600;color:#f1f5f9;margin:0;">Save as Template</h2>
            <button id="tmpl-close" style="background:transparent;border:none;color:#64748b;cursor:pointer;padding:4px;border-radius:4px;display:flex;align-items:center;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div style="padding:20px;display:flex;flex-direction:column;gap:16px;">
            <p style="font-size:13px;color:#64748b;margin:0;">Give this layout a name so you can reuse it when creating new pages.</p>
            <input type="text" id="tmpl-name" placeholder="e.g. Landing Page, Blog Post…"
                   style="background:#1e293b;border:1px solid #334155;color:#cbd5e1;padding:8px 12px;border-radius:7px;font-size:13px;outline:none;width:100%;box-sizing:border-box;">
            <div style="display:flex;justify-content:flex-end;gap:8px;">
                <button id="tmpl-cancel" style="background:transparent;border:1px solid #1e293b;color:#64748b;padding:7px 16px;border-radius:7px;font-size:13px;cursor:pointer;">Cancel</button>
                <button id="tmpl-confirm" style="background:#4f46e5;color:#fff;border:none;padding:7px 18px;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;">Save Template</button>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/grapesjs@0.21.13/dist/grapes.min.js"></script>
<script src="https://unpkg.com/grapesjs-blocks-basic@1.0.2/dist/grapesjs-blocks-basic.min.js"></script>
<script src="https://unpkg.com/grapesjs-plugin-forms@2.0.7/dist/grapesjs-plugin-forms.min.js"></script>

<script>
const SAVE_URL   = @json(route('admin.pages.builder.save', $page));
const CSRF_TOKEN = @json(csrf_token());
const SAVED_DATA = @json($page->builder_data);

// ─── Panel tabs
function initTabs(panelEl) {
    panelEl.querySelectorAll('.p-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            panelEl.querySelectorAll('.p-tab').forEach(t => t.classList.remove('active'));
            panelEl.querySelectorAll('.p-pane').forEach(p => p.classList.remove('active'));
            tab.classList.add('active');
            const pane = panelEl.querySelector('#pane-' + tab.dataset.panel);
            if (pane) pane.classList.add('active');
            // Toggle search bar for left panel
            const searchWrap = document.getElementById('block-search-wrap');
            if (searchWrap) searchWrap.style.display = tab.dataset.panel === 'blocks' ? '' : 'none';
        });
    });
}
initTabs(document.getElementById('panel-left'));
initTabs(document.getElementById('panel-right'));

// ─── GrapesJS
const editor = grapesjs.init({
    container: '#gjs',
    height: '100%',
    width: 'auto',
    fromElement: false,
    storageManager: false,
    noticeOnUnload: false,

    plugins: ['gjs-blocks-basic', 'gjs-plugin-forms'],
    pluginsOpts: {
        'gjs-blocks-basic': {
            flexGrid: true,
            addBasicStyle: true,
            blocks: ['column1', 'column2', 'column3', 'column3-7', 'text', 'link', 'image', 'video'],
        },
        'gjs-plugin-forms': {
            blocks: ['form', 'input', 'textarea', 'select', 'button', 'label', 'checkbox', 'radio'],
        },
    },

    blockManager: { appendTo: '#blocks-wrap' },
    layerManager:  { appendTo: '#layers-wrap' },
    styleManager:  { appendTo: '#styles-wrap', sectors: buildStyleSectors() },
    traitManager:  { appendTo: '#traits-wrap' },

    panels: { defaults: [] },

    deviceManager: {
        devices: [
            { name: 'Desktop', width: '' },
            { name: 'Tablet',  width: '768px',  widthMedia: '1024px' },
            { name: 'Mobile',  width: '375px',  widthMedia: '480px' },
        ],
    },

    canvas: {
        styles: ['https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap'],
        scripts: [],
    },
});

// ─── Custom blocks
const bm = editor.BlockManager;

bm.add('pb-section', {
    label: 'Section', category: 'Layout',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="9" x2="22" y2="9"/></svg>`,
    content: `<section data-gjs-type="section" style="padding:80px 24px;background:#ffffff;">
  <div style="max-width:1200px;margin:0 auto;">
    <p>Section content — drag elements in here.</p>
  </div>
</section>`,
});

bm.add('pb-hero', {
    label: 'Hero', category: 'Layout',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="13" x2="22" y2="13"/><circle cx="12" cy="8.5" r="2"/></svg>`,
    content: `<section style="padding:100px 24px;background:linear-gradient(135deg,#1e40af,#7c3aed);color:#fff;text-align:center;">
  <h1 style="font-size:52px;font-weight:800;margin-bottom:16px;line-height:1.1;">Your Hero Headline</h1>
  <p style="font-size:20px;opacity:.85;max-width:560px;margin:0 auto 36px;">Subheadline text that supports the main headline and drives action.</p>
  <a href="#" style="display:inline-block;padding:14px 36px;background:#fff;color:#1e40af;border-radius:10px;font-weight:700;font-size:15px;text-decoration:none;">Get Started</a>
</section>`,
});

bm.add('pb-2cols', {
    label: '2 Columns', category: 'Layout',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="4" width="9" height="16" rx="1.5"/><rect x="13" y="4" width="9" height="16" rx="1.5"/></svg>`,
    content: `<div style="display:flex;flex-wrap:wrap;gap:24px;padding:40px 24px;">
  <div style="flex:1 1 280px;min-width:0;padding:24px;background:#f8fafc;border-radius:10px;"><h3 style="font-weight:600;margin-bottom:8px;">Column One</h3><p style="color:#64748b;font-size:15px;line-height:1.6;">Add your content here.</p></div>
  <div style="flex:1 1 280px;min-width:0;padding:24px;background:#f8fafc;border-radius:10px;"><h3 style="font-weight:600;margin-bottom:8px;">Column Two</h3><p style="color:#64748b;font-size:15px;line-height:1.6;">Add your content here.</p></div>
</div>`,
});

bm.add('pb-3cols', {
    label: '3 Columns', category: 'Layout',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="4" width="6" height="16" rx="1"/><rect x="9" y="4" width="6" height="16" rx="1"/><rect x="17" y="4" width="6" height="16" rx="1"/></svg>`,
    content: `<div style="display:flex;flex-wrap:wrap;gap:20px;padding:40px 24px;">
  <div style="flex:1 1 200px;min-width:0;padding:20px;background:#f8fafc;border-radius:10px;text-align:center;"><h3 style="font-weight:600;margin-bottom:8px;">Feature 1</h3><p style="color:#64748b;font-size:14px;line-height:1.6;">Description text.</p></div>
  <div style="flex:1 1 200px;min-width:0;padding:20px;background:#f8fafc;border-radius:10px;text-align:center;"><h3 style="font-weight:600;margin-bottom:8px;">Feature 2</h3><p style="color:#64748b;font-size:14px;line-height:1.6;">Description text.</p></div>
  <div style="flex:1 1 200px;min-width:0;padding:20px;background:#f8fafc;border-radius:10px;text-align:center;"><h3 style="font-weight:600;margin-bottom:8px;">Feature 3</h3><p style="color:#64748b;font-size:14px;line-height:1.6;">Description text.</p></div>
</div>`,
});

bm.add('pb-card', {
    label: 'Card', category: 'Layout',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><line x1="3" y1="9" x2="21" y2="9"/></svg>`,
    content: `<div style="background:#fff;border-radius:12px;box-shadow:0 2px 16px rgba(0,0,0,.08);overflow:hidden;max-width:360px;">
  <div style="height:180px;background:linear-gradient(135deg,#dbeafe,#ede9fe);"></div>
  <div style="padding:24px;">
    <h3 style="font-size:18px;font-weight:700;margin-bottom:8px;">Card Title</h3>
    <p style="color:#64748b;font-size:14px;line-height:1.6;margin-bottom:16px;">Card description goes here. Edit this text.</p>
    <a href="#" style="display:inline-block;padding:8px 20px;background:#4f46e5;color:#fff;border-radius:7px;font-size:13px;font-weight:600;text-decoration:none;">Learn More</a>
  </div>
</div>`,
});

bm.add('pb-h1', {
    label: 'Heading 1', category: 'Text',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 6h16M4 12h16M4 18h7"/></svg>`,
    content: '<h1 style="font-size:48px;font-weight:800;color:#0f172a;line-height:1.1;margin:0 0 16px;">Main Headline</h1>',
});

bm.add('pb-h2', {
    label: 'Heading 2', category: 'Text',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 6h16M4 12h10"/></svg>`,
    content: '<h2 style="font-size:32px;font-weight:700;color:#0f172a;line-height:1.2;margin:0 0 12px;">Section Heading</h2>',
});

bm.add('pb-h3', {
    label: 'Heading 3', category: 'Text',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 6h12M4 12h8"/></svg>`,
    content: '<h3 style="font-size:22px;font-weight:600;color:#1e293b;line-height:1.3;margin:0 0 10px;">Sub Heading</h3>',
});

bm.add('pb-paragraph', {
    label: 'Paragraph', category: 'Text',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="10" x2="20" y2="10"/><line x1="4" y1="14" x2="14" y2="14"/></svg>`,
    content: '<p style="font-size:16px;line-height:1.75;color:#475569;margin:0 0 16px;">Your paragraph text goes here. Click to select and edit this text block. You can change the font, size, color, and alignment from the Style panel.</p>',
});

bm.add('pb-quote', {
    label: 'Quote', category: 'Text',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/></svg>`,
    content: `<blockquote style="border-left:4px solid #818cf8;padding:20px 24px;margin:0 0 20px;background:#f8f7ff;border-radius:0 8px 8px 0;">
  <p style="font-size:18px;font-style:italic;color:#475569;line-height:1.7;margin:0 0 12px;">"The best way to predict the future is to create it."</p>
  <cite style="font-size:13px;font-weight:600;color:#818cf8;font-style:normal;">— Author Name</cite>
</blockquote>`,
});

bm.add('pb-button', {
    label: 'Button', category: 'Elements',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="8" width="18" height="8" rx="4"/></svg>`,
    content: '<a href="#" style="display:inline-block;padding:12px 28px;background:#4f46e5;color:#fff;border-radius:8px;font-weight:600;font-size:15px;text-decoration:none;cursor:pointer;">Button Text</a>',
});

bm.add('pb-divider', {
    label: 'Divider', category: 'Elements',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="4" y1="12" x2="20" y2="12"/></svg>`,
    content: '<hr style="border:none;border-top:1px solid #e2e8f0;margin:32px 0;">',
});

bm.add('pb-spacer', {
    label: 'Spacer', category: 'Elements',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 6h8M8 18h8M12 6v12"/></svg>`,
    content: '<div style="height:60px;"></div>',
});

bm.add('pb-badge', {
    label: 'Badge', category: 'Elements',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="9" width="18" height="6" rx="3"/></svg>`,
    content: '<span style="display:inline-block;padding:4px 12px;background:#e0e7ff;color:#4338ca;border-radius:99px;font-size:12px;font-weight:600;letter-spacing:.04em;">Badge</span>',
});

// ─── Load saved content
if (SAVED_DATA) {
    try {
        const d = JSON.parse(SAVED_DATA);
        // components/styles may be an array (correct) or a JSON string (old double-encoded format)
        const components = typeof d.components === 'string' ? JSON.parse(d.components) : d.components;
        const styles     = typeof d.styles     === 'string' ? JSON.parse(d.styles)     : d.styles;
        if (components && components.length) editor.setComponents(components);
        if (styles     && styles.length)     editor.setStyle(styles);
    } catch (e) {
        console.warn('Could not load builder data', e);
    }
}

// Force the canvas to fill the available area after first render
editor.on('load', () => setTimeout(() => editor.refresh(), 50));

// ─── Device buttons
document.querySelectorAll('.device-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.device-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        editor.setDevice(btn.dataset.device);
    });
});

// ─── Undo / Redo
document.getElementById('btn-undo').addEventListener('click', () => editor.runCommand('core:undo'));
document.getElementById('btn-redo').addEventListener('click', () => editor.runCommand('core:redo'));

// ─── Block search
document.getElementById('block-search').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#blocks-wrap .gjs-block').forEach(block => {
        const label = block.querySelector('.gjs-block-label');
        const text = (label ? label.textContent : block.textContent).toLowerCase();
        block.style.display = text.includes(q) ? '' : 'none';
    });
    // Hide empty categories
    document.querySelectorAll('#blocks-wrap .gjs-block-category').forEach(cat => {
        const visible = [...cat.querySelectorAll('.gjs-block')].some(b => b.style.display !== 'none');
        cat.style.display = visible ? '' : 'none';
    });
});

// ─── Gradient & Opacity — injected into the Background sector
function hexFromRgb(rgb) {
    const m = rgb.match(/\d+/g);
    if (!m || m.length < 3) return '#000000';
    return '#' + m.slice(0,3).map(n => parseInt(n).toString(16).padStart(2,'0')).join('');
}

function parseGradient(bgImage) {
    if (!bgImage) return null;
    const lin = bgImage.match(/linear-gradient\(\s*([^,]+?)\s*,\s*([^,]+?)\s*,\s*([^)]+?)\s*\)/i);
    if (lin) return { type: 'linear', dir: lin[1].trim(), c1: lin[2].trim(), c2: lin[3].trim() };
    const rad = bgImage.match(/radial-gradient\([^,]+,\s*([^,]+?)\s*,\s*([^)]+?)\s*\)/i);
    if (rad) return { type: 'radial', c1: rad[1].trim(), c2: rad[2].trim() };
    return null;
}

const GRADIENT_HTML = `
<div class="gi-sep"></div>
<div class="gi-row">
  <span class="gi-label">Opacity</span>
  <input type="range" id="gi-opacity" min="0" max="1" step="0.01" value="1">
  <span class="gi-opacity-val" id="gi-opacity-val">100%</span>
</div>
<div class="gi-sep"></div>
<div class="gi-row">
  <span class="gi-label">Gradient type</span>
  <select id="gi-type">
    <option value="none">None</option>
    <option value="linear">Linear</option>
    <option value="radial">Radial</option>
  </select>
</div>
<div id="gi-opts" style="display:none;flex-direction:column;gap:6px;">
  <div class="gi-row" id="gi-dir-row">
    <span class="gi-label">Direction</span>
    <select id="gi-dir">
      <option value="to right">→ To Right</option>
      <option value="to left">← To Left</option>
      <option value="to bottom">↓ To Bottom</option>
      <option value="to top">↑ To Top</option>
      <option value="to bottom right">↘ Bottom Right</option>
      <option value="to bottom left">↙ Bottom Left</option>
      <option value="45deg">45°</option>
      <option value="135deg">135°</option>
    </select>
  </div>
  <div class="gi-row">
    <span class="gi-label">Colors</span>
    <div class="gi-colors">
      <div class="gi-color-wrap">
        <input type="color" id="gi-c1" value="#4f46e5">
        <span>Start</span>
      </div>
      <div class="gi-color-wrap">
        <input type="color" id="gi-c2" value="#7c3aed">
        <span>End</span>
      </div>
    </div>
  </div>
</div>`;

let giInjected = false;

function injectGradientIntoBackground() {
    if (giInjected) return;
    const sectors = document.querySelectorAll('#styles-wrap .gjs-sm-sector');
    let bgSector = null;
    sectors.forEach(s => {
        const t = s.querySelector('.gjs-sm-sector-title');
        if (t && t.textContent.trim() === 'Background') bgSector = s;
    });
    if (!bgSector) return;
    const props = bgSector.querySelector('.gjs-sm-properties');
    if (!props) return;

    const wrap = document.createElement('div');
    wrap.id = 'gi-wrap';
    wrap.innerHTML = GRADIENT_HTML;
    props.appendChild(wrap);
    giInjected = true;

    // Wire up events
    const giOpacity = document.getElementById('gi-opacity');
    const giOpacityVal = document.getElementById('gi-opacity-val');
    const giType = document.getElementById('gi-type');
    const giDir  = document.getElementById('gi-dir');
    const giC1   = document.getElementById('gi-c1');
    const giC2   = document.getElementById('gi-c2');
    const giOpts = document.getElementById('gi-opts');
    const giDirRow = document.getElementById('gi-dir-row');

    function buildGrad() {
        const t = giType.value, c1 = giC1.value, c2 = giC2.value;
        if (t === 'none') return 'none';
        if (t === 'radial') return `radial-gradient(circle, ${c1}, ${c2})`;
        return `linear-gradient(${giDir.value}, ${c1}, ${c2})`;
    }

    function updateOpts() {
        const t = giType.value;
        giOpts.style.display   = t === 'none' ? 'none' : 'flex';
        giDirRow.style.display = t === 'linear' ? '' : 'none';
    }

    function applyGradient() {
        const c = editor.getSelected(); if (!c) return;
        c.addStyle({ 'background-image': buildGrad() });
    }

    giType.addEventListener('change', () => { updateOpts(); applyGradient(); });
    giDir.addEventListener('change', applyGradient);
    giC1.addEventListener('input', applyGradient);
    giC2.addEventListener('input', applyGradient);

    giOpacity.addEventListener('input', () => {
        const v = parseFloat(giOpacity.value);
        giOpacityVal.textContent = Math.round(v * 100) + '%';
        const c = editor.getSelected(); if (c) c.addStyle({ opacity: v });
    });

    // Sync controls from selected component's existing styles
    editor.on('component:selected', component => {
        if (!component) return;
        const styles = component.getStyle();
        // Opacity
        const op = styles.opacity !== undefined ? parseFloat(styles.opacity) : 1;
        giOpacity.value = op;
        giOpacityVal.textContent = Math.round(op * 100) + '%';
        // Gradient
        const parsed = parseGradient(styles['background-image'] || '');
        if (parsed) {
            giType.value = parsed.type;
            const toHex = v => v.startsWith('rgb') ? hexFromRgb(v) : v.slice(0,7);
            giC1.value = toHex(parsed.c1);
            giC2.value = toHex(parsed.c2);
            if (parsed.type === 'linear' && parsed.dir) giDir.value = parsed.dir;
        } else {
            giType.value = 'none';
        }
        updateOpts();
    });
}

// Inject as soon as the Background sector is available (on first component select)
editor.on('component:selected', injectGradientIntoBackground);

// ─── Toast helper
const toast = document.getElementById('toast');
function showToast(msg, type = 'ok') {
    toast.textContent = msg;
    toast.className = 'show ' + type;
    clearTimeout(toast._t);
    toast._t = setTimeout(() => { toast.className = ''; }, 2800);
}

// ─── Save dropdown
const saveMenu = document.getElementById('save-menu');

document.getElementById('save-chevron').addEventListener('click', e => {
    e.stopPropagation();
    saveMenu.classList.toggle('open');
});
document.addEventListener('click', () => saveMenu.classList.remove('open'));
saveMenu.addEventListener('click', e => e.stopPropagation());

document.getElementById('save-btn').addEventListener('click', () => doSave('draft'));

document.querySelectorAll('[data-save-action]').forEach(btn => {
    btn.addEventListener('click', () => {
        saveMenu.classList.remove('open');
        if (btn.dataset.saveAction === 'template') openTmplModal();
        else doSave(btn.dataset.saveAction);
    });
});

async function doSave(action) {
    const btn = document.getElementById('save-btn');
    btn.disabled = true;
    const prevHTML = btn.innerHTML;
    btn.textContent = 'Saving…';
    try {
        const res = await fetch(SAVE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify({
                html:       editor.getHtml(),
                css:        editor.getCss(),
                components: JSON.stringify(editor.getComponents()),
                styles:     JSON.stringify(editor.getStyle()),
                publish:    action === 'publish',
            }),
        });
        if (res.ok) showToast(action === 'publish' ? 'Published!' : 'Draft saved!', 'ok');
        else        showToast('Save failed — try again', 'err');
    } catch {
        showToast('Network error', 'err');
    }
    btn.disabled = false;
    btn.innerHTML = prevHTML;
}

// Cmd/Ctrl+S
document.addEventListener('keydown', e => {
    if ((e.metaKey || e.ctrlKey) && e.key === 's') { e.preventDefault(); doSave('draft'); }
});

// ─── Version History
const VERSIONS_URL = @json(route('admin.pages.versions.index', $page));
const RESTORE_BASE  = @json(url('admin/pages/' . $page->id . '/versions'));

const historyOverlay = document.getElementById('history-overlay');
const historyList    = document.getElementById('history-list');
const historyEmpty   = document.getElementById('history-empty');

document.getElementById('history-btn').addEventListener('click', openHistory);
document.getElementById('history-close').addEventListener('click', () => historyOverlay.classList.remove('open'));
historyOverlay.addEventListener('click', e => { if (e.target === historyOverlay) historyOverlay.classList.remove('open'); });

async function openHistory() {
    historyList.innerHTML = '<div style="padding:20px;text-align:center;color:#475569;font-size:13px;">Loading…</div>';
    historyOverlay.classList.add('open');

    try {
        const res  = await fetch(VERSIONS_URL, { headers: { 'X-CSRF-TOKEN': CSRF_TOKEN } });
        const list = await res.json();

        if (!list.length) {
            historyList.innerHTML = '<div id="history-empty">No saved versions yet.</div>';
            return;
        }

        historyList.innerHTML = list.map((v, i) => `
            <div class="hv-item">
                <div class="hv-meta">
                    <span class="hv-num">Version ${list.length - i}</span>
                    <span class="hv-date">${v.label}</span>
                </div>
                <button class="hv-restore" data-id="${v.id}">Restore</button>
            </div>
        `).join('');

        historyList.querySelectorAll('.hv-restore').forEach(btn => {
            btn.addEventListener('click', () => restoreVersion(btn.dataset.id, btn));
        });
    } catch {
        historyList.innerHTML = '<div style="padding:20px;text-align:center;color:#fca5a5;font-size:13px;">Failed to load history.</div>';
    }
}

async function restoreVersion(versionId, btn) {
    if (!confirm('Restore this version? Unsaved changes will be lost.')) return;
    btn.disabled = true;
    btn.textContent = 'Restoring…';

    try {
        const res  = await fetch(`${RESTORE_BASE}/${versionId}/restore`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Content-Type': 'application/json' },
        });
        const data = await res.json();

        if (res.ok && data.success) {
            const d = JSON.parse(data.builder_data);
            const components = typeof d.components === 'string' ? JSON.parse(d.components) : d.components;
            const styles     = typeof d.styles     === 'string' ? JSON.parse(d.styles)     : d.styles;
            if (components && components.length) editor.setComponents(components);
            else editor.setComponents([]);
            if (styles && styles.length) editor.setStyle(styles);
            else editor.setStyle([]);
            historyOverlay.classList.remove('open');
            showToast('Version restored', 'ok');
        } else {
            showToast('Restore failed', 'err');
            btn.disabled = false;
            btn.textContent = 'Restore';
        }
    } catch {
        showToast('Network error', 'err');
        btn.disabled = false;
        btn.textContent = 'Restore';
    }
}

// ─── Save as Template
const TEMPLATES_STORE_URL = @json(route('admin.templates.store'));

const tmplOverlay = document.getElementById('tmpl-overlay');
const tmplNameInput = document.getElementById('tmpl-name');

function openTmplModal() {
    tmplNameInput.value = '';
    tmplOverlay.style.display = 'flex';
    setTimeout(() => tmplNameInput.focus(), 50);
}
function closeTmplModal() {
    tmplOverlay.style.display = 'none';
}

document.getElementById('tmpl-close').addEventListener('click', closeTmplModal);
document.getElementById('tmpl-cancel').addEventListener('click', closeTmplModal);
tmplOverlay.addEventListener('click', e => { if (e.target === tmplOverlay) closeTmplModal(); });

tmplNameInput.addEventListener('keydown', e => {
    if (e.key === 'Enter') document.getElementById('tmpl-confirm').click();
    if (e.key === 'Escape') closeTmplModal();
});

document.getElementById('tmpl-confirm').addEventListener('click', async () => {
    const name = tmplNameInput.value.trim();
    if (!name) { tmplNameInput.focus(); return; }

    const btn = document.getElementById('tmpl-confirm');
    btn.disabled = true;
    btn.textContent = 'Saving…';

    try {
        const res = await fetch(TEMPLATES_STORE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify({
                name,
                html:       editor.getHtml(),
                css:        editor.getCss(),
                components: JSON.stringify(editor.getComponents()),
                styles:     JSON.stringify(editor.getStyle()),
            }),
        });
        if (res.ok) {
            closeTmplModal();
            showToast('Template saved!', 'ok');
        } else {
            showToast('Failed to save template', 'err');
        }
    } catch {
        showToast('Network error', 'err');
    }

    btn.disabled = false;
    btn.textContent = 'Save Template';
});

// ─── Style sectors config (defined as a function so it reads cleanly above)
function buildStyleSectors() {
    return [
        {
            name: 'Layout', open: true,
            properties: [
                { property: 'display', type: 'select', list: [
                    { value: 'block', name: 'Block' }, { value: 'flex', name: 'Flex' },
                    { value: 'grid', name: 'Grid' }, { value: 'inline-block', name: 'Inline Block' },
                    { value: 'inline', name: 'Inline' }, { value: 'none', name: 'None' },
                ]},
                { property: 'flex-direction', type: 'select', list: [
                    { value: 'row', name: 'Row' }, { value: 'column', name: 'Column' },
                    { value: 'row-reverse', name: 'Row Reverse' }, { value: 'column-reverse', name: 'Col Reverse' },
                ]},
                { property: 'justify-content', type: 'select', list: [
                    { value: 'flex-start', name: 'Start' }, { value: 'center', name: 'Center' },
                    { value: 'flex-end', name: 'End' }, { value: 'space-between', name: 'Space Between' },
                    { value: 'space-around', name: 'Space Around' }, { value: 'space-evenly', name: 'Space Evenly' },
                ]},
                { property: 'align-items', type: 'select', list: [
                    { value: 'stretch', name: 'Stretch' }, { value: 'flex-start', name: 'Start' },
                    { value: 'center', name: 'Center' }, { value: 'flex-end', name: 'End' },
                ]},
                { property: 'flex-wrap', type: 'select', list: [
                    { value: 'nowrap', name: 'No Wrap' }, { value: 'wrap', name: 'Wrap' },
                ]},
                { property: 'gap', type: 'integer', units: ['px','em','rem'], unit: 'px' },
            ]
        },
        {
            name: 'Dimension', open: false,
            properties: [
                { property: 'width',      type: 'integer', units: ['px','%','vw','em','rem'], unit: '%' },
                { property: 'height',     type: 'integer', units: ['px','%','vh','em','rem'], unit: 'px' },
                { property: 'max-width',  type: 'integer', units: ['px','%','vw','em','rem'], unit: 'px' },
                { property: 'min-height', type: 'integer', units: ['px','%','vh','em','rem'], unit: 'px' },
                { property: 'margin', type: 'composite', properties: [
                    { property: 'margin-top',    type: 'integer', units: ['px','%','em','rem','auto'], unit: 'px' },
                    { property: 'margin-right',  type: 'integer', units: ['px','%','em','rem','auto'], unit: 'px' },
                    { property: 'margin-bottom', type: 'integer', units: ['px','%','em','rem','auto'], unit: 'px' },
                    { property: 'margin-left',   type: 'integer', units: ['px','%','em','rem','auto'], unit: 'px' },
                ]},
                { property: 'padding', type: 'composite', properties: [
                    { property: 'padding-top',    type: 'integer', units: ['px','%','em','rem'], unit: 'px' },
                    { property: 'padding-right',  type: 'integer', units: ['px','%','em','rem'], unit: 'px' },
                    { property: 'padding-bottom', type: 'integer', units: ['px','%','em','rem'], unit: 'px' },
                    { property: 'padding-left',   type: 'integer', units: ['px','%','em','rem'], unit: 'px' },
                ]},
            ]
        },
        {
            name: 'Typography', open: false,
            properties: [
                { property: 'font-family', type: 'select', list: [
                    { value: 'inherit', name: 'Inherit' },
                    { value: "'Inter', sans-serif", name: 'Inter' },
                    { value: "-apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif", name: 'System' },
                    { value: "Georgia, 'Times New Roman', serif", name: 'Georgia' },
                    { value: "'Helvetica Neue', Helvetica, Arial, sans-serif", name: 'Helvetica' },
                    { value: "'Courier New', Courier, monospace", name: 'Monospace' },
                ]},
                { property: 'font-size',    type: 'integer', units: ['px','em','rem','%','vw'], unit: 'px' },
                { property: 'font-weight',  type: 'select', list: [
                    { value: '300', name: 'Light 300' }, { value: '400', name: 'Regular 400' },
                    { value: '500', name: 'Medium 500' }, { value: '600', name: 'SemiBold 600' },
                    { value: '700', name: 'Bold 700' },   { value: '800', name: 'ExtraBold 800' },
                ]},
                { property: 'line-height',    type: 'integer', units: ['','px','em'], unit: '' },
                { property: 'letter-spacing', type: 'integer', units: ['px','em'], unit: 'px' },
                { property: 'color', type: 'color' },
                { property: 'text-align', type: 'radio', list: [
                    { value: 'left', name: 'Left' }, { value: 'center', name: 'Center' },
                    { value: 'right', name: 'Right' }, { value: 'justify', name: 'Justify' },
                ]},
                { property: 'text-decoration', type: 'select', list: [
                    { value: 'none', name: 'None' }, { value: 'underline', name: 'Underline' },
                    { value: 'line-through', name: 'Line-through' },
                ]},
                { property: 'text-transform', type: 'select', list: [
                    { value: 'none', name: 'None' }, { value: 'uppercase', name: 'Uppercase' },
                    { value: 'lowercase', name: 'Lowercase' }, { value: 'capitalize', name: 'Capitalize' },
                ]},
            ]
        },
        {
            name: 'Background', open: false,
            properties: [
                { property: 'background-color', type: 'color' },
                { property: 'background-image' },
                { property: 'background-repeat', type: 'select', list: [
                    { value: 'repeat', name: 'Repeat' }, { value: 'no-repeat', name: 'No Repeat' },
                    { value: 'repeat-x', name: 'Repeat X' }, { value: 'repeat-y', name: 'Repeat Y' },
                ]},
                { property: 'background-size', type: 'select', list: [
                    { value: 'auto', name: 'Auto' }, { value: 'cover', name: 'Cover' },
                    { value: 'contain', name: 'Contain' },
                ]},
                { property: 'background-position', type: 'select', list: [
                    { value: 'center center', name: 'Center' }, { value: 'top center', name: 'Top' },
                    { value: 'bottom center', name: 'Bottom' },
                ]},
            ]
        },
        {
            name: 'Border & Shadow', open: false,
            properties: [
                { property: 'border-radius', type: 'integer', units: ['px','%'], unit: 'px' },
                { property: 'border', type: 'composite', properties: [
                    { property: 'border-width', type: 'integer', units: ['px'], unit: 'px' },
                    { property: 'border-style', type: 'select', list: [
                        { value: 'none', name: 'None' }, { value: 'solid', name: 'Solid' },
                        { value: 'dashed', name: 'Dashed' }, { value: 'dotted', name: 'Dotted' },
                    ]},
                    { property: 'border-color', type: 'color' },
                ]},
                { property: 'box-shadow', type: 'shadow' },
                { property: 'opacity', type: 'slider', min: 0, max: 1, step: 0.01 },
            ]
        },
        {
            name: 'Position', open: false,
            properties: [
                { property: 'position', type: 'select', list: [
                    { value: 'static', name: 'Static' }, { value: 'relative', name: 'Relative' },
                    { value: 'absolute', name: 'Absolute' }, { value: 'fixed', name: 'Fixed' },
                    { value: 'sticky', name: 'Sticky' },
                ]},
                { property: 'top',    type: 'integer', units: ['px','%'], unit: 'px' },
                { property: 'right',  type: 'integer', units: ['px','%'], unit: 'px' },
                { property: 'bottom', type: 'integer', units: ['px','%'], unit: 'px' },
                { property: 'left',   type: 'integer', units: ['px','%'], unit: 'px' },
                { property: 'z-index', type: 'integer', units: [''], unit: '' },
                { property: 'overflow', type: 'select', list: [
                    { value: 'visible', name: 'Visible' }, { value: 'hidden', name: 'Hidden' },
                    { value: 'scroll', name: 'Scroll' }, { value: 'auto', name: 'Auto' },
                ]},
            ]
        },
    ];
}
</script>
</body>
</html>
