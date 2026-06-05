<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Builder — {{ $page->name }}</title>
    <link rel="stylesheet" href="https://unpkg.com/grapesjs@0.21.13/dist/css/grapes.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --gjs-primary-color: #000000;
        }

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
            flex: 1; background: transparent; border: none; color: #cbd5e1;
            padding: 10px 0; cursor: pointer;
            font-size: 12px; font-weight: 500;
            border-bottom: 2px solid transparent;
            transition: all .15s; letter-spacing: .02em;
        }
        .p-tab.active { color: #c4b5fd; border-bottom-color: #818cf8; }
        .p-tab:hover:not(.active) { color: #f8fafc; }

        .p-body { flex: 1; overflow-y: auto; overflow-x: hidden; }
        .p-pane { display: none; }
        .p-pane.active { display: block; }

        /* ─── SCROLLBAR ─── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }

        /* ─── BLOCK MANAGER ─── */
        #blocks-wrap,
        #blocks-wrap .gjs-blocks-cs,
        #blocks-wrap .gjs-one-bg,
        #blocks-wrap .gjs-block-categories,
        #blocks-wrap .gjs-block-category {
            background-color: var(--gjs-primary-color) !important;
        }
        #blocks-wrap .gjs-block-categories { padding: 0; }
        #blocks-wrap .gjs-block-category {
            border-bottom: 1px solid #1e293b !important;
        }
        #blocks-wrap .gjs-block-category .gjs-title {
            background: #0f172a !important;
            color: #cbd5e1 !important;
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
            color: #cbd5e1 !important; flex-shrink: 0 !important;
        }
        #blocks-wrap .gjs-blocks-c {
            display: none !important;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            padding: 0;
        }
        #blocks-wrap .gjs-open .gjs-blocks-c {
            display: grid !important;
            padding: 8px;
            background-color: var(--gjs-primary-color) !important;
        }
        #blocks-wrap .gjs-open .gjs-blocks-c:empty {
            display: none !important;
            padding: 0 !important;
        }
        #blocks-wrap .gjs-block {
            width: auto !important;
            height: auto !important;
            min-height: 60px !important;
            background-color: var(--gjs-primary-color) !important;
            border: 1px solid #1e293b !important;
            border-radius: 8px !important;
            color: #e2e8f0 !important;
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
            background: #1e293b !important;
            border-color: #818cf8 !important;
            color: #fff !important;
        }
        #blocks-wrap .gjs-block:active { cursor: grabbing !important; }
        #blocks-wrap .gjs-block .gjs-block-label { font-size: 11px !important; }
        #blocks-wrap .gjs-block svg { width: 20px; height: 20px; }
        #blocks-wrap .fa { font-size: 20px; }

        /* ─── LAYER MANAGER ─── */
        #layers-wrap .gjs-layer {
            background: transparent !important;
            border-color: #1e293b !important;
            color: #e2e8f0 !important;
        }
        #layers-wrap .gjs-layer:hover { background: #1e293b !important; }
        #layers-wrap .gjs-layer.gjs-selected { background: #1e293b !important; color: #fff !important; }
        #layers-wrap .gjs-layer-title-c { color: #e2e8f0 !important; }
        #layers-wrap .gjs-layer-count { color: #94a3b8 !important; }

        /* ─── STYLE MANAGER ─── */
        #styles-wrap .gjs-sm-sector {
            background: transparent !important;
            border-color: #1e293b !important;
        }
        #styles-wrap .gjs-sm-sector-title {
            background: #0f172a !important;
            border-color: #1e293b !important;
            color: #cbd5e1 !important;
            font-size: 10px !important;
            font-weight: 700 !important;
            letter-spacing: .08em !important;
            text-transform: uppercase !important;
            padding: 9px 12px !important;
        }
        #styles-wrap .gjs-sm-properties { padding: 8px 10px !important; gap: 6px !important; }
        #styles-wrap .gjs-sm-property {
            background: transparent !important;
            min-width: 0 !important;
            max-width: 100% !important;
        }
        #styles-wrap .gjs-sm-label { color: #e2e8f0 !important; font-size: 11px !important; margin-bottom: 3px !important; }
        #styles-wrap .gjs-field,
        #styles-wrap .gjs-field input,
        #styles-wrap .gjs-field select,
        #styles-wrap .gjs-select {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
            border-radius: 5px !important;
            font-size: 12px !important;
        }
        #styles-wrap .gjs-field:focus-within { border-color: #818cf8 !important; }
        #styles-wrap .gjs-sm-radio input:checked + .gjs-sm-radio-item { background: #4f46e5 !important; color: #fff !important; }
        #styles-wrap .gjs-sm-radio-item { background: #1e293b !important; color: #cbd5e1 !important; border-color: #334155 !important; }
        #styles-wrap .gjs-clm-tags-btn,
        #styles-wrap .gjs-field-color-picker { border-color: #334155 !important; }
        #styles-wrap .gjs-sm-units { background: #1e293b !important; border-color: #334155 !important; color: #cbd5e1 !important; }

        /* ─── TRAIT MANAGER ─── */
        #traits-wrap .gjs-trt-trait {
            border-color: #1e293b !important;
            padding: 6px 10px !important;
        }
        #traits-wrap .gjs-trt-trait__label { color: #e2e8f0 !important; font-size: 11px !important; }
        #traits-wrap .gjs-trt-trait__wrp input,
        #traits-wrap .gjs-trt-trait__wrp select,
        #traits-wrap .gjs-trt-trait__wrp textarea {
            background: #1e293b !important;
            border: 1px solid #334155 !important;
            color: #f8fafc !important;
            border-radius: 5px !important;
            padding: 5px 8px !important;
            font-size: 12px !important;
            width: 100% !important;
        }
        #traits-wrap .gjs-trt-trait__wrp textarea.editor-content-field {
            min-height: 76px !important;
            line-height: 1.45 !important;
            resize: vertical !important;
        }
        #traits-wrap .gjs-trt-trait__wrp input.editor-content-field:focus,
        #traits-wrap .gjs-trt-trait__wrp textarea.editor-content-field:focus {
            border-color: #818cf8 !important;
            outline: none !important;
        }
        #traits-wrap .gjs-trt-trait--editor-upload-picker .gjs-field,
        #traits-wrap .gjs-trt-trait--editor-upload-picker .gjs-field-wrp {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }
        #styles-wrap .gjs-sm-property__background-image .gjs-field {
            display: flex !important;
            align-items: stretch !important;
            gap: 4px !important;
            padding-right: 4px !important;
        }
        #styles-wrap .gjs-sm-property__background-image input {
            min-width: 0 !important;
            flex: 1 1 auto !important;
        }
        .bg-image-pick-upload {
            width: 26px;
            height: 26px;
            border: 1px solid #334155;
            border-radius: 5px;
            background: #1e293b;
            color: #cbd5e1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            cursor: pointer;
            padding: 0;
            transition: all .15s;
        }
        .bg-image-pick-upload:hover {
            border-color: #818cf8;
            background: #4f46e5;
            color: #fff;
        }
        .bg-image-pick-upload svg {
            width: 14px;
            height: 14px;
        }
        .upload-picker-field {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            min-width: 0;
        }
        .upload-picker-trigger {
            width: 32px;
            height: 30px;
            flex-shrink: 0;
            border: 1px solid #334155;
            border-radius: 6px;
            background: #1e293b;
            color: #cbd5e1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all .15s;
        }
        .upload-picker-trigger:hover,
        .upload-picker-trigger.has-value {
            border-color: #818cf8;
            background: #312e81;
            color: #fff;
        }
        .upload-picker-trigger svg {
            width: 16px;
            height: 16px;
        }
        .upload-picker-current {
            min-width: 0;
            color: #94a3b8;
            font-size: 11px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        #media-picker-overlay {
            position: fixed;
            inset: 0;
            z-index: 99996;
            background: rgba(2, 6, 23, .72);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        #media-picker-overlay.open { display: flex; }
        #media-picker-modal {
            width: min(820px, 100%);
            max-height: min(720px, calc(100vh - 48px));
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 12px;
            box-shadow: 0 24px 70px rgba(0,0,0,.62);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .mp-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 18px;
            border-bottom: 1px solid #1e293b;
        }
        #media-picker-title {
            color: #f8fafc;
            font-size: 14px;
            font-weight: 700;
            margin: 0;
        }
        #media-picker-close {
            width: 30px;
            height: 30px;
            border: 1px solid #334155;
            border-radius: 6px;
            background: #1e293b;
            color: #cbd5e1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        #media-picker-close:hover {
            border-color: #818cf8;
            color: #fff;
        }
        #media-picker-close svg {
            width: 15px;
            height: 15px;
        }
        .mp-controls {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            border-bottom: 1px solid #1e293b;
        }
        #media-picker-search {
            flex: 1 1 260px;
            min-width: 0;
            background: #1e293b;
            border: 1px solid #334155;
            color: #f8fafc;
            border-radius: 7px;
            padding: 8px 10px;
            font-size: 12px;
            outline: none;
        }
        #media-picker-search::placeholder { color: #64748b; }
        #media-picker-search:focus { border-color: #818cf8; }
        #media-picker-extension-filters {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }
        .mp-filter-label {
            color: #94a3b8;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        .mp-filter-option {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #cbd5e1;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }
        .mp-filter-option input {
            width: 13px;
            height: 13px;
            accent-color: #4f46e5;
        }
        #media-picker-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
            padding: 18px;
            overflow-y: auto;
            min-height: 220px;
        }
        .mp-card {
            min-width: 0;
            border: 1px solid #334155;
            border-radius: 8px;
            background: #111827;
            color: inherit;
            text-align: left;
            overflow: hidden;
            cursor: pointer;
            padding: 0;
            transition: border-color .15s, transform .15s, box-shadow .15s;
        }
        .mp-card:hover,
        .mp-card.selected {
            border-color: #818cf8;
            box-shadow: 0 0 0 1px #818cf8;
        }
        .mp-card:active { transform: translateY(1px); }
        .mp-preview {
            position: relative;
            aspect-ratio: 4 / 3;
            background: #020617;
        }
        .mp-preview img,
        .mp-preview video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .mp-type-badge {
            position: absolute;
            right: 7px;
            bottom: 7px;
            border-radius: 4px;
            background: rgba(2, 6, 23, .78);
            color: #fff;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .mp-meta {
            padding: 8px 9px;
            min-width: 0;
        }
        .mp-name {
            color: #f8fafc;
            font-size: 11px;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .mp-size {
            margin-top: 2px;
            color: #64748b;
            font-size: 10px;
        }
        #media-picker-empty {
            display: none;
            padding: 42px 18px;
            color: #94a3b8;
            text-align: center;
            font-size: 13px;
        }
        #media-picker-empty.visible { display: block; }
        #media-picker-gallery.empty { display: none; }

        /* ─── EMPTY STATE ─── */
        .empty-panel {
            padding: 32px 16px;
            text-align: center;
            color: #94a3b8;
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
            color: #f8fafc;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            outline: none;
        }
        .search-wrap input::placeholder { color: #94a3b8; }
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
        #gi-wrap {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            flex: 1 1 100%;
            overflow: hidden;
        }
        #gi-wrap, #gi-wrap * { box-sizing: border-box; }
        .gi-sep {
            border-top: 1px solid #1e293b;
            margin: 6px 0 2px;
        }
        .gi-row {
            display: flex; align-items: center; gap: 8px;
            padding: 3px 0;
            width: 100%;
            max-width: 100%;
            min-width: 0;
        }
        .gi-label {
            font-size: 11px; color: #e2e8f0;
            width: 82px; flex-shrink: 0;
        }
        .gi-row select, .gi-row input[type=range] {
            flex: 1 1 0; background: #1e293b; border: 1px solid #334155;
            color: #f8fafc; border-radius: 5px; padding: 4px 6px;
            font-size: 11px; outline: none; min-width: 0; width: 0; max-width: 100%;
        }
        .gi-row select:focus { border-color: #818cf8; }
        .gi-colors { display: flex; gap: 6px; flex: 1 1 0; min-width: 0; max-width: 100%; }
        .gi-color-wrap {
            flex: 1 1 0; min-width: 0; display: flex; flex-direction: column; align-items: center; gap: 3px;
        }
        .gi-color-wrap span { font-size: 10px; color: #94a3b8; }
        .gi-row input[type=color] {
            width: 100%; height: 28px; border-radius: 5px;
            border: 1px solid #334155; background: #1e293b;
            cursor: pointer; padding: 2px;
        }
        .gi-opacity-val {
            font-size: 11px; color: #cbd5e1; width: 30px; text-align: right; flex-shrink: 0;
        }
        #pa-wrap {
            width: 100%;
            flex: 1 1 100%;
            border-top: 1px solid #1e293b;
            margin-top: 8px;
            padding-top: 8px;
        }
        .pa-row {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
        }
        .pa-label {
            width: 82px;
            flex-shrink: 0;
            color: #e2e8f0;
            font-size: 11px;
        }
        .pa-buttons {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 4px;
            flex: 1;
            min-width: 0;
        }
        .pa-btn {
            height: 28px;
            border: 1px solid #334155;
            border-radius: 5px;
            background: #1e293b;
            color: #cbd5e1;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            transition: all .15s;
        }
        .pa-btn:hover,
        .pa-btn.active {
            background: #4f46e5;
            border-color: #818cf8;
            color: #fff;
        }
        .pa-btn svg {
            width: 15px;
            height: 15px;
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
            Save &amp; Publish
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

<div id="media-picker-overlay" aria-hidden="true">
    <div id="media-picker-modal" role="dialog" aria-modal="true" aria-labelledby="media-picker-title">
        <div class="mp-header">
            <h2 id="media-picker-title">Choose Upload</h2>
            <button type="button" id="media-picker-close" aria-label="Close media picker">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="mp-controls">
            <input type="text" id="media-picker-search" placeholder="Search uploads...">
            <div id="media-picker-extension-filters"></div>
        </div>
        <div id="media-picker-gallery"></div>
        <div id="media-picker-empty">No uploads match.</div>
    </div>
</div>

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
const SAVED_DATA = @json($page->draft_builder_data ?? $page->builder_data);
const UPLOADED_MEDIA = @json($uploadedMedia ?? []);

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

// Content fields in the Settings tab
const CONTENT_TRAIT_PREFIX = '__editor_content_';
const TEXT_SETTING_TAGS = new Set(['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'cite', 'li', 'label', 'button', 'a']);

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function childComponents(component) {
    const components = component && component.components ? component.components() : null;
    return components ? components.models : [];
}

function getTagName(component) {
    return String(component?.get?.('tagName') || '').toLowerCase();
}

function getComponentText(component) {
    const el = component?.getEl?.();
    if (el) return el.textContent.replace(/\u00a0/g, ' ').trim();

    const content = component?.get?.('content');
    if (content) return String(content).trim();

    return childComponents(component).map(getComponentText).join('').trim();
}

function setComponentText(component, value) {
    if (!component) return;

    if (component.components) {
        component.components(escapeHtml(value));
    } else {
        component.set?.('content', value);
    }

    component.view?.render?.();
}

function walkComponents(component, callback) {
    childComponents(component).forEach(child => {
        callback(child);
        walkComponents(child, callback);
    });
}

function findFirstDescendant(component, predicate) {
    let found = null;
    walkComponents(component, child => {
        if (!found && predicate(child)) found = child;
    });
    return found;
}

function findBenefitParts(component) {
    if (getTagName(component) !== 'div') return null;

    const spans = childComponents(component).filter(child => getTagName(child) === 'span');
    if (spans.length < 2) return null;

    const iconText = getComponentText(spans[0]);
    const labelText = getComponentText(spans[1]);
    const styles = component.getStyle ? component.getStyle() : {};
    const isFlex = String(styles.display || '').toLowerCase().includes('flex');
    const hasShortIcon = iconText.length > 0 && iconText.length <= 3;

    if (!isFlex || !hasShortIcon || !labelText) return null;

    return { icon: spans[0], text: spans[1] };
}

function findBenefitCards(component) {
    const cards = [];
    walkComponents(component, child => {
        const parts = findBenefitParts(child);
        if (parts) cards.push({ component: child, ...parts });
    });
    return cards;
}

function findWhyChooseParts(component) {
    const benefitCards = findBenefitCards(component);
    if (benefitCards.length < 2) return null;

    const heading = findFirstDescendant(component, child => /^h[1-6]$/.test(getTagName(child)));
    const subheading = findFirstDescendant(component, child => getTagName(child) === 'p');

    return { heading, subheading, benefits: benefitCards };
}

function getEditableAttributeValue(component, name) {
    const attributeValue = component?.getAttributes?.()[name];
    if (attributeValue !== undefined && attributeValue !== null && attributeValue !== '') {
        return String(attributeValue);
    }

    const componentValue = component?.get?.(name);
    if (componentValue !== undefined && componentValue !== null && componentValue !== '') {
        return String(componentValue);
    }

    return '';
}

function updateEditableAttribute(component, name, value) {
    component.addAttributes({ [name]: value });

    if (['src', 'poster'].includes(name)) {
        component.set?.(name, value);
        const el = component.getEl?.();
        if (el) {
            if (value) el.setAttribute(name, value);
            else el.removeAttribute(name);
            if (name === 'src' && el.tagName?.toLowerCase() === 'video') el.load?.();
        }
    }

    component.view?.render?.();
}

function markEditorFieldInput(input, field) {
    input.dataset.editorFieldKind = field?.kind || '';
    input.dataset.editorFieldName = field?.name || '';
}

function syncEditorFieldInputs(field, value) {
    if (!field?.kind) return;

    document.querySelectorAll('#traits-wrap .editor-content-field').forEach(input => {
        if (input.dataset.editorFieldKind !== field.kind || input.dataset.editorFieldName !== (field.name || '')) return;
        if (input.classList.contains('upload-picker-field')) {
            updateUploadPickerTrigger(input, value, input.dataset.uploadKind || 'image');
        } else if (input.tagName?.toLowerCase() === 'select') {
            input.value = Array.from(input.options).some(option => option.value === value) ? value : '';
        } else {
            input.value = value;
        }
    });
}

function getEditableFieldValue(component, field) {
    if (!component || !field) return '';

    if (field.kind === 'text') return getComponentText(component);
    if (field.kind === 'attr') return getEditableAttributeValue(component, field.name);

    if (field.kind === 'benefit-icon' || field.kind === 'benefit-text') {
        const parts = findBenefitParts(component);
        if (!parts) return '';
        return getComponentText(field.kind === 'benefit-icon' ? parts.icon : parts.text);
    }

    if (field.kind === 'why-heading') {
        return getComponentText(findWhyChooseParts(component)?.heading);
    }

    if (field.kind === 'why-subheading') {
        return getComponentText(findWhyChooseParts(component)?.subheading);
    }

    if (field.kind === 'why-benefit') {
        const benefit = findWhyChooseParts(component)?.benefits[field.index];
        return getComponentText(benefit?.text);
    }

    return '';
}

function updateEditableField(component, field, value) {
    if (!component || !field) return;

    if (field.kind === 'text') {
        setComponentText(component, value);
        return;
    }

    if (field.kind === 'attr') {
        updateEditableAttribute(component, field.name, value);
        syncEditorFieldInputs(field, value);
        return;
    }

    if (field.kind === 'benefit-icon' || field.kind === 'benefit-text') {
        const parts = findBenefitParts(component);
        if (parts) setComponentText(field.kind === 'benefit-icon' ? parts.icon : parts.text, value);
        return;
    }

    if (field.kind === 'why-heading') {
        setComponentText(findWhyChooseParts(component)?.heading, value);
        return;
    }

    if (field.kind === 'why-subheading') {
        setComponentText(findWhyChooseParts(component)?.subheading, value);
        return;
    }

    if (field.kind === 'why-benefit') {
        const benefit = findWhyChooseParts(component)?.benefits[field.index];
        if (benefit) setComponentText(benefit.text, value);
    }
}

function registerContentTrait(type, tagName) {
    editor.TraitManager.addType(type, {
        createInput({ trait }) {
            const input = document.createElement(tagName);
            input.className = 'editor-content-field';
            markEditorFieldInput(input, trait.get('editorField'));
            input.placeholder = trait.get('placeholder') || '';
            input.addEventListener('input', () => updateEditableField(editor.getSelected(), trait.get('editorField'), input.value));
            return input;
        },
        onEvent({ elInput, component, trait }) {
            updateEditableField(component, trait.get('editorField'), elInput.value);
        },
        onUpdate({ elInput, component, trait }) {
            elInput.value = getEditableFieldValue(component, trait.get('editorField'));
        },
    });
}

registerContentTrait('editor-content-input', 'input');
registerContentTrait('editor-content-textarea', 'textarea');

function uploadedMediaForKind(kind) {
    return UPLOADED_MEDIA.filter(item => item.type === kind);
}

function mediaExtension(item) {
    const name = String(item?.name || item?.url || '').toLowerCase();
    const parts = name.split('.');
    return parts.length > 1 ? parts.pop() : '';
}

function mediaKindLabel(kind) {
    return kind === 'video' ? 'video' : 'image';
}

function formatUploadSize(bytes) {
    const size = Number(bytes) || 0;
    if (size >= 1024 * 1024) return `${(size / 1024 / 1024).toFixed(1).replace(/\.0$/, '')} MB`;
    if (size >= 1024) return `${(size / 1024).toFixed(1).replace(/\.0$/, '')} KB`;
    return size ? `${size} B` : '';
}

function uploadedMediaForUrl(value, kind = '') {
    return UPLOADED_MEDIA.find(item => item.url === value && (!kind || item.type === kind))
        || UPLOADED_MEDIA.find(item => item.url === value)
        || null;
}

function filenameFromUrl(value) {
    if (!value) return '';

    try {
        return decodeURIComponent(new URL(value, window.location.origin).pathname.split('/').filter(Boolean).pop() || value);
    } catch {
        return String(value).split('/').filter(Boolean).pop() || String(value);
    }
}

function updateUploadPickerTrigger(wrapper, value, kind = 'image') {
    const item = uploadedMediaForUrl(value, kind);
    const label = mediaKindLabel(kind);
    const displayName = item?.name || filenameFromUrl(value) || `Choose ${label}`;
    const button = wrapper.querySelector('.upload-picker-trigger');
    const current = wrapper.querySelector('.upload-picker-current');

    wrapper.dataset.currentValue = value || '';
    if (current) current.textContent = value ? displayName : 'No upload selected';
    if (button) {
        button.classList.toggle('has-value', Boolean(value));
        button.title = value ? `Change uploaded ${label}: ${displayName}` : `Choose uploaded ${label}`;
        button.setAttribute('aria-label', button.title);
    }
}

const mediaPicker = {
    active: null,
    hiddenExtensions: new Set(),
    overlay: document.getElementById('media-picker-overlay'),
    title: document.getElementById('media-picker-title'),
    close: document.getElementById('media-picker-close'),
    search: document.getElementById('media-picker-search'),
    filters: document.getElementById('media-picker-extension-filters'),
    gallery: document.getElementById('media-picker-gallery'),
    empty: document.getElementById('media-picker-empty'),
};

function renderMediaPickerFilters(kind) {
    const extensions = Array.from(new Set(uploadedMediaForKind(kind).map(mediaExtension).filter(Boolean))).sort();
    mediaPicker.filters.innerHTML = '';

    if (!extensions.length) return;

    const label = document.createElement('span');
    label.className = 'mp-filter-label';
    label.textContent = 'Hide';
    mediaPicker.filters.appendChild(label);

    extensions.forEach(extension => {
        const filterLabel = document.createElement('label');
        filterLabel.className = 'mp-filter-option';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.value = extension;
        checkbox.checked = mediaPicker.hiddenExtensions.has(extension);
        checkbox.addEventListener('change', () => {
            if (checkbox.checked) mediaPicker.hiddenExtensions.add(extension);
            else mediaPicker.hiddenExtensions.delete(extension);
            renderMediaPickerGallery();
        });

        const text = document.createElement('span');
        text.textContent = extension.toUpperCase();

        filterLabel.append(checkbox, text);
        mediaPicker.filters.appendChild(filterLabel);
    });
}

function mediaMatchesPicker(item) {
    const query = mediaPicker.search.value.trim().toLowerCase();
    const extension = mediaExtension(item);
    if (mediaPicker.hiddenExtensions.has(extension)) return false;
    if (!query) return true;

    return String(item.name || '').toLowerCase().includes(query)
        || extension.includes(query);
}

function buildMediaPreview(item) {
    const preview = document.createElement('div');
    preview.className = 'mp-preview';

    const mediaEl = document.createElement(item.type === 'video' ? 'video' : 'img');
    mediaEl.src = item.url;
    if (item.type === 'video') {
        mediaEl.muted = true;
        mediaEl.playsInline = true;
        mediaEl.preload = 'metadata';
    } else {
        mediaEl.alt = item.name || '';
        mediaEl.loading = 'lazy';
    }
    preview.appendChild(mediaEl);

    if (item.type === 'video') {
        const badge = document.createElement('span');
        badge.className = 'mp-type-badge';
        badge.textContent = 'Video';
        preview.appendChild(badge);
    }

    return preview;
}

function renderMediaPickerGallery() {
    const active = mediaPicker.active;
    if (!active) return;

    const selectedValue = active.getValue ? active.getValue() : getEditableFieldValue(active.component, active.field);
    const items = uploadedMediaForKind(active.kind).filter(mediaMatchesPicker);

    mediaPicker.gallery.innerHTML = '';
    mediaPicker.gallery.classList.toggle('empty', items.length === 0);
    mediaPicker.empty.classList.toggle('visible', items.length === 0);

    items.forEach(item => {
        const card = document.createElement('button');
        card.type = 'button';
        card.className = 'mp-card';
        card.classList.toggle('selected', item.url === selectedValue);
        card.title = item.name || '';

        const meta = document.createElement('div');
        meta.className = 'mp-meta';

        const name = document.createElement('div');
        name.className = 'mp-name';
        name.textContent = item.name || filenameFromUrl(item.url);

        const size = document.createElement('div');
        size.className = 'mp-size';
        size.textContent = [mediaExtension(item).toUpperCase(), formatUploadSize(item.size)].filter(Boolean).join(' - ');

        meta.append(name, size);
        card.append(buildMediaPreview(item), meta);
        card.addEventListener('click', () => {
            if (active.onChoose) {
                active.onChoose(item);
            } else {
                updateEditableField(active.component || editor.getSelected(), active.field, item.url);
                updateUploadPickerTrigger(active.wrapper, item.url, active.kind);
            }
            closeMediaPicker();
        });

        mediaPicker.gallery.appendChild(card);
    });
}

function openMediaPicker(trait, wrapper) {
    const kind = trait.get('uploadKind') || 'image';
    const component = editor.getSelected();
    const label = mediaKindLabel(kind);

    mediaPicker.active = {
        component,
        field: trait.get('editorField'),
        kind,
        wrapper,
    };
    mediaPicker.hiddenExtensions = new Set();
    mediaPicker.search.value = '';
    mediaPicker.title.textContent = `Choose Uploaded ${label.charAt(0).toUpperCase()}${label.slice(1)}`;
    renderMediaPickerFilters(kind);
    renderMediaPickerGallery();
    mediaPicker.overlay.classList.add('open');
    mediaPicker.overlay.setAttribute('aria-hidden', 'false');
    setTimeout(() => mediaPicker.search.focus(), 30);
}

function closeMediaPicker() {
    mediaPicker.overlay.classList.remove('open');
    mediaPicker.overlay.setAttribute('aria-hidden', 'true');
    mediaPicker.active = null;
}

mediaPicker.close.addEventListener('click', closeMediaPicker);
mediaPicker.overlay.addEventListener('click', event => {
    if (event.target === mediaPicker.overlay) closeMediaPicker();
});
mediaPicker.search.addEventListener('input', renderMediaPickerGallery);
document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && mediaPicker.overlay.classList.contains('open')) closeMediaPicker();
});

const EMPTY_BACKGROUND_IMAGE_VALUE = "url('')";

function cssUrlForImage(url) {
    return `url('${String(url || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'")}')`;
}

function getCssUrlValue(value) {
    const match = String(value || '').match(/url\(\s*(['"]?)(.*?)\1\s*\)/i);
    return match ? match[2] : '';
}

function isCssImageValue(value) {
    const text = String(value || '').trim().toLowerCase();
    return text === '' || text === 'none' || text.includes('gradient(') || text.startsWith('url(');
}

function backgroundImagePropertyEl() {
    return document.querySelector('#styles-wrap .gjs-sm-property__background-image')
        || [...document.querySelectorAll('#styles-wrap .gjs-sm-property')].find(property => {
            const label = property.querySelector('.gjs-sm-label')?.textContent.trim().toLowerCase();
            return label === 'background image';
        });
}

function backgroundImageInputEl() {
    return backgroundImagePropertyEl()?.querySelector('input, textarea') || null;
}

function selectedBackgroundImageValue() {
    return String(editor.getSelected()?.getStyle?.()?.['background-image'] || '');
}

function syncBackgroundImageInput() {
    const input = backgroundImageInputEl();
    if (!input) return;

    input.placeholder = EMPTY_BACKGROUND_IMAGE_VALUE;

    const styleValue = selectedBackgroundImageValue();
    if (styleValue && document.activeElement !== input) {
        input.value = styleValue;
        return;
    }

    if (!input.value.trim()) {
        input.value = EMPTY_BACKGROUND_IMAGE_VALUE;
    }
}

function selectBackgroundUrlSlot(input) {
    const value = input.value || EMPTY_BACKGROUND_IMAGE_VALUE;
    const start = value.indexOf("'") + 1;
    const end = value.lastIndexOf("'");
    if (start > 0 && end >= start && typeof input.setSelectionRange === 'function') {
        input.setSelectionRange(start, end);
    }
}

function normalizeBackgroundImageInput(input, commit = false) {
    const value = input.value.trim();
    if (!value || value === EMPTY_BACKGROUND_IMAGE_VALUE) {
        if (!value) input.value = EMPTY_BACKGROUND_IMAGE_VALUE;
        return;
    }

    if (isCssImageValue(value)) return;

    const normalized = cssUrlForImage(value);
    input.value = normalized;

    if (commit) {
        editor.getSelected()?.addStyle({ 'background-image': normalized });
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }
}

function applyBackgroundImageUrl(url) {
    const component = editor.getSelected();
    if (!component) return;

    const value = cssUrlForImage(url);
    component.addStyle({ 'background-image': value });

    const input = backgroundImageInputEl();
    if (input) {
        input.value = value;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    showToast('Background image selected.');
}

function openBackgroundImagePicker() {
    const component = editor.getSelected();
    if (!component) {
        showToast('Select an element first.', 'err');
        return;
    }

    mediaPicker.active = {
        component,
        kind: 'image',
        getValue: () => getCssUrlValue(selectedBackgroundImageValue()),
        onChoose: item => applyBackgroundImageUrl(item.url),
    };
    mediaPicker.hiddenExtensions = new Set();
    mediaPicker.search.value = '';
    mediaPicker.title.textContent = 'Choose Uploaded Background Image';
    renderMediaPickerFilters('image');
    renderMediaPickerGallery();
    mediaPicker.overlay.classList.add('open');
    mediaPicker.overlay.setAttribute('aria-hidden', 'false');
    setTimeout(() => mediaPicker.search.focus(), 30);
}

function injectBackgroundImagePicker() {
    const property = backgroundImagePropertyEl();
    const input = backgroundImageInputEl();
    if (!property || !input) return;

    if (!input.dataset.bgImageReady) {
        input.dataset.bgImageReady = 'true';
        input.addEventListener('focus', () => {
            if (!input.value.trim()) input.value = EMPTY_BACKGROUND_IMAGE_VALUE;
            requestAnimationFrame(() => selectBackgroundUrlSlot(input));
        });
        input.addEventListener('blur', () => normalizeBackgroundImageInput(input, true));
        input.addEventListener('change', () => normalizeBackgroundImageInput(input, true));
    }

    const field = property.querySelector('.gjs-field') || property;
    if (!field.querySelector('.bg-image-pick-upload')) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'bg-image-pick-upload';
        button.title = 'Choose from uploads';
        button.setAttribute('aria-label', 'Choose background image from uploads');
        button.innerHTML = '<svg fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>';
        button.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();
            openBackgroundImagePicker();
        });
        field.appendChild(button);
    }

    syncBackgroundImageInput();
}

function scheduleBackgroundImagePickerInjection() {
    setTimeout(injectBackgroundImagePicker, 0);
    setTimeout(injectBackgroundImagePicker, 150);
}

editor.on('load', scheduleBackgroundImagePickerInjection);
editor.on('component:selected', scheduleBackgroundImagePickerInjection);
editor.on('component:styleUpdate', scheduleBackgroundImagePickerInjection);

editor.TraitManager.addType('editor-upload-picker', {
    createInput({ trait }) {
        const wrapper = document.createElement('div');
        wrapper.className = 'editor-content-field upload-picker-field';
        wrapper.dataset.uploadKind = trait.get('uploadKind') || 'image';
        markEditorFieldInput(wrapper, trait.get('editorField'));

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'upload-picker-trigger';
        button.innerHTML = '<svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>';
        button.addEventListener('click', () => openMediaPicker(trait, wrapper));

        const current = document.createElement('span');
        current.className = 'upload-picker-current';

        wrapper.append(button, current);
        updateUploadPickerTrigger(wrapper, getEditableFieldValue(editor.getSelected(), trait.get('editorField')), wrapper.dataset.uploadKind);

        return wrapper;
    },
    onUpdate({ elInput, component, trait }) {
        updateUploadPickerTrigger(elInput, getEditableFieldValue(component, trait.get('editorField')), trait.get('uploadKind') || 'image');
    },
});

function contentTrait(name, label, field, options = {}) {
    return {
        type: options.type || 'editor-content-input',
        name: CONTENT_TRAIT_PREFIX + name,
        label,
        editorField: field,
        placeholder: options.placeholder || '',
        uploadKind: options.uploadKind || '',
    };
}

function getSerializableTraits(component) {
    const traits = typeof component.getTraits === 'function'
        ? component.getTraits()
        : (component.get('traits')?.models || []);

    return traits
        .map(trait => ({ ...(trait.attributes || trait) }))
        .filter(trait => !String(trait.name || '').startsWith(CONTENT_TRAIT_PREFIX));
}

function setComponentTraits(component, traits) {
    if (typeof component.setTraits === 'function') {
        component.setTraits(traits);
    } else if (component.get('traits')) {
        component.get('traits').reset(traits);
    } else {
        component.set('traits', traits);
    }
}

function buildContentTraits(component) {
    const tag = getTagName(component);
    const traits = [];
    const existingTraitNames = new Set(getSerializableTraits(component).map(trait => trait.name));
    const whyChoose = findWhyChooseParts(component);
    const benefitParts = findBenefitParts(component);
    const isTextElement = TEXT_SETTING_TAGS.has(tag) || component.get?.('type') === 'text';
    const hasTrait = name => existingTraitNames.has(name);

    if (whyChoose) {
        if (whyChoose.heading) {
            traits.push(contentTrait('why_heading', 'Heading', { kind: 'why-heading' }, {
                type: 'editor-content-textarea',
                placeholder: 'Section heading',
            }));
        }
        if (whyChoose.subheading) {
            traits.push(contentTrait('why_subheading', 'Subheading', { kind: 'why-subheading' }, {
                type: 'editor-content-textarea',
                placeholder: 'Section subheading',
            }));
        }
        whyChoose.benefits.forEach((benefit, index) => {
            traits.push(contentTrait(`why_benefit_${index}`, `Benefit ${index + 1}`, { kind: 'why-benefit', index }, {
                type: 'editor-content-textarea',
                placeholder: 'Benefit text',
            }));
        });
    } else if (benefitParts) {
        traits.push(contentTrait('benefit_icon', 'Icon', { kind: 'benefit-icon' }, { placeholder: 'Icon' }));
        traits.push(contentTrait('benefit_text', 'Benefit', { kind: 'benefit-text' }, {
            type: 'editor-content-textarea',
            placeholder: 'Benefit text',
        }));
    } else if (isTextElement) {
        traits.push(contentTrait('text', 'Text', { kind: 'text' }, {
            type: 'editor-content-textarea',
            placeholder: 'Text',
        }));
    }

    if (tag === 'a' && !hasTrait('href')) {
        traits.push(contentTrait('href', 'Link URL', { kind: 'attr', name: 'href' }, { placeholder: 'https://example.com' }));
    }

    if (tag === 'img' || component.get?.('type') === 'image') {
        traits.push(contentTrait('uploaded_image_src', 'Uploaded Image', { kind: 'attr', name: 'src' }, {
            type: 'editor-upload-picker',
            uploadKind: 'image',
        }));
        if (!hasTrait('src')) {
            traits.push(contentTrait('src', 'Image URL', { kind: 'attr', name: 'src' }, { placeholder: 'https://example.com/image.jpg' }));
        }
        if (!hasTrait('alt')) {
            traits.push(contentTrait('alt', 'Alt Text', { kind: 'attr', name: 'alt' }, {
                type: 'editor-content-textarea',
                placeholder: 'Describe the image',
            }));
        }
    }

    if (tag === 'video' || component.get?.('type') === 'video') {
        traits.push(contentTrait('uploaded_video_src', 'Uploaded Video', { kind: 'attr', name: 'src' }, {
            type: 'editor-upload-picker',
            uploadKind: 'video',
        }));
        if (!hasTrait('src')) {
            traits.push(contentTrait('src', 'Video URL', { kind: 'attr', name: 'src' }, { placeholder: 'https://example.com/video.mp4' }));
        }
        traits.push(contentTrait('uploaded_video_poster', 'Uploaded Poster', { kind: 'attr', name: 'poster' }, {
            type: 'editor-upload-picker',
            uploadKind: 'image',
        }));
        if (!hasTrait('poster')) {
            traits.push(contentTrait('poster', 'Poster URL', { kind: 'attr', name: 'poster' }, { placeholder: 'https://example.com/poster.jpg' }));
        }
    }

    if (tag === 'input' || tag === 'textarea') {
        if (!hasTrait('placeholder')) {
            traits.push(contentTrait('placeholder', 'Placeholder', { kind: 'attr', name: 'placeholder' }, { placeholder: 'Placeholder text' }));
        }
        if (!hasTrait('value')) {
            traits.push(contentTrait('value', 'Value', { kind: 'attr', name: 'value' }, { placeholder: 'Default value' }));
        }
    }

    return traits;
}

function showRightPane(panelName) {
    const rightPanel = document.getElementById('panel-right');
    const tab = rightPanel.querySelector(`.p-tab[data-panel="${panelName}"]`);
    if (tab && !tab.classList.contains('active')) tab.click();
}

const BACKGROUND_IMAGE_FUNCTIONS = [
    'repeating-linear-gradient',
    'repeating-radial-gradient',
    'linear-gradient',
    'radial-gradient',
    'url',
];
const BACKGROUND_REPEAT_VALUES = ['no-repeat', 'repeat-x', 'repeat-y', 'space', 'round', 'repeat'];
const BACKGROUND_ATTACHMENT_VALUES = ['fixed', 'local', 'scroll'];
const CSS_COLOR_KEYWORDS = new Set([
    'transparent', 'currentcolor', 'black', 'white', 'red', 'green', 'blue',
    'yellow', 'orange', 'purple', 'gray', 'grey', 'silver', 'maroon', 'navy',
    'teal', 'lime', 'aqua', 'fuchsia', 'olive',
]);

function readCssFunction(value, start) {
    let depth = 0;
    let quote = '';

    for (let i = start; i < value.length; i++) {
        const char = value[i];
        if (quote) {
            if (char === '\\') i++;
            else if (char === quote) quote = '';
            continue;
        }

        if (char === '"' || char === "'") {
            quote = char;
        } else if (char === '(') {
            depth++;
        } else if (char === ')') {
            depth--;
            if (depth === 0) {
                return { text: value.slice(start, i + 1), end: i + 1 };
            }
        }
    }

    return { text: value.slice(start), end: value.length };
}

function extractBackgroundImages(background) {
    const images = [];
    let remainder = '';
    const lower = background.toLowerCase();

    for (let i = 0; i < background.length;) {
        const fn = BACKGROUND_IMAGE_FUNCTIONS.find(name => lower.startsWith(`${name}(`, i));
        if (!fn) {
            remainder += background[i];
            i++;
            continue;
        }

        const parsed = readCssFunction(background, i);
        images.push(parsed.text);
        remainder += ' ';
        i = parsed.end;
    }

    return { images, remainder };
}

function splitCssList(value) {
    const parts = [];
    let depth = 0;
    let quote = '';
    let start = 0;

    for (let i = 0; i < value.length; i++) {
        const char = value[i];
        if (quote) {
            if (char === '\\') i++;
            else if (char === quote) quote = '';
            continue;
        }

        if (char === '"' || char === "'") quote = char;
        else if (char === '(') depth++;
        else if (char === ')') depth--;
        else if (char === ',' && depth === 0) {
            parts.push(value.slice(start, i).trim());
            start = i + 1;
        }
    }

    parts.push(value.slice(start).trim());
    return parts.filter(Boolean);
}

function findCssColor(value) {
    const colorFunction = value.match(/\b(?:rgba?|hsla?)\([^)]+\)/i);
    if (colorFunction) return colorFunction[0];

    const hex = value.match(/#[0-9a-f]{3,8}\b/i);
    if (hex) return hex[0];

    const keyword = value
        .replace(/[(),/]/g, ' ')
        .split(/\s+/)
        .find(token => CSS_COLOR_KEYWORDS.has(token.toLowerCase()));

    return keyword || '';
}

function firstGradientColor(images) {
    const gradient = images.find(image => image.toLowerCase().includes('gradient('));
    if (!gradient) return '';

    const body = gradient.slice(gradient.indexOf('(') + 1, gradient.lastIndexOf(')'));
    const stops = splitCssList(body);
    const colorStops = stops.filter((stop, index) => {
        if (index > 0) return true;
        return !/^(to\s|circle\b|ellipse\b|at\s|[-\d.]+(?:deg|rad|turn)\b)/i.test(stop.trim());
    });

    for (const stop of colorStops) {
        const color = findCssColor(stop);
        if (color) return color;
    }

    return '';
}

function normalizeBackgroundPosition(value) {
    const tokens = value.toLowerCase().match(/\b(left|center|right|top|bottom)\b/g) || [];
    if (!tokens.length) return '';
    if (tokens.length === 1) {
        if (tokens[0] === 'top' || tokens[0] === 'bottom') return `${tokens[0]} center`;
        if (tokens[0] === 'left' || tokens[0] === 'right') return `center ${tokens[0]}`;
        return 'center center';
    }

    const vertical = tokens.find(token => token === 'top' || token === 'bottom') || 'center';
    const horizontal = tokens.find(token => token === 'left' || token === 'right') || 'center';
    return `${vertical} ${horizontal}`;
}

function parseBackgroundPlacement(remainder) {
    const text = remainder.replace(/\b(?:rgba?|hsla?)\([^)]+\)/gi, ' ').replace(/#[0-9a-f]{3,8}\b/gi, ' ');
    const repeat = BACKGROUND_REPEAT_VALUES.find(value => text.toLowerCase().includes(value)) || '';
    const attachment = BACKGROUND_ATTACHMENT_VALUES.find(value => text.toLowerCase().includes(value)) || '';
    const ignoredPlacementValues = [...BACKGROUND_REPEAT_VALUES, ...BACKGROUND_ATTACHMENT_VALUES].join('|');
    const sizeMatch = text.match(/\/\s*([^,;]+)/);
    const size = sizeMatch
        ? sizeMatch[1].replace(new RegExp(`\\b(?:${ignoredPlacementValues})\\b`, 'gi'), '').trim()
        : (text.match(/\b(?:cover|contain)\b/i)?.[0] || '');
    const positionSource = sizeMatch ? text.slice(0, sizeMatch.index) : text;
    const position = normalizeBackgroundPosition(positionSource);

    return { repeat, size, position, attachment };
}

function parseBackgroundShorthand(background) {
    if (!background || background === 'none') return {};

    const { images, remainder } = extractBackgroundImages(String(background));
    const placement = parseBackgroundPlacement(remainder);
    const color = findCssColor(remainder) || firstGradientColor(images);
    const styles = {};

    if (color) styles['background-color'] = color;
    if (images.length) styles['background-image'] = images.join(', ');
    if (placement.repeat) styles['background-repeat'] = placement.repeat;
    if (placement.size) styles['background-size'] = placement.size;
    if (placement.position) styles['background-position'] = placement.position;
    if (placement.attachment) styles['background-attachment'] = placement.attachment;

    return styles;
}

function syncBackgroundShorthandToLonghands(component) {
    const styles = component?.getStyle?.() || {};
    const shorthand = styles.background;
    if (!shorthand) return;

    const parsed = parseBackgroundShorthand(shorthand);
    const patch = {};
    Object.entries(parsed).forEach(([property, value]) => {
        if (value && !styles[property]) patch[property] = value;
    });

    if (Object.keys(patch).length) {
        component.addStyle(patch);
    }
}

editor.on('component:selected', component => {
    if (!component) return;

    syncBackgroundShorthandToLonghands(component);

    const contentTraits = buildContentTraits(component);
    setComponentTraits(component, [...getSerializableTraits(component), ...contentTraits]);

    if (contentTraits.length) {
        showRightPane('traits');
    }
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

bm.add('pb-image', {
    label: 'Image', category: 'Elements',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8" cy="10" r="1.5"/><path d="M21 15l-4.5-4.5a2 2 0 00-2.8 0L7 17"/></svg>`,
    content: '<img src="https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?w=1200&q=80" alt="Content image" style="display:block;width:100%;max-width:720px;height:auto;border-radius:12px;object-fit:cover;">',
});

bm.add('pb-video', {
    label: 'Video', category: 'Elements',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M10 9l5 3-5 3V9z"/></svg>`,
    content: '<video src="https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4" controls playsinline style="display:block;width:100%;max-width:720px;border-radius:12px;background:#0f172a;"></video>',
});

// ─── Service Sections blocks
bm.add('pb-service-hero', {
    label: 'Service Hero', category: 'Service Sections',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/><circle cx="12" cy="10" r="2.5"/></svg>`,
    content: `<section style="position:relative;min-height:520px;background:linear-gradient(rgba(10,20,40,0.72),rgba(10,20,40,0.72)),url('https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1600&q=80') center/cover no-repeat;display:flex;align-items:center;justify-content:center;text-align:center;padding:80px 24px;">
  <div style="max-width:760px;margin:0 auto;">
    <div style="display:inline-block;background:rgba(29,107,58,.9);color:#fff;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:6px 18px;border-radius:99px;margin-bottom:20px;">Same-Day Service Available</div>
    <h1 style="font-size:52px;font-weight:800;color:#ffffff;line-height:1.1;margin:0 0 14px;">Your Company Name Here</h1>
    <p style="font-size:20px;color:rgba(255,255,255,.88);margin:0 0 8px;font-weight:500;">Service Type A, Service Type B &amp; Service Type C</p>
    <p style="font-size:15px;color:rgba(255,255,255,.65);margin:0 0 36px;">Serving the Greater Metro Area — Licensed &amp; Insured</p>
    <a href="tel:8000000000" style="display:inline-block;padding:16px 44px;background:#d97706;color:#fff;border-radius:10px;font-weight:800;font-size:28px;text-decoration:none;letter-spacing:-0.5px;box-shadow:0 4px 20px rgba(217,119,6,.4);">(800) 000-0000</a>
    <p style="color:rgba(255,255,255,.5);font-size:13px;margin:14px 0 0;">Available 24/7 · All calls answered by a live operator</p>
  </div>
</section>`,
});

bm.add('pb-service-cards', {
    label: 'Service Cards', category: 'Service Sections',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="4" width="6" height="16" rx="1"/><rect x="9" y="4" width="6" height="16" rx="1"/><rect x="17" y="4" width="6" height="16" rx="1"/><line x1="1" y1="10" x2="7" y2="10"/><line x1="9" y1="10" x2="15" y2="10"/><line x1="17" y1="10" x2="23" y2="10"/></svg>`,
    content: `<section style="background:#f8fafc;padding:60px 24px;">
  <div style="max-width:1200px;margin:0 auto;">
    <h2 style="text-align:center;font-size:36px;font-weight:800;color:#1e3a5f;margin:0 0 8px;">Our Services</h2>
    <p style="text-align:center;color:#64748b;margin:0 0 40px;font-size:16px;">Expert solutions — done right the first time</p>
    <div style="display:flex;flex-wrap:wrap;gap:0;border-radius:16px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,.15);">
      <div style="flex:1 1 300px;min-height:340px;position:relative;overflow:hidden;background:linear-gradient(135deg,#1e3a5f,#0f2744);">
        <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.8) 40%,rgba(0,0,0,.3) 100%);"></div>
        <div style="position:absolute;bottom:0;left:0;right:0;padding:28px 24px;text-align:center;">
          <div style="width:52px;height:52px;background:rgba(217,119,6,.9);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;"><svg width="26" height="26" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg></div>
          <h3 style="color:#fff;font-size:20px;font-weight:700;margin:0 0 10px;">Service One</h3>
          <p style="color:rgba(255,255,255,.75);font-size:14px;margin:0 0 18px;line-height:1.5;">Describe this service. Add your key selling points here.</p>
          <a href="#" style="display:inline-block;padding:10px 24px;background:#d97706;color:#fff;border-radius:8px;font-weight:700;font-size:14px;text-decoration:none;">Book Online Now</a>
        </div>
      </div>
      <div style="flex:1 1 300px;min-height:340px;position:relative;overflow:hidden;background:linear-gradient(135deg,#14532d,#0a3a1f);">
        <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.8) 40%,rgba(0,0,0,.3) 100%);"></div>
        <div style="position:absolute;bottom:0;left:0;right:0;padding:28px 24px;text-align:center;">
          <div style="width:52px;height:52px;background:rgba(217,119,6,.9);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;"><svg width="26" height="26" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg></div>
          <h3 style="color:#fff;font-size:20px;font-weight:700;margin:0 0 10px;">Service Two</h3>
          <p style="color:rgba(255,255,255,.75);font-size:14px;margin:0 0 18px;line-height:1.5;">Describe this service. Add your key selling points here.</p>
          <a href="#" style="display:inline-block;padding:10px 24px;background:#d97706;color:#fff;border-radius:8px;font-weight:700;font-size:14px;text-decoration:none;">Book Online Now</a>
        </div>
      </div>
      <div style="flex:1 1 300px;min-height:340px;position:relative;overflow:hidden;background:linear-gradient(135deg,#4c1d95,#2d0f63);">
        <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.8) 40%,rgba(0,0,0,.3) 100%);"></div>
        <div style="position:absolute;bottom:0;left:0;right:0;padding:28px 24px;text-align:center;">
          <div style="width:52px;height:52px;background:rgba(217,119,6,.9);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;"><svg width="26" height="26" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg></div>
          <h3 style="color:#fff;font-size:20px;font-weight:700;margin:0 0 10px;">Service Three</h3>
          <p style="color:rgba(255,255,255,.75);font-size:14px;margin:0 0 18px;line-height:1.5;">Describe this service. Add your key selling points here.</p>
          <a href="#" style="display:inline-block;padding:10px 24px;background:#d97706;color:#fff;border-radius:8px;font-weight:700;font-size:14px;text-decoration:none;">Book Online Now</a>
        </div>
      </div>
    </div>
  </div>
</section>`,
});

bm.add('pb-accordion', {
    label: 'Accordion', category: 'Service Sections',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="4" width="20" height="5" rx="1.5"/><rect x="2" y="10" width="20" height="5" rx="1.5"/><rect x="2" y="16" width="20" height="5" rx="1.5"/></svg>`,
    content: `<section style="background:#1e3a5f;padding:40px 24px;">
  <div style="max-width:960px;margin:0 auto;">
    <h2 style="color:#fff;font-size:24px;font-weight:700;margin:0 0 6px;text-align:center;">Service Areas</h2>
    <p style="color:rgba(255,255,255,.6);text-align:center;margin:0 0 24px;font-size:14px;">Click a region to see coverage and contact info</p>
    <div style="border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.3);">
      <div style="border-bottom:1px solid #e5e7eb;">
        <button onclick="(function(btn){var body=btn.nextElementSibling,ch=btn.querySelector('.acc-ch'),open=body.style.display==='block';document.querySelectorAll('.acc-body').forEach(function(b){b.style.display='none';});document.querySelectorAll('.acc-ch').forEach(function(c){c.style.transform='';});if(!open){body.style.display='block';ch.style.transform='rotate(180deg)';};})(this)" style="width:100%;display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:#fff;border:none;cursor:pointer;font-size:16px;font-weight:600;color:#1e3a5f;text-align:left;">
          <span>Region One</span>
          <svg class="acc-ch" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="transition:transform .2s;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="acc-body" style="display:none;padding:16px 20px 24px;background:#f9fafb;"><p style="margin:0 0 8px;color:#374151;">Covering all cities in this region.</p><a href="tel:8000000000" style="color:#1d6b3a;font-weight:700;font-size:18px;">(800) 000-0000</a></div>
      </div>
      <div style="border-bottom:1px solid #e5e7eb;">
        <button onclick="(function(btn){var body=btn.nextElementSibling,ch=btn.querySelector('.acc-ch'),open=body.style.display==='block';document.querySelectorAll('.acc-body').forEach(function(b){b.style.display='none';});document.querySelectorAll('.acc-ch').forEach(function(c){c.style.transform='';});if(!open){body.style.display='block';ch.style.transform='rotate(180deg)';};})(this)" style="width:100%;display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:#fff;border:none;cursor:pointer;font-size:16px;font-weight:600;color:#1e3a5f;text-align:left;">
          <span>Region Two</span>
          <svg class="acc-ch" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="transition:transform .2s;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="acc-body" style="display:none;padding:16px 20px 24px;background:#f9fafb;"><p style="margin:0 0 8px;color:#374151;">Covering all cities in this region.</p><a href="tel:8000000000" style="color:#1d6b3a;font-weight:700;font-size:18px;">(800) 000-0000</a></div>
      </div>
      <div>
        <button onclick="(function(btn){var body=btn.nextElementSibling,ch=btn.querySelector('.acc-ch'),open=body.style.display==='block';document.querySelectorAll('.acc-body').forEach(function(b){b.style.display='none';});document.querySelectorAll('.acc-ch').forEach(function(c){c.style.transform='';});if(!open){body.style.display='block';ch.style.transform='rotate(180deg)';};})(this)" style="width:100%;display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:#fff;border:none;cursor:pointer;font-size:16px;font-weight:600;color:#1e3a5f;text-align:left;">
          <span>Region Three</span>
          <svg class="acc-ch" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="transition:transform .2s;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="acc-body" style="display:none;padding:16px 20px 24px;background:#f9fafb;"><p style="margin:0 0 8px;color:#374151;">Covering all cities in this region.</p><a href="tel:8000000000" style="color:#1d6b3a;font-weight:700;font-size:18px;">(800) 000-0000</a></div>
      </div>
    </div>
  </div>
</section>`,
});

bm.add('pb-why-choose', {
    label: 'Why Choose Us', category: 'Service Sections',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>`,
    content: `<section style="background:#f0f7ff;padding:60px 24px;">
  <div style="max-width:900px;margin:0 auto;text-align:center;">
    <h2 style="font-size:34px;font-weight:800;color:#1e3a5f;margin:0 0 8px;">Why Choose Us?</h2>
    <p style="color:#64748b;font-size:16px;margin:0 0 40px;">We're not just another service company — we're your neighbors.</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;text-align:left;">
      <div style="background:#fff;border-radius:12px;padding:18px 20px;box-shadow:0 2px 12px rgba(0,0,0,.06);display:flex;align-items:flex-start;gap:14px;"><span style="color:#1d6b3a;font-size:22px;flex-shrink:0;">✓</span><span style="color:#1e293b;font-weight:600;font-size:15px;">Same Day Service</span></div>
      <div style="background:#fff;border-radius:12px;padding:18px 20px;box-shadow:0 2px 12px rgba(0,0,0,.06);display:flex;align-items:flex-start;gap:14px;"><span style="color:#1d6b3a;font-size:22px;flex-shrink:0;">✓</span><span style="color:#1e293b;font-weight:600;font-size:15px;">All Calls Answered By A Live Operator 24/7</span></div>
      <div style="background:#fff;border-radius:12px;padding:18px 20px;box-shadow:0 2px 12px rgba(0,0,0,.06);display:flex;align-items:flex-start;gap:14px;"><span style="color:#1d6b3a;font-size:22px;flex-shrink:0;">✓</span><span style="color:#1e293b;font-weight:600;font-size:15px;">Licensed, Bonded &amp; Insured Technicians</span></div>
      <div style="background:#fff;border-radius:12px;padding:18px 20px;box-shadow:0 2px 12px rgba(0,0,0,.06);display:flex;align-items:flex-start;gap:14px;"><span style="color:#1d6b3a;font-size:22px;flex-shrink:0;">✓</span><span style="color:#1e293b;font-weight:600;font-size:15px;">Free, No-Obligation Service Estimates</span></div>
      <div style="background:#fff;border-radius:12px;padding:18px 20px;box-shadow:0 2px 12px rgba(0,0,0,.06);display:flex;align-items:flex-start;gap:14px;"><span style="color:#1d6b3a;font-size:22px;flex-shrink:0;">✓</span><span style="color:#1e293b;font-weight:600;font-size:15px;">Industry-Leading Warranty On All Work</span></div>
      <div style="background:#fff;border-radius:12px;padding:18px 20px;box-shadow:0 2px 12px rgba(0,0,0,.06);display:flex;align-items:flex-start;gap:14px;"><span style="color:#1d6b3a;font-size:22px;flex-shrink:0;">✓</span><span style="color:#1e293b;font-weight:600;font-size:15px;">5-Star Rated · Transparent Pricing</span></div>
    </div>
  </div>
</section>`,
});

bm.add('pb-trust-badges', {
    label: 'Discount Badges', category: 'Service Sections',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M6 20v-2a6 6 0 0112 0v2"/><path d="M18 8h2M4 8h2"/></svg>`,
    content: `<section style="background:#fff;padding:56px 24px;">
  <div style="max-width:900px;margin:0 auto;text-align:center;">
    <h2 style="font-size:28px;font-weight:800;color:#1e3a5f;margin:0 0 8px;">Special Discounts</h2>
    <p style="color:#64748b;font-size:15px;margin:0 0 36px;">We proudly offer discounts to those who serve our community.</p>
    <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:24px;">
      <div style="width:160px;text-align:center;"><div style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#1e3a5f,#3b82f6);margin:0 auto 12px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 20px rgba(30,58,95,.25);"><svg width="44" height="44" viewBox="0 0 44 44" fill="none" stroke="white" stroke-width="2"><circle cx="22" cy="15" r="8"/><path d="M6 38c0-8.8 7.2-16 16-16s16 7.2 16 16"/></svg></div><p style="font-weight:700;color:#1e3a5f;font-size:15px;margin:0 0 4px;">Senior Citizens</p><p style="color:#d97706;font-weight:800;font-size:18px;margin:0;">10% OFF</p></div>
      <div style="width:160px;text-align:center;"><div style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#14532d,#22c55e);margin:0 auto 12px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 20px rgba(20,83,45,.25);"><svg width="44" height="44" viewBox="0 0 44 44" fill="none" stroke="white" stroke-width="2"><path d="M8 12l6-6 8 4 8-4 6 6-2 14-12 6-12-6z"/></svg></div><p style="font-weight:700;color:#1e3a5f;font-size:15px;margin:0 0 4px;">Military</p><p style="color:#d97706;font-weight:800;font-size:18px;margin:0;">10% OFF</p></div>
      <div style="width:160px;text-align:center;"><div style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#b45309,#f59e0b);margin:0 auto 12px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 20px rgba(180,83,9,.25);"><svg width="44" height="44" viewBox="0 0 44 44" fill="none" stroke="white" stroke-width="2"><path d="M22 6l4 10h10l-8 6 3 10-9-6-9 6 3-10-8-6h10z"/></svg></div><p style="font-weight:700;color:#1e3a5f;font-size:15px;margin:0 0 4px;">First Responders</p><p style="color:#d97706;font-weight:800;font-size:18px;margin:0;">10% OFF</p></div>
      <div style="width:160px;text-align:center;"><div style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#4c1d95,#8b5cf6);margin:0 auto 12px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 20px rgba(76,29,149,.25);"><svg width="44" height="44" viewBox="0 0 44 44" fill="none" stroke="white" stroke-width="2"><path d="M22 6v32M6 22h32"/></svg></div><p style="font-weight:700;color:#1e3a5f;font-size:15px;margin:0 0 4px;">Educators</p><p style="color:#d97706;font-weight:800;font-size:18px;margin:0;">10% OFF</p></div>
    </div>
  </div>
</section>`,
});

bm.add('pb-showroom', {
    label: 'Showroom CTA', category: 'Service Sections',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>`,
    content: `<section style="background:linear-gradient(135deg,#0f172a,#1e3a5f);padding:60px 24px;">
  <div style="max-width:900px;margin:0 auto;text-align:center;">
    <div style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:20px;padding:48px 40px;">
      <div style="width:72px;height:72px;background:rgba(217,119,6,.9);border-radius:50%;margin:0 auto 20px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 24px rgba(217,119,6,.4);"><svg width="34" height="34" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg></div>
      <h2 style="color:#fff;font-size:34px;font-weight:800;margin:0 0 12px;">Browse Our Online Showroom</h2>
      <p style="color:rgba(255,255,255,.7);font-size:17px;line-height:1.6;max-width:560px;margin:0 auto 10px;">Sort by style, price, color, and more. Find the perfect option to complement your home.</p>
      <p style="color:rgba(255,255,255,.5);font-size:14px;margin:0 0 28px;">Hundreds of styles · Multiple price points · Instant visualization</p>
      <a href="#" style="display:inline-block;padding:14px 40px;background:#d97706;color:#fff;border-radius:10px;font-weight:700;font-size:17px;text-decoration:none;box-shadow:0 4px 20px rgba(217,119,6,.4);">View Now →</a>
    </div>
  </div>
</section>`,
});

bm.add('pb-footer-banner', {
    label: 'Footer Banner', category: 'Service Sections',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="16" width="20" height="6" rx="1.5"/><path d="M12 2v10M7 7l5-5 5 5"/></svg>`,
    content: `<section style="background:linear-gradient(135deg,#1e3a5f,#0f2744);padding:80px 24px;text-align:center;position:relative;overflow:hidden;">
  <div style="position:absolute;inset:0;opacity:.05;background:repeating-linear-gradient(45deg,#fff,#fff 2px,transparent 2px,transparent 20px);"></div>
  <div style="position:relative;max-width:760px;margin:0 auto;">
    <div style="display:flex;align-items:center;justify-content:center;gap:16px;margin-bottom:20px;">
      <div style="height:2px;background:rgba(217,119,6,.6);width:80px;"></div>
      <svg width="48" height="48" viewBox="0 0 48 48" fill="none"><rect x="4" y="14" width="40" height="22" rx="2" stroke="#d97706" stroke-width="2.5"/><line x1="4" y1="21" x2="44" y2="21" stroke="#d97706" stroke-width="2"/><line x1="4" y1="28" x2="44" y2="28" stroke="#d97706" stroke-width="2"/></svg>
      <div style="height:2px;background:rgba(217,119,6,.6);width:80px;"></div>
    </div>
    <h2 style="color:#ffffff;font-size:42px;font-weight:900;margin:0 0 12px;line-height:1.1;letter-spacing:-0.5px;">Your Company Name</h2>
    <p style="color:#fbbf24;font-size:22px;font-weight:700;margin:0 0 28px;letter-spacing:.02em;">Your Tagline Here</p>
    <p style="color:rgba(255,255,255,.65);font-size:15px;margin:0 0 36px;">Licensed · Bonded · Insured · Available 24/7</p>
    <a href="tel:8000000000" style="display:inline-block;padding:18px 52px;background:#d97706;color:#fff;border-radius:12px;font-weight:800;font-size:26px;text-decoration:none;box-shadow:0 6px 32px rgba(217,119,6,.45);letter-spacing:-0.5px;">(800) 000-0000</a>
  </div>
</section>`,
});

function setBlockCategoriesOpen(open) {
    if (bm.getCategories) {
        bm.getCategories().each(category => category.set('open', open));
    }

    document.querySelectorAll('#blocks-wrap .gjs-block-category').forEach(category => {
        category.classList.toggle('gjs-open', open);
    });
}

function collapseBlockCategories() {
    setBlockCategoriesOpen(false);
}

setTimeout(collapseBlockCategories, 0);

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
editor.on('load', () => setTimeout(() => {
    collapseBlockCategories();
    editor.refresh();
}, 50));

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
    const q = this.value.trim().toLowerCase();
    document.querySelectorAll('#blocks-wrap .gjs-block').forEach(block => {
        const label = block.querySelector('.gjs-block-label');
        const text = (label ? label.textContent : block.textContent).toLowerCase();
        const matches = text.includes(q);
        block.dataset.searchMatch = matches ? 'true' : 'false';
        if (matches) block.style.removeProperty('display');
        else block.style.setProperty('display', 'none', 'important');
    });
    // Hide empty categories
    document.querySelectorAll('#blocks-wrap .gjs-block-category').forEach(cat => {
        const visible = [...cat.querySelectorAll('.gjs-block')].some(b => b.dataset.searchMatch !== 'false');
        cat.style.display = visible ? '' : 'none';
        cat.classList.toggle('gjs-open', Boolean(q && visible));
    });
    if (!q) collapseBlockCategories();
});

// ─── Gradient & Opacity — injected into the Background sector
function hexFromRgb(rgb) {
    const m = rgb.match(/\d+/g);
    if (!m || m.length < 3) return '#000000';
    return '#' + m.slice(0,3).map(n => parseInt(n).toString(16).padStart(2,'0')).join('');
}

function hexFromCssColor(value) {
    const color = String(value || '').trim();
    if (color.startsWith('rgb')) return hexFromRgb(color);

    const hex = color.match(/#[0-9a-f]{3,8}\b/i);
    if (!hex) return '#000000';

    const raw = hex[0].slice(1);
    if (raw.length === 3 || raw.length === 4) {
        return '#' + raw.slice(0, 3).split('').map(char => char + char).join('');
    }

    return '#' + raw.slice(0, 6);
}

function clampOpacity(value) {
    const parsed = parseFloat(value);
    if (Number.isNaN(parsed)) return 1;
    return Math.max(0, Math.min(1, parsed));
}

function opacityFromCssColor(value) {
    const color = String(value || '').trim();
    const rgba = color.match(/rgba?\(([^)]*)\)/i);
    if (rgba) {
        const body = rgba[1].trim();
        const slashAlpha = body.match(/\/\s*([\d.]+%?)/);
        if (slashAlpha) {
            const alpha = slashAlpha[1].endsWith('%') ? parseFloat(slashAlpha[1]) / 100 : parseFloat(slashAlpha[1]);
            return clampOpacity(alpha);
        }

        const parts = body.split(',').map(part => part.trim());
        if (parts.length >= 4) {
            const alpha = parts[3].endsWith('%') ? parseFloat(parts[3]) / 100 : parseFloat(parts[3]);
            return clampOpacity(alpha);
        }
    }

    const hex = color.match(/#([0-9a-f]{4}|[0-9a-f]{8})\b/i);
    if (hex) {
        const alphaHex = hex[1].length === 4 ? hex[1][3] + hex[1][3] : hex[1].slice(6, 8);
        return clampOpacity(parseInt(alphaHex, 16) / 255);
    }

    return 1;
}

function parseGradient(bgImage) {
    if (!bgImage) return null;
    const lower = bgImage.toLowerCase();
    const linearIndex = lower.indexOf('linear-gradient(');
    const radialIndex = lower.indexOf('radial-gradient(');
    const isLinear = linearIndex > -1 && (radialIndex === -1 || linearIndex < radialIndex);
    const start = isLinear ? linearIndex : radialIndex;
    if (start < 0) return null;

    const gradient = readCssFunction(bgImage, start).text;
    const body = gradient.slice(gradient.indexOf('(') + 1, gradient.lastIndexOf(')'));
    const parts = splitCssList(body);
    if (parts.length < 2) return null;

    let dir = '';
    let stops = parts;
    if (isLinear && /^(to\s|[-\d.]+(?:deg|rad|turn)\b)/i.test(parts[0].trim())) {
        dir = parts[0].trim();
        stops = parts.slice(1);
    } else if (!isLinear && /^(circle\b|ellipse\b|at\s)/i.test(parts[0].trim())) {
        stops = parts.slice(1);
    }

    if (stops.length < 2) return null;

    return {
        type: isLinear ? 'linear' : 'radial',
        dir,
        c1: stops[0].trim(),
        c2: stops[1].trim(),
        opacity: Math.min(opacityFromCssColor(stops[0]), opacityFromCssColor(stops[1])),
    };
}

const GRADIENT_HTML = `
<div class="gi-sep"></div>
<div class="gi-row">
  <span class="gi-label">Effect</span>
  <select id="gi-effect">
    <option value="none">None</option>
    <option value="parallax">Parallax</option>
  </select>
</div>
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
    <span class="gi-label">Grad opacity</span>
    <input type="range" id="gi-gradient-opacity" min="0" max="1" step="0.01" value="1">
    <span class="gi-opacity-val" id="gi-gradient-opacity-val">100%</span>
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
    const giEffect = document.getElementById('gi-effect');
    const giGradientOpacity = document.getElementById('gi-gradient-opacity');
    const giGradientOpacityVal = document.getElementById('gi-gradient-opacity-val');
    giEffect.value = 'none';

    function rgbaFromHex(hex, opacity) {
        const raw = hex.replace('#', '');
        const normalized = raw.length === 3 ? raw.split('').map(char => char + char).join('') : raw;
        const r = parseInt(normalized.slice(0, 2), 16);
        const g = parseInt(normalized.slice(2, 4), 16);
        const b = parseInt(normalized.slice(4, 6), 16);
        const alpha = Math.round(clampOpacity(opacity) * 100) / 100;
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    function gradientColor(hex) {
        const opacity = clampOpacity(giGradientOpacity.value);
        return opacity >= 1 ? hex : rgbaFromHex(hex, opacity);
    }

    function updateGradientOpacityLabel() {
        giGradientOpacityVal.textContent = Math.round(clampOpacity(giGradientOpacity.value) * 100) + '%';
    }

    function buildGrad() {
        const t = giType.value, c1 = gradientColor(giC1.value), c2 = gradientColor(giC2.value);
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

    function getBackgroundEffect(styles) {
        const attachment = String(styles['background-attachment'] || 'scroll').toLowerCase();
        if (attachment !== 'fixed') return 'none';

        const repeat = String(styles['background-repeat'] || '').toLowerCase();
        const size = String(styles['background-size'] || '').toLowerCase();
        const position = normalizeBackgroundPosition(String(styles['background-position'] || ''));

        if (repeat === 'no-repeat' && size === 'cover' && position === 'center center') {
            return 'parallax';
        }

        return 'none';
    }

    function applyBackgroundEffect() {
        const c = editor.getSelected(); if (!c) return;
        const mode = giEffect.value;
        const patch = {};

        if (mode === 'parallax') {
            Object.assign(patch, {
                'background-attachment': 'fixed',
                'background-repeat': 'no-repeat',
                'background-size': 'cover',
                'background-position': 'center center',
            });
        } else {
            patch['background-attachment'] = 'scroll';
        }

        c.addStyle(patch);
        setTimeout(() => syncGradientControls(editor.getSelected()), 0);
    }

    giType.addEventListener('change', () => { updateOpts(); applyGradient(); });
    giDir.addEventListener('change', applyGradient);
    giC1.addEventListener('input', applyGradient);
    giC2.addEventListener('input', applyGradient);
    giGradientOpacity.addEventListener('input', () => {
        updateGradientOpacityLabel();
        applyGradient();
    });
    giEffect.addEventListener('change', applyBackgroundEffect);

    props.addEventListener('change', event => {
        const syncSelectors = [
            '.gjs-sm-property__background-attachment select',
            '.gjs-sm-property__background-repeat select',
            '.gjs-sm-property__background-size select',
            '.gjs-sm-property__background-position select',
        ];

        if (syncSelectors.some(selector => event.target.matches(selector))) {
            setTimeout(() => syncGradientControls(editor.getSelected()), 0);
        }
    });

    giOpacity.addEventListener('input', () => {
        const v = parseFloat(giOpacity.value);
        giOpacityVal.textContent = Math.round(v * 100) + '%';
        const c = editor.getSelected(); if (c) c.addStyle({ opacity: v });
    });

    // Sync controls from selected component's existing styles
    function syncGradientControls(component) {
        if (!component) {
            giEffect.value = 'none';
            return;
        }

        const styles = component.getStyle();
        giEffect.value = getBackgroundEffect(styles);
        // Opacity
        const op = styles.opacity !== undefined ? parseFloat(styles.opacity) : 1;
        giOpacity.value = op;
        giOpacityVal.textContent = Math.round(op * 100) + '%';
        // Gradient
        const parsed = parseGradient(styles['background-image'] || '');
        if (parsed) {
            giType.value = parsed.type;
            giC1.value = hexFromCssColor(parsed.c1);
            giC2.value = hexFromCssColor(parsed.c2);
            giGradientOpacity.value = parsed.opacity ?? 1;
            if (parsed.type === 'linear') giDir.value = parsed.dir || 'to right';
        } else {
            giType.value = 'none';
            giGradientOpacity.value = 1;
        }
        updateGradientOpacityLabel();
        updateOpts();
    }

    editor.on('component:selected', syncGradientControls);
    syncGradientControls(editor.getSelected());
}

// Inject as soon as the Background sector is available.
function scheduleGradientInjection() {
    setTimeout(injectGradientIntoBackground, 0);
    setTimeout(injectGradientIntoBackground, 150);
}

editor.on('load', scheduleGradientInjection);
editor.on('component:selected', scheduleGradientInjection);

const ALIGNMENT_HTML = `
<div id="pa-wrap">
  <div class="pa-row">
    <span class="pa-label">Align</span>
    <div class="pa-buttons">
      <button type="button" class="pa-btn" data-align="left" title="Align left" aria-label="Align left">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="10" x2="14" y2="10"/><line x1="4" y1="14" x2="20" y2="14"/><line x1="4" y1="18" x2="14" y2="18"/></svg>
      </button>
      <button type="button" class="pa-btn" data-align="center" title="Align center" aria-label="Align center">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="6" x2="20" y2="6"/><line x1="7" y1="10" x2="17" y2="10"/><line x1="4" y1="14" x2="20" y2="14"/><line x1="7" y1="18" x2="17" y2="18"/></svg>
      </button>
      <button type="button" class="pa-btn" data-align="right" title="Align right" aria-label="Align right">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="6" x2="20" y2="6"/><line x1="10" y1="10" x2="20" y2="10"/><line x1="4" y1="14" x2="20" y2="14"/><line x1="10" y1="18" x2="20" y2="18"/></svg>
      </button>
    </div>
  </div>
</div>`;

function inferAlignment(component) {
    if (!component) return 'left';

    const styles = component.getStyle?.() || {};
    const textAlign = String(styles['text-align'] || '').toLowerCase();
    if (['left', 'center', 'right'].includes(textAlign)) return textAlign;

    const marginLeft = String(styles['margin-left'] || '').toLowerCase();
    const marginRight = String(styles['margin-right'] || '').toLowerCase();
    if (marginLeft === 'auto' && marginRight === 'auto') return 'center';
    if (marginLeft === 'auto') return 'right';
    if (marginRight === 'auto') return 'left';

    const el = component.getEl?.();
    if (el) {
        const computed = el.ownerDocument.defaultView.getComputedStyle(el);
        const computedAlign = String(computed.textAlign || '').toLowerCase();
        if (['left', 'center', 'right'].includes(computedAlign)) return computedAlign;
    }

    return 'left';
}

function syncAlignmentControls(component = editor.getSelected()) {
    const active = inferAlignment(component);
    document.querySelectorAll('#pa-wrap .pa-btn').forEach(button => {
        button.classList.toggle('active', button.dataset.align === active);
    });
}

function applyAlignment(value) {
    const component = editor.getSelected();
    if (!component || !['left', 'center', 'right'].includes(value)) return;

    const tag = getTagName(component);
    const type = component.get?.('type');
    component.addStyle({ 'text-align': value });

    if (['img', 'video', 'iframe'].includes(tag) || ['image', 'video'].includes(type)) {
        const marginPatch = value === 'center'
            ? { display: 'block', 'margin-left': 'auto', 'margin-right': 'auto' }
            : value === 'right'
                ? { display: 'block', 'margin-left': 'auto', 'margin-right': '0' }
                : { display: 'block', 'margin-left': '0', 'margin-right': 'auto' };
        component.addStyle(marginPatch);
    }

    if (['a', 'button'].includes(tag)) {
        component.parent?.()?.addStyle({ 'text-align': value });
    }

    syncAlignmentControls(component);
}

function injectAlignmentIntoLayout() {
    const props = document.querySelector('#styles-wrap .gjs-sm-sector__layout .gjs-sm-properties');
    if (!props) return;

    if (!document.getElementById('pa-wrap')) {
        props.insertAdjacentHTML('beforeend', ALIGNMENT_HTML);
        document.querySelectorAll('#pa-wrap .pa-btn').forEach(button => {
            button.addEventListener('click', () => applyAlignment(button.dataset.align));
        });
    }

    syncAlignmentControls();
}

function scheduleAlignmentInjection() {
    setTimeout(injectAlignmentIntoLayout, 0);
    setTimeout(injectAlignmentIntoLayout, 150);
}

editor.on('load', scheduleAlignmentInjection);
editor.on('component:selected', scheduleAlignmentInjection);
editor.on('component:styleUpdate', syncAlignmentControls);

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

document.getElementById('save-btn').addEventListener('click', () => doSave('publish'));

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
                { property: 'background' },
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
                { property: 'background-attachment', type: 'select', list: [
                    { value: 'scroll', name: 'Scroll' },
                    { value: 'fixed', name: 'Fixed' },
                    { value: 'local', name: 'Local' },
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
