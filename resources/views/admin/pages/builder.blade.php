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
            --builder-style-properties-bg: #385361;
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
            height: calc(100vh - 52px);
            margin-top: 52px;
            min-height: 0;
        }

        /* ─── LEFT PANEL ─── */
        #panel-left {
            width: 244px;
            flex-shrink: 0;
            min-height: 0;
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
            min-height: 0;
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
            min-height: 0;
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

        .p-body { flex: 1; min-height: 0; overflow-y: auto; overflow-x: hidden; }
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
        #styles-wrap .gjs-sm-properties {
            background: var(--builder-style-properties-bg) !important;
            padding: 8px 10px !important;
            gap: 6px !important;
        }
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
        #styles-wrap .gjs-field-colorp { cursor: pointer !important; }
        .sp-container.gjs-editor-sp {
            max-width: calc(100vw - 16px) !important;
            max-height: calc(100vh - 16px) !important;
        }
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
        .editor-action-button {
            width: 100%;
            height: 34px;
            border: 1px solid #818cf8;
            border-radius: 6px;
            background: #4f46e5;
            color: #fff;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            transition: background .15s, border-color .15s;
        }
        .editor-action-button:hover {
            background: #4338ca;
            border-color: #a5b4fc;
        }
        .editor-action-button:disabled {
            cursor: not-allowed;
            opacity: .55;
        }
        .editor-color-field {
            display: flex;
            align-items: stretch;
            gap: 7px;
            width: 100%;
        }
        .editor-color-field .editor-color-swatch {
            width: 36px;
            height: 34px;
            flex: 0 0 36px;
            border: 1px solid #334155;
            border-radius: 6px;
            background: #1e293b;
            cursor: pointer;
            padding: 3px;
        }
        .editor-color-field .editor-color-text {
            flex: 1 1 auto;
            min-width: 0;
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
        #sa-wrap {
            width: 100%;
            flex: 1 1 100%;
            border-top: 1px solid #1e293b;
            border-bottom: 1px solid #1e293b;
            margin: 8px 0;
            padding: 8px 0;
        }
        #sa-wrap,
        #sa-wrap * {
            box-sizing: border-box;
        }
        .sa-row {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            min-width: 0;
        }
        .sa-row + .sa-row {
            margin-top: 7px;
        }
        .sa-label-wrap {
            width: 84px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 4px;
            min-width: 0;
        }
        .sa-label {
            min-width: 0;
            color: #e2e8f0;
            font-size: 11px;
        }
        .sa-clear-btn {
            width: 16px;
            height: 16px;
            flex: 0 0 16px;
            border: 0;
            border-radius: 4px;
            background: transparent;
            color: #cbd5e1;
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
            line-height: 16px;
            opacity: 0;
            padding: 0;
            pointer-events: none;
            transition: background .15s, color .15s, opacity .15s;
        }
        .sa-clear-btn.visible {
            opacity: 1;
            pointer-events: auto;
        }
        .sa-clear-btn.visible:hover {
            background: #1e293b;
            color: #fff;
        }
        .sa-clear-btn:disabled {
            cursor: not-allowed;
            opacity: 0;
            pointer-events: none;
        }
        .sa-control {
            display: flex;
            flex: 1 1 auto;
            min-width: 0;
        }
        .sa-control input,
        .sa-control select {
            background: #1e293b;
            border: 1px solid #334155;
            color: #f8fafc;
            font-size: 11px;
            height: 28px;
            outline: none;
        }
        .sa-control input {
            width: 0;
            min-width: 0;
            flex: 1 1 auto;
            border-radius: 5px 0 0 5px;
            padding: 4px 6px;
        }
        .sa-control select {
            width: 56px;
            flex-shrink: 0;
            border-left: 0;
            border-radius: 0 5px 5px 0;
            padding: 4px 5px;
        }
        .sa-control input:focus,
        .sa-control select:focus {
            border-color: #818cf8;
        }
        .sa-control input:disabled {
            color: #94a3b8;
            cursor: not-allowed;
        }
        #sa-wrap.is-disabled {
            opacity: .55;
        }
        #styles-wrap .gjs-sm-property__margin .gjs-sm-property__margin-top,
        #styles-wrap .gjs-sm-property__margin .gjs-sm-property__margin-right,
        #styles-wrap .gjs-sm-property__margin .gjs-sm-property__margin-bottom,
        #styles-wrap .gjs-sm-property__margin .gjs-sm-property__margin-left {
            flex: 1 1 100% !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        #styles-wrap .gjs-sm-property__margin .gjs-sm-property__margin-top > .gjs-fields,
        #styles-wrap .gjs-sm-property__margin .gjs-sm-property__margin-right > .gjs-fields,
        #styles-wrap .gjs-sm-property__margin .gjs-sm-property__margin-bottom > .gjs-fields,
        #styles-wrap .gjs-sm-property__margin .gjs-sm-property__margin-left > .gjs-fields {
            display: flex !important;
            align-items: center;
            gap: 6px;
            min-width: 0;
        }
        #styles-wrap .gjs-sm-property__margin .gjs-field {
            flex: 1 1 auto;
            min-width: 0;
        }
        .ma-auto-btn {
            height: 26px;
            flex: 0 0 auto;
            border: 1px solid #334155;
            border-radius: 5px;
            background: #1e293b;
            color: #cbd5e1;
            cursor: pointer;
            font-size: 10px;
            font-weight: 700;
            line-height: 1;
            padding: 0 7px;
            text-transform: uppercase;
            transition: all .15s;
        }
        .ma-auto-btn:hover,
        .ma-auto-btn.active {
            background: #4f46e5;
            border-color: #818cf8;
            color: #fff;
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
        #be-wrap {
            width: 100%;
            flex: 1 1 100%;
            border-top: 1px solid #1e293b;
            margin-top: 8px;
            padding-top: 8px;
        }
        #be-wrap,
        #be-wrap * {
            box-sizing: border-box;
        }
        .be-sides {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 4px;
        }
        .be-side {
            height: 26px;
            border: 1px solid #334155;
            border-radius: 5px;
            background: #1e293b;
            color: #cbd5e1;
            cursor: pointer;
            font-size: 9px;
            font-weight: 700;
            padding: 0 3px;
            transition: all .15s;
        }
        .be-side:hover,
        .be-side.active {
            background: #4f46e5;
            border-color: #818cf8;
            color: #fff;
        }
        .be-preview-row {
            display: grid;
            grid-template-columns: 48px minmax(0, 1fr);
            gap: 8px;
            align-items: center;
            margin-top: 8px;
        }
        #be-preview {
            width: 44px;
            height: 34px;
            border-radius: 6px;
            background: #f8fafc;
            border: 1px solid #334155;
        }
        #be-target-label {
            color: #cbd5e1;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .be-control,
        .be-row {
            margin-top: 7px;
        }
        .be-control-head,
        .be-row {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }
        .be-label {
            width: 44px;
            flex-shrink: 0;
            color: #e2e8f0;
            font-size: 11px;
        }
        #be-width-range {
            flex: 1 1 auto;
            min-width: 0;
            accent-color: #4f46e5;
        }
        #be-width-number {
            width: 58px;
            flex-shrink: 0;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 5px;
            color: #f8fafc;
            font-size: 11px;
            padding: 4px 6px;
            outline: none;
        }
        #be-style {
            flex: 1 1 auto;
            min-width: 0;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 5px;
            color: #f8fafc;
            font-size: 11px;
            padding: 4px 6px;
            outline: none;
        }
        #be-color {
            width: 100%;
            height: 26px;
            flex: 1 1 auto;
            min-width: 0;
            border: 1px solid #334155;
            border-radius: 5px;
            background: #1e293b;
            cursor: pointer;
            padding: 2px;
        }
        #be-width-number:focus,
        #be-style:focus,
        #be-color:focus {
            border-color: #818cf8;
        }
        #be-wrap.is-disabled {
            opacity: .55;
        }
        #br-wrap {
            width: 100%;
            flex: 1 1 100%;
            border-top: 1px solid #1e293b;
            border-bottom: 1px solid #1e293b;
            margin: 8px 0;
            padding: 8px 0;
        }
        #br-wrap,
        #br-wrap * {
            box-sizing: border-box;
        }
        .br-title {
            color: #e2e8f0;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 7px;
        }
        .br-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 7px;
        }
        .br-corner {
            min-width: 0;
        }
        .br-label {
            display: block;
            color: #cbd5e1;
            font-size: 10px;
            margin-bottom: 3px;
        }
        .br-control {
            display: flex;
            min-width: 0;
        }
        .br-value,
        .br-unit {
            height: 26px;
            background: #1e293b;
            border: 1px solid #334155;
            color: #f8fafc;
            font-size: 11px;
            outline: none;
        }
        .br-value {
            width: 0;
            min-width: 0;
            flex: 1 1 auto;
            border-radius: 5px 0 0 5px;
            padding: 4px 6px;
        }
        .br-unit {
            width: 46px;
            flex-shrink: 0;
            border-left: 0;
            border-radius: 0 5px 5px 0;
            padding: 4px;
        }
        .br-value:focus,
        .br-unit:focus {
            border-color: #818cf8;
        }
        #br-wrap.is-disabled {
            opacity: .55;
        }
        #se-wrap {
            width: 100%;
            flex: 1 1 100%;
            border-top: 1px solid #1e293b;
            margin-top: 8px;
            padding-top: 8px;
        }
        #se-wrap,
        #se-wrap * {
            box-sizing: border-box;
        }
        .se-presets {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 4px;
        }
        .se-preset {
            height: 26px;
            border: 1px solid #334155;
            border-radius: 5px;
            background: #1e293b;
            color: #cbd5e1;
            cursor: pointer;
            font-size: 10px;
            font-weight: 700;
            padding: 0 4px;
            transition: all .15s;
        }
        .se-preset:hover,
        .se-preset.active {
            background: #4f46e5;
            border-color: #818cf8;
            color: #fff;
        }
        .se-preview-row {
            display: grid;
            grid-template-columns: 48px minmax(0, 1fr);
            gap: 8px;
            align-items: center;
            margin-top: 8px;
        }
        #se-preview {
            width: 44px;
            height: 34px;
            border-radius: 6px;
            border: 1px solid #334155;
            background: #f8fafc;
        }
        .se-toggles {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            min-width: 0;
        }
        .se-toggle {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #cbd5e1;
            font-size: 10px;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
        }
        .se-toggle input {
            width: 13px;
            height: 13px;
            accent-color: #4f46e5;
        }
        .se-control {
            margin-top: 7px;
        }
        .se-control-head,
        .se-color-row {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }
        .se-control-label,
        .se-color-label {
            width: 44px;
            flex-shrink: 0;
            color: #e2e8f0;
            font-size: 11px;
        }
        .se-number {
            width: 58px;
            flex-shrink: 0;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 5px;
            color: #f8fafc;
            font-size: 11px;
            padding: 4px 6px;
            outline: none;
        }
        .se-number:focus,
        .se-color-row input[type=color]:focus {
            border-color: #818cf8;
        }
        .se-control input[type=range],
        .se-color-row input[type=range] {
            flex: 1 1 auto;
            min-width: 0;
            accent-color: #4f46e5;
        }
        .se-color-row {
            margin-top: 8px;
        }
        .se-color-row input[type=color] {
            width: 42px;
            height: 26px;
            flex-shrink: 0;
            border: 1px solid #334155;
            border-radius: 5px;
            background: #1e293b;
            cursor: pointer;
            padding: 2px;
        }
        #se-opacity-val {
            width: 34px;
            flex-shrink: 0;
            text-align: right;
            color: #cbd5e1;
            font-size: 11px;
        }
        #se-wrap.is-disabled {
            opacity: .55;
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

const STYLE_COLOR_PICKER_MARGIN = 8;
let styleColorPickerClampRaf = 0;

function visibleStyleColorPickers() {
    return [...document.querySelectorAll('.sp-container.gjs-editor-sp')].filter(picker => {
        const styles = getComputedStyle(picker);
        return styles.display !== 'none' && !picker.classList.contains('sp-hidden');
    });
}

function clampStyleColorPickers() {
    styleColorPickerClampRaf = 0;

    visibleStyleColorPickers().forEach(picker => {
        const viewportWidth = document.documentElement.clientWidth || window.innerWidth;
        const viewportHeight = document.documentElement.clientHeight || window.innerHeight;
        const maxWidth = Math.max(180, viewportWidth - STYLE_COLOR_PICKER_MARGIN * 2);
        const maxHeight = Math.max(180, viewportHeight - STYLE_COLOR_PICKER_MARGIN * 2);

        if (picker.style.position !== 'fixed') picker.style.position = 'fixed';
        if (picker.style.maxWidth !== `${maxWidth}px`) picker.style.maxWidth = `${maxWidth}px`;
        if (picker.style.maxHeight !== `${maxHeight}px`) picker.style.maxHeight = `${maxHeight}px`;

        const rect = picker.getBoundingClientRect();
        const nextLeft = Math.max(
            STYLE_COLOR_PICKER_MARGIN,
            Math.min(rect.left, viewportWidth - rect.width - STYLE_COLOR_PICKER_MARGIN)
        );
        const nextTop = Math.max(
            STYLE_COLOR_PICKER_MARGIN,
            Math.min(rect.top, viewportHeight - rect.height - STYLE_COLOR_PICKER_MARGIN)
        );
        const left = `${Math.round(nextLeft)}px`;
        const top = `${Math.round(nextTop)}px`;
        const overflowY = rect.height > maxHeight ? 'auto' : '';

        if (picker.style.left !== left) picker.style.left = left;
        if (picker.style.top !== top) picker.style.top = top;
        if (picker.style.overflowY !== overflowY) picker.style.overflowY = overflowY;
    });
}

function scheduleStyleColorPickerClamp() {
    if (!styleColorPickerClampRaf) {
        styleColorPickerClampRaf = requestAnimationFrame(clampStyleColorPickers);
    }
}

function scheduleStyleColorPickerClampBurst() {
    scheduleStyleColorPickerClamp();
    setTimeout(scheduleStyleColorPickerClamp, 25);
    setTimeout(scheduleStyleColorPickerClamp, 100);
    setTimeout(scheduleStyleColorPickerClamp, 250);
}

document.addEventListener('pointerdown', event => {
    if (event.target.closest?.('#styles-wrap .gjs-field-color, .sp-container.gjs-editor-sp')) {
        scheduleStyleColorPickerClampBurst();
    }
}, true);

document.addEventListener('click', event => {
    if (event.target.closest?.('#styles-wrap .gjs-field-color, .sp-container.gjs-editor-sp')) {
        scheduleStyleColorPickerClampBurst();
    }
}, true);

document.querySelector('#panel-right .p-body')?.addEventListener('scroll', scheduleStyleColorPickerClamp, { passive: true });
window.addEventListener('resize', scheduleStyleColorPickerClampBurst);

new MutationObserver(scheduleStyleColorPickerClamp).observe(document.body, {
    subtree: true,
    childList: true,
    attributes: true,
    attributeFilter: ['class', 'style'],
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

function escapeAttribute(value) {
    return escapeHtml(value).replace(/"/g, '&quot;');
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

function componentHasAttribute(component, name) {
    return component?.getAttributes?.()[name] !== undefined;
}

function findDescendant(component, predicate) {
    if (!component) return null;
    if (predicate(component)) return component;
    return findFirstDescendant(component, predicate);
}

function cleanServiceAreaName(value) {
    return String(value || '')
        .replace(/\u00a0/g, ' ')
        .replace(/📍/g, '')
        .replace(/\s+/g, ' ')
        .trim();
}

function phoneHrefFromText(value) {
    const digits = String(value || '').replace(/\D/g, '');
    return digits ? `tel:${digits}` : 'tel:8000000000';
}

function serviceAreaCardMarkup(area = {}) {
    const name = cleanServiceAreaName(area.name) || 'New Service Area';
    const description = String(area.description || `Covering all cities in the ${name} area.`).trim();
    const phone = String(area.phone || '(800) 000-0000').trim();
    const href = String(area.href || phoneHrefFromText(phone)).trim();
    const background = String(area.background || '#f8fafc').trim();

    return `<article data-service-area-card="true" style="background:${escapeAttribute(background)};border:1px solid rgba(30,58,95,.12);border-radius:10px;padding:14px 12px;min-height:140px;display:flex;flex-direction:column;gap:8px;box-shadow:0 3px 14px rgba(15,23,42,.12);">
  <h3 data-service-area-name="true" style="color:#1e3a5f;font-size:14px;font-weight:800;line-height:1.25;margin:0;"><span style="color:#d97706;margin-right:4px;">&#128205;</span>${escapeHtml(name)}</h3>
  <p data-service-area-description="true" style="color:#475569;font-size:12px;line-height:1.45;margin:0;flex:1 1 auto;">${escapeHtml(description)}</p>
  <a data-service-area-phone="true" href="${escapeAttribute(href)}" style="color:#1d6b3a;font-size:13px;font-weight:800;text-decoration:none;">${escapeHtml(phone)}</a>
</article>`;
}

function serviceAreasGridStyle() {
    return 'display:grid;grid-template-columns:repeat(auto-fit,minmax(132px,1fr));gap:12px;align-items:stretch;';
}

function serviceAreasSectionInnerHtml(heading, subheading, areas) {
    const cards = (areas.length ? areas : [
        { name: 'North County' },
        { name: 'South District' },
        { name: 'East Valley' },
    ]).map(serviceAreaCardMarkup).join('\n');

    return `<div data-service-areas-inner="true" style="max-width:1140px;margin:0 auto;">
  <h2 data-service-area-heading="true" style="color:#fff;font-size:24px;font-weight:700;margin:0 0 6px;text-align:center;">${escapeHtml(heading || 'Service Areas')}</h2>
  <p data-service-area-subheading="true" style="color:rgba(255,255,255,.65);text-align:center;margin:0 0 24px;font-size:14px;">${escapeHtml(subheading || 'Click a region to see coverage and contact info')}</p>
  <div data-service-area-grid="true" style="${serviceAreasGridStyle()}">
    ${cards}
  </div>
</div>`;
}

function findServiceAreasGrid(component) {
    return findDescendant(component, child => componentHasAttribute(child, 'data-service-area-grid'));
}

function findServiceAreaCards(component) {
    const grid = findServiceAreasGrid(component);
    const cards = [];
    if (!grid) return cards;

    childComponents(grid).forEach(child => {
        if (componentHasAttribute(child, 'data-service-area-card')) cards.push(child);
    });

    return cards;
}

function serviceAreaCardBackground(card) {
    const styles = card?.getStyle?.() || {};
    return String(cssPropertyValue(styles, 'background-color') || cssPropertyValue(styles, 'background') || '#f8fafc').trim();
}

function serviceAreaCardsBackgroundValue(component) {
    const backgrounds = findServiceAreaCards(component)
        .map(serviceAreaCardBackground)
        .filter(Boolean);

    if (!backgrounds.length) return '#f8fafc';

    const first = backgrounds[0].toLowerCase();
    return backgrounds.every(background => background.toLowerCase() === first) ? backgrounds[0] : '';
}

function updateServiceAreaCardsBackground(component, value) {
    const background = String(value || '').trim();
    if (!background) return;

    findServiceAreaCards(component).forEach(card => {
        card.addStyle({
            background,
            'background-color': background,
        });
    });
    component.view?.render?.();
}

function serviceAreaCardParts(card) {
    if (!card) return null;

    const name = findDescendant(card, child => componentHasAttribute(child, 'data-service-area-name'));
    const description = findDescendant(card, child => componentHasAttribute(child, 'data-service-area-description'));
    const phone = findDescendant(card, child => componentHasAttribute(child, 'data-service-area-phone'));

    return { name, description, phone };
}

function serviceAreaCardData(card) {
    const parts = serviceAreaCardParts(card);
    const phone = parts?.phone ? getComponentText(parts.phone) : '(800) 000-0000';

    return {
        name: parts?.name ? cleanServiceAreaName(getComponentText(parts.name)) : 'New Service Area',
        description: parts?.description ? getComponentText(parts.description) : '',
        phone,
        href: parts?.phone ? getEditableAttributeValue(parts.phone, 'href') : phoneHrefFromText(phone),
    };
}

function collectAccordionServiceAreas(component) {
    const areas = [];

    walkComponents(component, child => {
        if (getTagName(child) !== 'button') return;

        const parent = child.parent?.();
        const siblings = childComponents(parent);
        const body = siblings.find(sibling => sibling !== child && (
            componentHasAttribute(sibling, 'class') || getTagName(sibling) === 'div'
        ));
        const description = body ? findDescendant(body, descendant => getTagName(descendant) === 'p') : null;
        const phone = body ? findDescendant(body, descendant => getTagName(descendant) === 'a') : null;
        const name = cleanServiceAreaName(getComponentText(child));

        if (!name || name.toLowerCase().includes('save') || name.length > 80) return;

        areas.push({
            name,
            description: description ? getComponentText(description) : `Covering all cities in the ${name} area.`,
            phone: phone ? getComponentText(phone) : '(800) 000-0000',
            href: phone ? getEditableAttributeValue(phone, 'href') : '',
        });
    });

    return areas;
}

function findServiceAreasParts(component) {
    if (!component) return null;

    const tag = getTagName(component);
    const isExplicitServiceAreasWidget = componentHasAttribute(component, 'data-service-areas-widget');
    if (!isExplicitServiceAreasWidget && tag !== 'section') return null;

    const heading = findDescendant(component, child => {
        const childTag = getTagName(child);
        return /^h[1-6]$/.test(childTag) && getComponentText(child).toLowerCase().includes('service areas');
    }) || findDescendant(component, child => /^h[1-6]$/.test(getTagName(child)));
    const headingText = heading ? getComponentText(heading).toLowerCase() : '';
    const grid = findServiceAreasGrid(component);
    const cards = findServiceAreaCards(component);
    const accordionAreas = grid ? [] : collectAccordionServiceAreas(component);
    const hasServiceAreasHeading = headingText.includes('service areas');
    const hasServiceAreaAccordion = accordionAreas.length >= 2;

    if (!grid && !hasServiceAreaAccordion && !isExplicitServiceAreasWidget) return null;
    if (!hasServiceAreasHeading && !hasServiceAreaAccordion && !isExplicitServiceAreasWidget) return null;

    const subheading = findDescendant(component, child => {
        if (getTagName(child) !== 'p') return false;
        const text = getComponentText(child).toLowerCase();
        return text.includes('region') || text.includes('coverage') || text.includes('contact');
    }) || findDescendant(component, child => getTagName(child) === 'p');

    return {
        heading,
        subheading,
        grid,
        cards,
        accordionAreas,
    };
}

function normalizeServiceAreaCards(component) {
    const grid = findServiceAreasGrid(component);
    if (!grid) return;

    grid.addStyle({
        display: 'grid',
        'grid-template-columns': 'repeat(auto-fit,minmax(132px,1fr))',
        gap: '12px',
        'align-items': 'stretch',
    });
}

function normalizeServiceAreasWidget(component) {
    const parts = findServiceAreasParts(component);
    if (!parts) return false;

    const wasExplicitServiceAreasWidget = componentHasAttribute(component, 'data-service-areas-widget');
    component.addAttributes({ 'data-service-areas-widget': 'true' });

    if (parts.grid) {
        normalizeServiceAreaCards(component);
        return false;
    }

    const hasServiceAreasHeading = getComponentText(parts.heading).toLowerCase().includes('service areas');
    const canUseExistingIntroCopy = hasServiceAreasHeading || wasExplicitServiceAreasWidget;
    const heading = canUseExistingIntroCopy ? (getComponentText(parts.heading) || 'Service Areas') : 'Service Areas';
    const subheading = canUseExistingIntroCopy
        ? (getComponentText(parts.subheading) || 'Click a region to see coverage and contact info')
        : 'Click a region to see coverage and contact info';
    component.components(serviceAreasSectionInnerHtml(heading, subheading, parts.accordionAreas));
    component.view?.render?.();
    return true;
}

function addServiceAreaCard(component) {
    if (!component) return;

    normalizeServiceAreasWidget(component);
    const grid = findServiceAreasGrid(component);
    if (!grid) return;

    const nextIndex = findServiceAreaCards(component).length + 1;
    grid.append(serviceAreaCardMarkup({
        name: `Service Area ${nextIndex}`,
        description: `Covering all cities in Service Area ${nextIndex}.`,
        phone: '(800) 000-0000',
        background: serviceAreaCardsBackgroundValue(component) || '#f8fafc',
    }));
    normalizeServiceAreaCards(component);
    component.view?.render?.();
}

function serviceAreaCardAt(component, index) {
    return findServiceAreaCards(component)[index] || null;
}

function getServiceAreaFieldValue(component, field) {
    const parts = findServiceAreasParts(component);
    if (!parts) return '';

    if (field.kind === 'service-area-heading') return getComponentText(parts.heading);
    if (field.kind === 'service-area-subheading') return getComponentText(parts.subheading);
    if (field.kind === 'service-area-card-background') return serviceAreaCardsBackgroundValue(component);

    const card = serviceAreaCardAt(component, field.index);
    const cardParts = serviceAreaCardParts(card);
    if (!cardParts) return '';

    if (field.kind === 'service-area-name') return cleanServiceAreaName(getComponentText(cardParts.name));
    if (field.kind === 'service-area-description') return getComponentText(cardParts.description);
    if (field.kind === 'service-area-phone') return getComponentText(cardParts.phone);
    if (field.kind === 'service-area-phone-href') return getEditableAttributeValue(cardParts.phone, 'href');

    return '';
}

function updateServiceAreaField(component, field, value) {
    const parts = findServiceAreasParts(component);
    if (!parts) return;

    if (field.kind === 'service-area-heading') {
        setComponentText(parts.heading, value);
        return;
    }

    if (field.kind === 'service-area-subheading') {
        setComponentText(parts.subheading, value);
        return;
    }

    if (field.kind === 'service-area-card-background') {
        updateServiceAreaCardsBackground(component, value);
        return;
    }

    const card = serviceAreaCardAt(component, field.index);
    const cardParts = serviceAreaCardParts(card);
    if (!cardParts) return;

    if (field.kind === 'service-area-name') {
        setComponentText(cardParts.name, value);
        return;
    }

    if (field.kind === 'service-area-description') {
        setComponentText(cardParts.description, value);
        return;
    }

    if (field.kind === 'service-area-phone') {
        setComponentText(cardParts.phone, value);
        if (!getEditableAttributeValue(cardParts.phone, 'href')?.trim().startsWith('tel:')) return;
        updateEditableAttribute(cardParts.phone, 'href', phoneHrefFromText(value));
        return;
    }

    if (field.kind === 'service-area-phone-href') {
        updateEditableAttribute(cardParts.phone, 'href', value);
    }
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

function getEditableFieldTarget(component, field) {
    if (!field) return { component: null, field: null };

    if (!Array.isArray(field.targetPath) || !field.field) {
        return { component, field };
    }

    let target = component;
    for (const index of field.targetPath) {
        target = childComponents(target)[index] || null;
        if (!target) break;
    }

    return { component: target, field: field.field };
}

function editorFieldKey(field) {
    const targetPath = Array.isArray(field?.targetPath) ? field.targetPath : [];
    const editableField = field?.field || field || {};

    return JSON.stringify({
        targetPath,
        kind: editableField.kind || '',
        name: editableField.name || '',
        index: editableField.index ?? '',
    });
}

function markEditorFieldInput(input, field) {
    const editableField = field?.field || field || {};

    input.dataset.editorFieldKey = editorFieldKey(field);
    input.dataset.editorFieldKind = editableField.kind || '';
    input.dataset.editorFieldName = editableField.name || '';
}

function syncEditorFieldInputs(field, value) {
    if (!(field?.kind || field?.field?.kind)) return;

    const key = editorFieldKey(field);

    document.querySelectorAll('#traits-wrap .editor-content-field').forEach(input => {
        if (input.dataset.editorFieldKey !== key) return;
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

    ({ component, field } = getEditableFieldTarget(component, field));
    if (!component || !field) return '';

    if (field.kind === 'text') return getComponentText(component);
    if (field.kind === 'attr') return getEditableAttributeValue(component, field.name);

    if (String(field.kind || '').startsWith('service-area-')) {
        return getServiceAreaFieldValue(component, field);
    }

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

    ({ component, field } = getEditableFieldTarget(component, field));
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

    if (String(field.kind || '').startsWith('service-area-')) {
        updateServiceAreaField(component, field, value);
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

editor.TraitManager.addType('editor-action-button', {
    createInput({ trait }) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'editor-action-button';
        button.textContent = trait.get('buttonLabel') || 'Add';
        button.addEventListener('click', event => {
            event.preventDefault();

            const component = editor.getSelected();
            if (!component) return;

            if (trait.get('action') === 'add-service-area-card') {
                addServiceAreaCard(component);
                const contentTraits = buildContentTraits(component);
                setComponentTraits(component, [...getSerializableTraits(component), ...contentTraits]);
                showRightPane('traits');
            }
        });
        return button;
    },
    onUpdate({ elInput, component }) {
        elInput.disabled = !component;
    },
});

function colorInputValue(value) {
    const text = String(value || '').trim();
    const fullHex = text.match(/^#([0-9a-f]{6})$/i);
    if (fullHex) return `#${fullHex[1]}`;

    const shortHex = text.match(/^#([0-9a-f]{3})$/i);
    if (shortHex) {
        return `#${shortHex[1].split('').map(char => char + char).join('')}`;
    }

    return '#ffffff';
}

editor.TraitManager.addType('editor-color-field', {
    createInput({ trait }) {
        const wrapper = document.createElement('div');
        wrapper.className = 'editor-color-field';

        const swatch = document.createElement('input');
        swatch.type = 'color';
        swatch.className = 'editor-color-swatch';

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'editor-content-field editor-color-text';
        input.placeholder = trait.get('placeholder') || '';
        markEditorFieldInput(input, trait.get('editorField'));

        swatch.addEventListener('input', () => {
            input.value = swatch.value;
            updateEditableField(editor.getSelected(), trait.get('editorField'), input.value);
        });
        input.addEventListener('input', () => {
            swatch.value = colorInputValue(input.value);
            updateEditableField(editor.getSelected(), trait.get('editorField'), input.value);
        });

        wrapper.append(swatch, input);
        return wrapper;
    },
    onUpdate({ elInput, component, trait }) {
        const value = getEditableFieldValue(component, trait.get('editorField'));
        const input = elInput.querySelector('.editor-color-text');
        const swatch = elInput.querySelector('.editor-color-swatch');
        if (input && document.activeElement !== input) input.value = value;
        if (input) input.placeholder = value ? (trait.get('placeholder') || '') : 'mixed';
        if (swatch && document.activeElement !== swatch) swatch.value = colorInputValue(value);
    },
});

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
const DEFAULT_HIDDEN_MEDIA_PICKER_EXTENSIONS = new Set(['webp']);

function resetMediaPickerHiddenExtensions() {
    mediaPicker.hiddenExtensions = new Set(DEFAULT_HIDDEN_MEDIA_PICKER_EXTENSIONS);
}

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
    resetMediaPickerHiddenExtensions();
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
    resetMediaPickerHiddenExtensions();
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

const CARD_MEDIA_SELECTOR = '[data-card-media="true"]';
const CARD_MEDIA_PICKER_SELECTOR = '[data-card-media-picker="true"]';
let cardMediaPickerOpenLock = 0;
let cardMediaOverlayLayer = null;

function componentByCanvasElement(element) {
    if (!element) return null;

    const id = element.getAttribute('id');
    if (id) {
        const byId = editor.getWrapper().find(`#${CSS.escape(id)}`)[0];
        if (byId) return byId;
    }

    let found = null;
    walkComponents(editor.getWrapper(), component => {
        if (!found && component.getEl?.() === element) found = component;
    });

    return found;
}

function isLegacyCardContainer(component) {
    if (getTagName(component) !== 'div') return false;

    const children = childComponents(component);
    const media = children[0];
    if (!media || getTagName(media) !== 'div') return false;

    const styles = component.getStyle?.() || {};
    const mediaStyles = media.getStyle?.() || {};
    const maxWidth = String(styles['max-width'] || styles.maxWidth || '').trim();
    const hasCardShell = maxWidth.includes('360')
        && String(styles.overflow || '').toLowerCase() === 'hidden';
    const hasMediaTop = String(mediaStyles.height || '').includes('180')
        || String(mediaStyles.background || '').includes('linear-gradient')
        || String(mediaStyles['background-image'] || '').includes('linear-gradient');
    const hasHeading = Boolean(findFirstDescendant(component, child => /^h[1-6]$/.test(getTagName(child))));

    return hasCardShell && hasMediaTop && hasHeading;
}

function isCardMediaAreaComponent(component) {
    const parent = component?.parent?.();
    return Boolean(componentHasAttribute(component, 'data-card-media') && parent && isLegacyCardContainer(parent));
}

function upgradeCardMediaAreas() {
    walkComponents(editor.getWrapper(), component => {
        if (!isLegacyCardContainer(component)) return;

        const media = childComponents(component)[0];
        const attributes = media.getAttributes?.() || {};
        if (attributes['data-card-media'] !== 'true') {
            media.addAttributes({ 'data-card-media': 'true' });
        }

        const mediaStyles = media.getStyle?.() || {};
        if (!mediaStyles.position) {
            media.addStyle({ position: 'relative' });
        }
    });
}

function cardMediaButtonHtml() {
    return '<svg fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>';
}

function styleCardMediaButton(button) {
    Object.assign(button.style, {
        position: 'absolute',
        left: '50%',
        top: '50%',
        transform: 'translate(-50%, -50%)',
        width: '46px',
        height: '46px',
        border: '1px solid rgba(148, 163, 184, .45)',
        borderRadius: '999px',
        background: 'rgba(255, 255, 255, .92)',
        color: '#1e40af',
        boxShadow: '0 10px 24px rgba(15, 23, 42, .22)',
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        cursor: 'pointer',
        zIndex: '5',
        padding: '0',
        pointerEvents: 'auto',
        transition: 'transform .15s ease, background .15s ease, box-shadow .15s ease',
    });

    const icon = button.querySelector('svg');
    if (icon) {
        Object.assign(icon.style, {
            width: '22px',
            height: '22px',
            pointerEvents: 'none',
        });
    }
}

function cardMediaOverlayRoot() {
    if (!cardMediaOverlayLayer) {
        cardMediaOverlayLayer = document.createElement('div');
        cardMediaOverlayLayer.id = 'card-media-picker-layer';
        Object.assign(cardMediaOverlayLayer.style, {
            position: 'fixed',
            inset: '0',
            zIndex: '9995',
            pointerEvents: 'none',
        });
        document.body.appendChild(cardMediaOverlayLayer);
    }

    return cardMediaOverlayLayer;
}

function openCardMediaPickerFromControl(event, mediaEl) {
    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation?.();

    const now = Date.now();
    if (now - cardMediaPickerOpenLock < 400) return;
    cardMediaPickerOpenLock = now;

    setTimeout(() => openCardMediaPickerForElement(mediaEl), 350);
}

function injectCardMediaPickers() {
    const canvasDocument = editor.Canvas.getDocument();
    const frameEl = editor.Canvas.getFrameEl?.() || document.querySelector('iframe.gjs-frame');
    const layer = cardMediaOverlayRoot();
    layer.innerHTML = '';

    if (!canvasDocument || !frameEl) return;

    const frameRect = frameEl.getBoundingClientRect();
    const scaleX = frameRect.width / (frameEl.clientWidth || frameRect.width || 1);
    const scaleY = frameRect.height / (frameEl.clientHeight || frameRect.height || 1);

    canvasDocument.querySelectorAll(CARD_MEDIA_SELECTOR).forEach(mediaEl => {
        const mediaComponent = componentByCanvasElement(mediaEl);
        if (!isCardMediaAreaComponent(mediaComponent)) return;

        mediaEl.style.position = mediaEl.style.position || 'relative';
        let mediaRect = mediaEl.getBoundingClientRect();
        if (mediaRect.width <= 0 || mediaRect.height <= 0) {
            const parentRect = mediaEl.parentElement?.getBoundingClientRect();
            if (!parentRect || parentRect.width <= 0 || parentRect.height <= 0) return;

            const fallbackHeight = Math.min(180, Math.max(80, parentRect.height * .45));
            mediaRect = {
                left: parentRect.left,
                top: parentRect.top,
                width: parentRect.width,
                height: fallbackHeight,
                bottom: parentRect.top + fallbackHeight,
            };
        }
        if (mediaRect.bottom < 0 || mediaRect.top > (frameEl.clientHeight || frameRect.height)) return;

        const button = document.createElement('button');
        button.type = 'button';
        button.innerHTML = cardMediaButtonHtml();
        button.setAttribute('data-card-media-picker', 'true');
        button.setAttribute('aria-label', 'Choose card image from uploads');
        button.title = 'Choose card image';
        styleCardMediaButton(button);
        Object.assign(button.style, {
            position: 'fixed',
            left: `${frameRect.left + (mediaRect.left + mediaRect.width / 2) * scaleX}px`,
            top: `${frameRect.top + (mediaRect.top + mediaRect.height / 2) * scaleY}px`,
            zIndex: '9995',
        });
        button.addEventListener('click', event => openCardMediaPickerFromControl(event, mediaEl), true);
        layer.appendChild(button);
    });
}

function applyCardMediaImage(component, item) {
    if (!component) return;

    component.addAttributes({ 'data-card-media': 'true' });
    component.addStyle({
        position: 'relative',
        'background-image': cssUrlForImage(item.url),
        'background-size': 'cover',
        'background-position': 'center',
        'background-repeat': 'no-repeat',
    });
    component.view?.render?.();
    scheduleCardMediaPickerInjection();
    showToast('Card image selected.');
}

function openCardMediaPickerForElement(mediaEl) {
    const component = componentByCanvasElement(mediaEl);
    if (!isCardMediaAreaComponent(component)) {
        showToast('Select a card image area first.', 'err');
        return;
    }

    editor.select(component);
    mediaPicker.active = {
        component,
        kind: 'image',
        getValue: () => getCssUrlValue(component.getStyle?.()?.['background-image'] || ''),
        onChoose: item => applyCardMediaImage(component, item),
    };
    resetMediaPickerHiddenExtensions();
    mediaPicker.search.value = '';
    mediaPicker.title.textContent = 'Choose Card Image';
    renderMediaPickerFilters('image');
    renderMediaPickerGallery();
    mediaPicker.overlay.classList.add('open');
    mediaPicker.overlay.setAttribute('aria-hidden', 'false');
    setTimeout(() => mediaPicker.search.focus(), 30);
}

function handleCardMediaPickerEvent(event) {
    const button = event.target.closest?.(CARD_MEDIA_PICKER_SELECTOR);
    if (!button) return;

    openCardMediaPickerFromControl(event, button.closest(CARD_MEDIA_SELECTOR));
}

function bindCardMediaPickerClicks() {
    const canvasDocument = editor.Canvas.getDocument();
    const canvasWindow = editor.Canvas.getWindow();
    if (!canvasDocument || !canvasWindow || canvasWindow.__cardMediaPickerBound) return;

    canvasWindow.__cardMediaPickerBound = true;
    canvasWindow.addEventListener('pointerdown', handleCardMediaPickerEvent, true);
    canvasWindow.addEventListener('mousedown', handleCardMediaPickerEvent, true);
    canvasWindow.addEventListener('click', handleCardMediaPickerEvent, true);
    canvasWindow.addEventListener('scroll', scheduleCardMediaPickerInjection, true);
    window.addEventListener('resize', scheduleCardMediaPickerInjection);
}

function scheduleCardMediaPickerInjection() {
    const refreshCardMediaPickers = () => {
        upgradeCardMediaAreas();
        bindCardMediaPickerClicks();
        injectCardMediaPickers();
    };

    setTimeout(refreshCardMediaPickers, 0);
    setTimeout(refreshCardMediaPickers, 200);
    setTimeout(refreshCardMediaPickers, 900);
}

editor.on('load', scheduleCardMediaPickerInjection);
editor.on('canvas:frame:load', scheduleCardMediaPickerInjection);
editor.on('component:add', scheduleCardMediaPickerInjection);
editor.on('component:selected', scheduleCardMediaPickerInjection);
editor.on('component:styleUpdate', scheduleCardMediaPickerInjection);
scheduleCardMediaPickerInjection();

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

function actionTrait(name, label, action, buttonLabel) {
    return {
        type: 'editor-action-button',
        name: CONTENT_TRAIT_PREFIX + name,
        label,
        action,
        buttonLabel,
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

function buildOwnContentTraits(component, options = {}) {
    const tag = getTagName(component);
    const traits = [];
    const existingTraitNames = options.ignoreExistingTraitNames
        ? new Set()
        : new Set(getSerializableTraits(component).map(trait => trait.name));
    const whyChoose = findWhyChooseParts(component);
    const benefitParts = findBenefitParts(component);
    const isTextElement = TEXT_SETTING_TAGS.has(tag) || component.get?.('type') === 'text';
    const hasTrait = name => existingTraitNames.has(name);
    const serviceAreas = findServiceAreasParts(component);

    if (serviceAreas) {
        normalizeServiceAreasWidget(component);
        const normalizedServiceAreas = findServiceAreasParts(component) || serviceAreas;
        const serviceAreaCards = findServiceAreaCards(component);

        traits.push(actionTrait('service_area_add_card', 'Cards', 'add-service-area-card', 'Add service area card'));
        traits.push(contentTrait('service_area_card_background', 'Card Background', {
            kind: 'service-area-card-background',
        }, {
            type: 'editor-color-field',
            placeholder: '#f8fafc',
        }));

        if (normalizedServiceAreas.heading) {
            traits.push(contentTrait('service_area_heading', 'Heading', { kind: 'service-area-heading' }, {
                type: 'editor-content-textarea',
                placeholder: 'Service areas heading',
            }));
        }

        if (normalizedServiceAreas.subheading) {
            traits.push(contentTrait('service_area_subheading', 'Subheading', { kind: 'service-area-subheading' }, {
                type: 'editor-content-textarea',
                placeholder: 'Service areas intro',
            }));
        }

        serviceAreaCards.forEach((card, index) => {
            traits.push(contentTrait(`service_area_${index}_name`, `Area ${index + 1} Name`, {
                kind: 'service-area-name',
                index,
            }, {
                type: 'editor-content-textarea',
                placeholder: 'Service area name',
            }));
            traits.push(contentTrait(`service_area_${index}_description`, `Area ${index + 1} Coverage`, {
                kind: 'service-area-description',
                index,
            }, {
                type: 'editor-content-textarea',
                placeholder: 'Coverage details',
            }));
            traits.push(contentTrait(`service_area_${index}_phone`, `Area ${index + 1} Phone`, {
                kind: 'service-area-phone',
                index,
            }, {
                placeholder: '(800) 000-0000',
            }));
            traits.push(contentTrait(`service_area_${index}_phone_href`, `Area ${index + 1} Phone Link`, {
                kind: 'service-area-phone-href',
                index,
            }, {
                placeholder: 'tel:8000000000',
            }));
        });

        return traits;
    }

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

function childComponentLabel(component) {
    const tag = getTagName(component) || component?.get?.('type') || 'element';

    if (/^h[1-6]$/.test(tag)) return 'Heading';
    if (tag === 'p') return 'Paragraph';
    if (tag === 'a') return 'Link';
    if (tag === 'img') return 'Image';
    if (tag === 'video') return 'Video';
    if (tag === 'button') return 'Button';
    if (tag === 'input') return 'Input';
    if (tag === 'textarea') return 'Textarea';
    if (tag === 'span' || component?.get?.('type') === 'text') return 'Text';

    return tag.toUpperCase();
}

function childTraitLabel(component, trait, sequence) {
    return `${childComponentLabel(component)} ${sequence} - ${trait.label}`;
}

function shouldStopAfterChildTraits(component) {
    const tag = getTagName(component);

    return TEXT_SETTING_TAGS.has(tag)
        || ['img', 'video', 'input', 'textarea'].includes(tag)
        || component.get?.('type') === 'text'
        || component.get?.('type') === 'image'
        || component.get?.('type') === 'video'
        || Boolean(findBenefitParts(component))
        || Boolean(findWhyChooseParts(component))
        || Boolean(findServiceAreasParts(component));
}

function wrapChildContentTrait(component, trait, targetPath, sequence) {
    const baseName = String(trait.name || '').replace(CONTENT_TRAIT_PREFIX, '');

    return {
        ...trait,
        name: `${CONTENT_TRAIT_PREFIX}child_${targetPath.join('_')}_${baseName}`,
        label: childTraitLabel(component, trait, sequence),
        editorField: {
            targetPath,
            field: trait.editorField,
        },
    };
}

function buildChildContentTraits(component) {
    const traits = [];
    const maxChildTraits = 40;
    const labelCounts = new Map();

    const collect = (child, targetPath) => {
        if (traits.length >= maxChildTraits) return;

        const childTraits = buildOwnContentTraits(child, { ignoreExistingTraitNames: true });
        if (childTraits.length) {
            const label = childComponentLabel(child);
            const sequence = (labelCounts.get(label) || 0) + 1;
            labelCounts.set(label, sequence);

            childTraits.forEach(trait => {
                if (traits.length < maxChildTraits) {
                    traits.push(wrapChildContentTrait(child, trait, targetPath, sequence));
                }
            });

            if (shouldStopAfterChildTraits(child)) return;
        }

        childComponents(child).forEach((grandchild, index) => collect(grandchild, [...targetPath, index]));
    };

    childComponents(component).forEach((child, index) => collect(child, [index]));

    return traits;
}

function buildContentTraits(component) {
    const ownTraits = buildOwnContentTraits(component);
    const tag = getTagName(component);
    const isServiceAreasWidget = Boolean(findServiceAreasParts(component));
    const isLeafEditable = TEXT_SETTING_TAGS.has(tag)
        || component.get?.('type') === 'text'
        || ['img', 'video', 'input', 'textarea'].includes(tag);

    if (isServiceAreasWidget || isLeafEditable || !childComponents(component).length) {
        return ownTraits;
    }

    return [...ownTraits, ...buildChildContentTraits(component)];
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

function normalizeAllServiceAreasWidgets(root = editor.getWrapper?.()) {
    if (!root) return;

    normalizeServiceAreasWidget(root);
    walkComponents(root, child => normalizeServiceAreasWidget(child));
}

function scheduleServiceAreasWidgetNormalization(root = null) {
    setTimeout(() => normalizeAllServiceAreasWidgets(root || editor.getWrapper?.()), 0);
    setTimeout(() => normalizeAllServiceAreasWidgets(root || editor.getWrapper?.()), 250);
}

editor.on('load', () => scheduleServiceAreasWidgetNormalization());
editor.on('canvas:frame:load', () => scheduleServiceAreasWidgetNormalization());
editor.on('component:add', component => scheduleServiceAreasWidgetNormalization(component));
scheduleServiceAreasWidgetNormalization();

editor.on('component:selected', component => {
    if (!component) return;

    syncBackgroundShorthandToLonghands(component);
    normalizeServiceAreasWidget(component);

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
    label: 'Hero', category: 'Elements',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="13" x2="22" y2="13"/><circle cx="12" cy="8.5" r="2"/></svg>`,
    content: `<section style="padding:100px 24px;background:linear-gradient(135deg,#1e40af,#7c3aed);color:#fff;text-align:center;">
  <h1 style="font-size:52px;font-weight:800;margin-bottom:16px;line-height:1.1;">Your Hero Headline</h1>
  <p style="font-size:20px;opacity:.85;max-width:560px;margin:0 auto 36px;">Subheadline text that supports the main headline and drives action.</p>
  <a href="#" style="display:inline-block;padding:14px 36px;background:#fff;color:#1e40af;border-radius:10px;font-weight:700;font-size:15px;text-decoration:none;">Get Started</a>
</section>`,
});

bm.add('pb-row', {
    label: 'Row', category: 'Layout',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="10" rx="1.5"/><line x1="8" y1="7" x2="8" y2="17"/><line x1="16" y1="7" x2="16" y2="17"/></svg>`,
    content: `<div style="display:flex;flex-direction:row;flex-wrap:wrap;align-items:stretch;gap:24px;padding:24px;">
  <div style="flex:1 1 240px;min-width:0;min-height:80px;padding:16px;border:1px dashed #cbd5e1;border-radius:8px;">Row item</div>
  <div style="flex:1 1 240px;min-width:0;min-height:80px;padding:16px;border:1px dashed #cbd5e1;border-radius:8px;">Row item</div>
</div>`,
});

bm.add('pb-2cols', {
    label: '1 Column', category: 'Layout',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="5" y="4" width="14" height="16" rx="1.5"/></svg>`,
    content: `<div style="padding:40px 24px;">
  <div style="max-width:960px;margin:0 auto;padding:24px;background:#f8fafc;border-radius:10px;"><h3 style="font-weight:600;margin-bottom:8px;">Column</h3><p style="color:#64748b;font-size:15px;line-height:1.6;">Add your content here.</p></div>
</div>`,
});

bm.add('pb-3cols', {
    label: '2 Columns', category: 'Layout',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="4" width="9" height="16" rx="1.5"/><rect x="13" y="4" width="9" height="16" rx="1.5"/></svg>`,
    content: `<div style="display:flex;flex-wrap:wrap;gap:24px;padding:40px 24px;">
  <div style="flex:1 1 280px;min-width:0;padding:24px;background:#f8fafc;border-radius:10px;"><h3 style="font-weight:600;margin-bottom:8px;">Column One</h3><p style="color:#64748b;font-size:15px;line-height:1.6;">Add your content here.</p></div>
  <div style="flex:1 1 280px;min-width:0;padding:24px;background:#f8fafc;border-radius:10px;"><h3 style="font-weight:600;margin-bottom:8px;">Column Two</h3><p style="color:#64748b;font-size:15px;line-height:1.6;">Add your content here.</p></div>
</div>`,
});

bm.add('pb-card', {
    label: 'Card', category: 'Elements',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><line x1="3" y1="9" x2="21" y2="9"/></svg>`,
    content: `<div style="background:#fff;border-radius:12px;box-shadow:0 2px 16px rgba(0,0,0,.08);overflow:hidden;max-width:360px;">
  <div data-card-media="true" style="height:180px;position:relative;background:linear-gradient(135deg,#dbeafe,#ede9fe);"></div>
  <div style="padding:24px;">
    <h3 style="font-size:18px;font-weight:700;margin-bottom:8px;">Card Title</h3>
    <p style="color:#64748b;font-size:14px;line-height:1.6;margin-bottom:16px;">Card description goes here. Edit this text.</p>
    <a href="#" style="display:inline-block;padding:8px 20px;background:#4f46e5;color:#fff;border-radius:7px;font-size:13px;font-weight:600;text-decoration:none;">Learn More</a>
  </div>
</div>`,
});

bm.add('pb-div', {
    label: 'Div', category: 'Elements',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="4" y="5" width="16" height="14" rx="1.5"/><path d="M8 9h8M8 13h5"/></svg>`,
    content: '<div style="min-height:80px;padding:24px;border:1px dashed #cbd5e1;border-radius:8px;color:#475569;">Div content</div>',
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
    label: 'Service Areas', category: 'Service Sections',
    media: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="4" width="20" height="5" rx="1.5"/><rect x="2" y="10" width="20" height="5" rx="1.5"/><rect x="2" y="16" width="20" height="5" rx="1.5"/></svg>`,
    content: `<section data-service-areas-widget="true" style="background:#1e3a5f;padding:40px 24px;">
  ${serviceAreasSectionInnerHtml('Service Areas', 'Click a region to see coverage and contact info', [
    { name: 'North County', description: 'Coverage for homes and businesses across North County.', phone: '(800) 000-0000', href: 'tel:8000000000' },
    { name: 'South District', description: 'Fast local help throughout the South District.', phone: '(800) 000-0000', href: 'tel:8000000000' },
    { name: 'East Valley', description: 'Same-day service across East Valley neighborhoods.', phone: '(800) 000-0000', href: 'tel:8000000000' },
    { name: 'West Side', description: 'Reliable coverage for the West Side service area.', phone: '(800) 000-0000', href: 'tel:8000000000' },
  ])}
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

function findStyleSectorByTitle(title) {
    return [...document.querySelectorAll('#styles-wrap .gjs-sm-sector')]
        .find(sector => sector.querySelector('.gjs-sm-sector-title')?.textContent.trim() === title) || null;
}

const SPACING_SIDE_ORDER = ['top', 'right', 'bottom', 'left'];
const SPACING_UNITS = {
    margin: ['px', '%', 'em', 'rem', 'auto'],
    padding: ['px', '%', 'em', 'rem'],
};
const SPACING_ALL_HTML = `
<div class="sa-row">
  <div class="sa-label-wrap">
    <label class="sa-label" for="sa-margin-value">Margin all</label>
    <button type="button" class="sa-clear-btn" data-spacing-clear="margin" title="Clear margin all" aria-label="Clear margin all">x</button>
  </div>
  <div class="sa-control">
    <input type="number" id="sa-margin-value" step="1" placeholder="mixed">
    <select id="sa-margin-unit">
      ${SPACING_UNITS.margin.map(unit => `<option value="${unit}">${unit}</option>`).join('')}
    </select>
  </div>
</div>
<div class="sa-row">
  <div class="sa-label-wrap">
    <label class="sa-label" for="sa-padding-value">Padding all</label>
    <button type="button" class="sa-clear-btn" data-spacing-clear="padding" title="Clear padding all" aria-label="Clear padding all">x</button>
  </div>
  <div class="sa-control">
    <input type="number" id="sa-padding-value" step="1" placeholder="mixed">
    <select id="sa-padding-unit">
      ${SPACING_UNITS.padding.map(unit => `<option value="${unit}">${unit}</option>`).join('')}
    </select>
  </div>
</div>`;

let syncingSpacingAllEditor = false;

function spacingAllControls() {
    const wrap = document.getElementById('sa-wrap');
    if (!wrap) return null;

    return {
        wrap,
        marginValue: document.getElementById('sa-margin-value'),
        marginUnit: document.getElementById('sa-margin-unit'),
        marginClear: wrap.querySelector('[data-spacing-clear="margin"]'),
        paddingValue: document.getElementById('sa-padding-value'),
        paddingUnit: document.getElementById('sa-padding-unit'),
        paddingClear: wrap.querySelector('[data-spacing-clear="padding"]'),
        inputs: [...wrap.querySelectorAll('input, select, button')],
    };
}

function spacingPropertyName(kind, side) {
    return `${kind}-${side}`;
}

function camelStyleProperty(property) {
    return property.replace(/-([a-z])/g, (_, char) => char.toUpperCase());
}

function spacingValueFromStyle(styles, property) {
    return styles[property] ?? styles[camelStyleProperty(property)] ?? '';
}

function splitSpacingShorthand(value) {
    const parts = String(value || '').trim().split(/\s+/).filter(Boolean);
    if (parts.length === 0) return null;
    if (parts.length === 1) return [parts[0], parts[0], parts[0], parts[0]];
    if (parts.length === 2) return [parts[0], parts[1], parts[0], parts[1]];
    if (parts.length === 3) return [parts[0], parts[1], parts[2], parts[1]];
    return [parts[0], parts[1], parts[2], parts[3]];
}

function componentSpacingValues(component, kind) {
    if (!component) return SPACING_SIDE_ORDER.map(() => '');

    const styles = component.getStyle?.() || {};
    const shorthand = splitSpacingShorthand(spacingValueFromStyle(styles, kind));

    return SPACING_SIDE_ORDER.map((side, index) => {
        const explicit = spacingValueFromStyle(styles, spacingPropertyName(kind, side));
        if (explicit !== '' && explicit !== undefined && explicit !== null) return String(explicit).trim();
        return shorthand?.[index] || '';
    });
}

function parseSpacingValue(value, fallbackUnit = 'px') {
    const text = String(value || '').trim().toLowerCase();
    if (!text) return { number: '', unit: fallbackUnit, css: '' };
    if (text === 'auto') return { number: '', unit: 'auto', css: 'auto' };

    const match = text.match(/^(-?\d*\.?\d+)(px|%|em|rem)?$/);
    if (!match) return { number: '', unit: fallbackUnit, css: text };

    const unit = match[2] || fallbackUnit;
    return {
        number: match[1],
        unit,
        css: `${match[1]}${unit}`,
    };
}

function commonSpacingValue(component, kind) {
    const values = componentSpacingValues(component, kind).filter(value => value !== '');
    if (values.length !== SPACING_SIDE_ORDER.length) return null;

    const first = values[0].toLowerCase();
    return values.every(value => value.toLowerCase() === first) ? values[0] : null;
}

function setSpacingControlKind(kind, component = editor.getSelected()) {
    const controls = spacingAllControls();
    if (!controls) return;

    const valueInput = controls[`${kind}Value`];
    const unitSelect = controls[`${kind}Unit`];
    const common = commonSpacingValue(component, kind);

    valueInput.disabled = !component;
    unitSelect.disabled = !component;

    if (!component) {
        valueInput.value = '';
        valueInput.placeholder = '';
        unitSelect.value = SPACING_UNITS[kind][0];
        return;
    }

    if (!common) {
        if (unitSelect.value === 'auto') unitSelect.value = SPACING_UNITS[kind][0];
        if (document.activeElement !== valueInput) valueInput.value = '';
        valueInput.placeholder = 'mixed';
        valueInput.disabled = false;
        return;
    }

    const parsed = parseSpacingValue(common, unitSelect.value);
    if (SPACING_UNITS[kind].includes(parsed.unit)) unitSelect.value = parsed.unit;

    if (parsed.unit === 'auto') {
        valueInput.value = '';
        valueInput.placeholder = 'auto';
        valueInput.disabled = true;
        return;
    }

    if (document.activeElement !== valueInput) valueInput.value = parsed.number;
    valueInput.placeholder = '';
    valueInput.disabled = false;
}

function setSpacingAllDisabled(disabled) {
    const controls = spacingAllControls();
    if (!controls) return;
    controls.wrap.classList.toggle('is-disabled', disabled);
    controls.inputs.forEach(input => { input.disabled = disabled; });
}

function spacingKindHasAnyValue(component, kind) {
    if (!component) return false;

    return componentSpacingValues(component, kind)
        .some(value => String(value || '').trim() !== '');
}

function syncSpacingClearButtons(component = editor.getSelected()) {
    const controls = spacingAllControls();
    if (!controls) return;

    ['margin', 'padding'].forEach(kind => {
        const button = controls[`${kind}Clear`];
        if (!button) return;

        const hasValue = spacingKindHasAnyValue(component, kind);
        button.classList.toggle('visible', hasValue);
        button.disabled = !hasValue;
    });
}

function selectedSpacingCssValue(kind) {
    const controls = spacingAllControls();
    if (!controls) return '';

    const valueInput = controls[`${kind}Value`];
    const unitSelect = controls[`${kind}Unit`];
    const unit = unitSelect.value;

    if (unit === 'auto') return 'auto';

    const rawValue = String(valueInput.value || '').trim();
    if (rawValue === '') return '';

    return `${rawValue}${unit}`;
}

function syncNativeSpacingFields(kind, cssValue) {
    const parsed = parseSpacingValue(cssValue);

    SPACING_SIDE_ORDER.forEach(side => {
        const property = document.querySelector(`#styles-wrap .gjs-sm-property__${kind}-${side}`);
        if (!property) return;

        const input = property.querySelector('input');
        const select = property.querySelector('select');

        if (input && document.activeElement !== input) {
            input.value = parsed.unit === 'auto' ? '' : parsed.number;
        }

        if (select && SPACING_UNITS[kind].includes(parsed.unit) && document.activeElement !== select) {
            select.value = parsed.unit;
        }
    });
}

function marginSideProperty(side) {
    return document.querySelector(`#styles-wrap .gjs-sm-property__margin-${side}`);
}

function setNativeMarginSideField(side, cssValue) {
    const property = marginSideProperty(side);
    if (!property) return;

    const parsed = parseSpacingValue(cssValue);
    const input = property.querySelector('input');
    const select = property.querySelector('select');

    if (input && document.activeElement !== input) {
        input.value = parsed.unit === 'auto' ? '' : parsed.number;
        input.placeholder = parsed.unit === 'auto' ? 'auto' : '';
    }

    if (select && SPACING_UNITS.margin.includes(parsed.unit) && document.activeElement !== select) {
        select.value = parsed.unit;
    }
}

function clearComponentStyleProperty(component, property) {
    if (!component || !property) return;

    const styles = { ...(component.getStyle?.() || {}) };
    delete styles[property];
    delete styles[camelStyleProperty(property)];

    if (typeof component.setStyle === 'function') {
        component.setStyle(styles);
    } else {
        component.addStyle({ [property]: '' });
    }
}

function syncMarginAutoButtons(component = editor.getSelected()) {
    const values = componentSpacingValues(component, 'margin');

    SPACING_SIDE_ORDER.forEach((side, index) => {
        const value = String(values[index] || '').trim().toLowerCase();
        const isAuto = value === 'auto';
        const property = marginSideProperty(side);
        const button = property?.querySelector('.ma-auto-btn');

        if (button) {
            button.classList.toggle('active', isAuto);
            button.disabled = !component;
        }

        if (isAuto) {
            setNativeMarginSideField(side, 'auto');
        } else {
            const input = property?.querySelector('input');
            if (input && document.activeElement !== input && input.placeholder === 'auto') {
                input.placeholder = '';
            }
        }
    });
}

function syncSpacingAllControls(component = editor.getSelected()) {
    const controls = spacingAllControls();
    if (!controls) return;

    syncingSpacingAllEditor = true;
    setSpacingAllDisabled(!component);
    setSpacingControlKind('margin', component);
    setSpacingControlKind('padding', component);
    syncSpacingClearButtons(component);
    syncMarginAutoButtons(component);
    syncingSpacingAllEditor = false;
}

function applySpacingAll(kind) {
    if (syncingSpacingAllEditor) return;

    const component = editor.getSelected();
    if (!component) return;

    const cssValue = selectedSpacingCssValue(kind);
    if (cssValue === '') {
        syncSpacingAllControls(component);
        return;
    }

    const patch = {};
    SPACING_SIDE_ORDER.forEach(side => {
        patch[spacingPropertyName(kind, side)] = cssValue;
    });

    component.addStyle(patch);
    syncNativeSpacingFields(kind, cssValue);
    syncSpacingAllControls(component);
}

function clearSpacingAll(kind) {
    const component = editor.getSelected();
    if (!component || !['margin', 'padding'].includes(kind)) return;

    clearComponentStyleProperty(component, kind);
    SPACING_SIDE_ORDER.forEach(side => clearComponentStyleProperty(component, spacingPropertyName(kind, side)));
    syncNativeSpacingFields(kind, '');

    if (kind === 'margin') {
        SPACING_SIDE_ORDER.forEach(side => setNativeMarginSideField(side, ''));
    }

    syncSpacingAllControls(component);
}

function bindSpacingAllEvents(wrap) {
    ['margin', 'padding'].forEach(kind => {
        const valueInput = wrap.querySelector(`#sa-${kind}-value`);
        const unitSelect = wrap.querySelector(`#sa-${kind}-unit`);
        const clearButton = wrap.querySelector(`[data-spacing-clear="${kind}"]`);

        valueInput?.addEventListener('input', () => applySpacingAll(kind));
        valueInput?.addEventListener('change', () => applySpacingAll(kind));
        unitSelect?.addEventListener('change', () => {
            const isAuto = unitSelect.value === 'auto';
            if (valueInput) {
                valueInput.disabled = isAuto;
                valueInput.placeholder = isAuto ? 'auto' : '';
            }
            applySpacingAll(kind);
        });
        clearButton?.addEventListener('click', event => {
            event.preventDefault();
            clearSpacingAll(kind);
        });
    });
}

function toggleMarginSideAuto(side) {
    const component = editor.getSelected();
    if (!component || !SPACING_SIDE_ORDER.includes(side)) return;

    const property = spacingPropertyName('margin', side);
    const values = componentSpacingValues(component, 'margin');
    const sideIndex = SPACING_SIDE_ORDER.indexOf(side);
    const isAuto = String(values[sideIndex] || '').trim().toLowerCase() === 'auto';

    if (isAuto) {
        clearComponentStyleProperty(component, property);
        setNativeMarginSideField(side, '');
    } else {
        component.addStyle({ [property]: 'auto' });
        setNativeMarginSideField(side, 'auto');
    }

    syncMarginAutoButtons(component);
    syncSpacingAllControls(component);
}

function injectMarginAutoButtons() {
    SPACING_SIDE_ORDER.forEach(side => {
        const property = marginSideProperty(side);
        if (!property || property.querySelector('.ma-auto-btn')) return;

        const fields = property.querySelector(':scope > .gjs-fields');
        if (!fields) return;

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'ma-auto-btn';
        button.dataset.marginAutoSide = side;
        button.textContent = 'auto';
        button.title = `Toggle margin ${side} auto`;
        button.addEventListener('click', event => {
            event.preventDefault();
            toggleMarginSideAuto(side);
        });

        fields.appendChild(button);
    });

    syncMarginAutoButtons();
}

function injectSpacingAllEditor() {
    const existing = document.getElementById('sa-wrap');
    if (existing) {
        injectMarginAutoButtons();
        syncSpacingAllControls();
        return;
    }

    const dimensionSector = findStyleSectorByTitle('Dimension');
    const props = dimensionSector?.querySelector('.gjs-sm-properties');
    if (!props) return;

    const wrap = document.createElement('div');
    wrap.id = 'sa-wrap';
    wrap.innerHTML = SPACING_ALL_HTML;

    const marginProperty = props.querySelector('.gjs-sm-property__margin');
    if (marginProperty) {
        props.insertBefore(wrap, marginProperty);
    } else {
        props.appendChild(wrap);
    }

    bindSpacingAllEvents(wrap);
    injectMarginAutoButtons();

    props.addEventListener('input', event => {
        if (event.target.closest?.('.gjs-sm-property__margin, .gjs-sm-property__padding')) {
            setTimeout(() => {
                syncSpacingAllControls(editor.getSelected());
                syncMarginAutoButtons(editor.getSelected());
            }, 0);
        }
    });
    props.addEventListener('change', event => {
        if (event.target.closest?.('.gjs-sm-property__margin, .gjs-sm-property__padding')) {
            setTimeout(() => {
                syncSpacingAllControls(editor.getSelected());
                syncMarginAutoButtons(editor.getSelected());
            }, 0);
        }
    });

    syncSpacingAllControls();
}

function scheduleSpacingAllInjection() {
    setTimeout(injectSpacingAllEditor, 0);
    setTimeout(injectSpacingAllEditor, 150);
}

editor.on('load', scheduleSpacingAllInjection);
editor.on('component:selected', scheduleSpacingAllInjection);
editor.on('component:styleUpdate', syncSpacingAllControls);
editor.on('component:styleUpdate', syncMarginAutoButtons);

const BORDER_RADIUS_CSS_CORNERS = [
    ['top-left', 'Top left'],
    ['top-right', 'Top right'],
    ['bottom-right', 'Bottom right'],
    ['bottom-left', 'Bottom left'],
];
const BORDER_RADIUS_CORNERS = [
    ['top-left', 'Top left'],
    ['top-right', 'Top right'],
    ['bottom-left', 'Bottom left'],
    ['bottom-right', 'Bottom right'],
];
const BORDER_RADIUS_SHORTHAND_INDEX = new Map(
    BORDER_RADIUS_CSS_CORNERS.map(([corner], index) => [corner, index])
);
const BORDER_RADIUS_UNITS = ['px', '%'];
const BORDER_RADIUS_HTML = `
<div class="br-title">Corner radius</div>
<div class="br-grid">
  ${BORDER_RADIUS_CORNERS.map(([corner, label]) => `
  <label class="br-corner">
    <span class="br-label">${label}</span>
    <span class="br-control">
      <input type="number" class="br-value" data-radius-corner="${corner}" step="1" min="0">
      <select class="br-unit" data-radius-unit="${corner}">
        ${BORDER_RADIUS_UNITS.map(unit => `<option value="${unit}">${unit}</option>`).join('')}
      </select>
    </span>
  </label>`).join('')}
</div>`;

let borderRadiusEditorInjected = false;
let syncingBorderRadiusEditor = false;

function radiusCornerProperty(corner) {
    return `border-${corner}-radius`;
}

function parseRadiusValue(value, fallbackUnit = 'px') {
    const text = String(value || '').trim().toLowerCase();
    if (!text) return { number: '', unit: fallbackUnit, css: '' };

    const match = text.match(/^(-?\d*\.?\d+)(px|%)?$/);
    if (!match) return { number: '', unit: fallbackUnit, css: text };

    const number = Math.max(0, parseFloat(match[1]));
    const cleanNumber = Number.isInteger(number) ? String(number) : String(number);
    const unit = match[2] || fallbackUnit;

    return {
        number: cleanNumber,
        unit,
        css: `${cleanNumber}${unit}`,
    };
}

function splitRadiusShorthand(value) {
    const mainRadius = String(value || '').split('/')[0].trim();
    const tokens = splitCssWhitespace(mainRadius);

    if (!tokens.length) return null;
    if (tokens.length === 1) return [tokens[0], tokens[0], tokens[0], tokens[0]];
    if (tokens.length === 2) return [tokens[0], tokens[1], tokens[0], tokens[1]];
    if (tokens.length === 3) return [tokens[0], tokens[1], tokens[2], tokens[1]];
    return [tokens[0], tokens[1], tokens[2], tokens[3]];
}

function componentRadiusValues(component) {
    if (!component) return BORDER_RADIUS_CORNERS.map(() => '');

    const styles = component.getStyle?.() || {};
    const shorthand = splitRadiusShorthand(cssPropertyValue(styles, 'border-radius'));

    return BORDER_RADIUS_CORNERS.map(([corner]) => {
        const explicit = cssPropertyValue(styles, radiusCornerProperty(corner));
        if (explicit !== '' && explicit !== undefined && explicit !== null) return String(explicit).trim();
        return shorthand?.[BORDER_RADIUS_SHORTHAND_INDEX.get(corner)] || '';
    });
}

function borderRadiusControls() {
    const wrap = document.getElementById('br-wrap');
    if (!wrap) return null;

    return {
        wrap,
        inputs: [...wrap.querySelectorAll('input, select')],
    };
}

function setBorderRadiusEditorDisabled(disabled) {
    const controls = borderRadiusControls();
    if (!controls) return;

    controls.wrap.classList.toggle('is-disabled', disabled);
    controls.inputs.forEach(input => { input.disabled = disabled; });
}

function syncNativeBorderRadiusField(component = editor.getSelected()) {
    const values = componentRadiusValues(component).filter(value => value !== '');
    const input = document.querySelector('#styles-wrap .gjs-sm-property__border-radius input');
    const select = document.querySelector('#styles-wrap .gjs-sm-property__border-radius select');
    if (!input || !select) return;

    if (!component || values.length !== BORDER_RADIUS_CORNERS.length) {
        if (document.activeElement !== input) input.value = '';
        input.placeholder = component ? 'mixed' : '';
        if (BORDER_RADIUS_UNITS.includes(select.value) === false) select.value = 'px';
        return;
    }

    const first = values[0].toLowerCase();
    if (!values.every(value => value.toLowerCase() === first)) {
        if (document.activeElement !== input) input.value = '';
        input.placeholder = 'mixed';
        return;
    }

    const parsed = parseRadiusValue(values[0], select.value);
    if (BORDER_RADIUS_UNITS.includes(parsed.unit) && document.activeElement !== select) select.value = parsed.unit;
    if (document.activeElement !== input) input.value = parsed.number;
    input.placeholder = '';
}

function syncBorderRadiusControls(component = editor.getSelected()) {
    const controls = borderRadiusControls();
    if (!controls) return;

    setBorderRadiusEditorDisabled(!component);
    syncingBorderRadiusEditor = true;

    const values = componentRadiusValues(component);
    BORDER_RADIUS_CORNERS.forEach(([corner], index) => {
        const input = controls.wrap.querySelector(`[data-radius-corner="${corner}"]`);
        const select = controls.wrap.querySelector(`[data-radius-unit="${corner}"]`);
        if (!input || !select) return;

        if (!component) {
            input.value = '';
            input.placeholder = '';
            select.value = 'px';
            return;
        }

        const parsed = parseRadiusValue(values[index], select.value || 'px');
        if (BORDER_RADIUS_UNITS.includes(parsed.unit) && document.activeElement !== select) select.value = parsed.unit;
        if (document.activeElement !== input) input.value = parsed.number;
        input.placeholder = parsed.css && !parsed.number ? parsed.css : '';
    });

    syncNativeBorderRadiusField(component);
    syncingBorderRadiusEditor = false;
}

function readRadiusCornerControl(corner) {
    const input = document.querySelector(`[data-radius-corner="${corner}"]`);
    const select = document.querySelector(`[data-radius-unit="${corner}"]`);
    if (!input || !select) return '';

    const value = String(input.value || '').trim();
    if (value === '') return '';

    return parseRadiusValue(`${value}${select.value || 'px'}`).css;
}

function applyRadiusCorner(corner) {
    if (syncingBorderRadiusEditor) return;

    const component = editor.getSelected();
    if (!component) return;

    const property = radiusCornerProperty(corner);
    const cssValue = readRadiusCornerControl(corner);

    if (cssValue) {
        component.addStyle({ [property]: cssValue });
    } else {
        clearComponentStyleProperty(component, property);
    }

    syncBorderRadiusControls(component);
}

function bindBorderRadiusEvents(wrap) {
    BORDER_RADIUS_CORNERS.forEach(([corner]) => {
        wrap.querySelector(`[data-radius-corner="${corner}"]`)?.addEventListener('input', () => applyRadiusCorner(corner));
        wrap.querySelector(`[data-radius-corner="${corner}"]`)?.addEventListener('change', () => applyRadiusCorner(corner));
        wrap.querySelector(`[data-radius-unit="${corner}"]`)?.addEventListener('change', () => applyRadiusCorner(corner));
    });
}

function injectBorderRadiusEditor() {
    if (borderRadiusEditorInjected) {
        syncBorderRadiusControls();
        return;
    }

    const borderSector = findStyleSectorByTitle('Border & Shadow');
    const props = borderSector?.querySelector('.gjs-sm-properties');
    if (!props) return;

    const wrap = document.createElement('div');
    wrap.id = 'br-wrap';
    wrap.innerHTML = BORDER_RADIUS_HTML;

    const radiusProperty = props.querySelector('.gjs-sm-property__border-radius');
    const borderProperty = props.querySelector('.gjs-sm-property__border');
    if (radiusProperty) radiusProperty.insertAdjacentElement('afterend', wrap);
    else if (borderProperty) borderProperty.insertAdjacentElement('beforebegin', wrap);
    else props.appendChild(wrap);

    bindBorderRadiusEvents(wrap);
    if (!props.dataset.borderRadiusEditorNativeSync) {
        props.dataset.borderRadiusEditorNativeSync = 'true';
        props.addEventListener('input', event => {
            if (event.target.closest?.('.gjs-sm-property__border-radius')) {
                setTimeout(() => syncBorderRadiusControls(editor.getSelected()), 0);
            }
        });
        props.addEventListener('change', event => {
            if (event.target.closest?.('.gjs-sm-property__border-radius')) {
                setTimeout(() => syncBorderRadiusControls(editor.getSelected()), 0);
            }
        });
    }

    borderRadiusEditorInjected = true;
    syncBorderRadiusControls();
}

function scheduleBorderRadiusEditorInjection() {
    setTimeout(injectBorderRadiusEditor, 0);
    setTimeout(injectBorderRadiusEditor, 150);
}

editor.on('load', scheduleBorderRadiusEditorInjection);
editor.on('component:selected', scheduleBorderRadiusEditorInjection);
editor.on('component:styleUpdate', syncBorderRadiusControls);

const BORDER_EDGE_LABELS = {
    all: 'All',
    top: 'Top',
    right: 'Right',
    bottom: 'Bottom',
    left: 'Left',
};
const BORDER_EDGE_ORDER = ['top', 'right', 'bottom', 'left'];
const BORDER_STYLE_OPTIONS = ['none', 'solid', 'dashed', 'dotted', 'double'];
const BORDER_EDITOR_HTML = `
<div class="be-sides">
  ${Object.entries(BORDER_EDGE_LABELS).map(([edge, label]) => `<button type="button" class="be-side" data-border-edge="${edge}">${label}</button>`).join('')}
</div>
<div class="be-preview-row">
  <div id="be-preview" aria-hidden="true"></div>
  <div id="be-target-label">All edges</div>
</div>
<div class="be-control">
  <div class="be-control-head">
    <span class="be-label">Width</span>
    <input type="range" id="be-width-range" min="0" max="40" step="1" value="0">
    <input type="number" id="be-width-number" min="0" step="1" value="0">
  </div>
</div>
<div class="be-row">
  <span class="be-label">Style</span>
  <select id="be-style">
    ${BORDER_STYLE_OPTIONS.map(style => `<option value="${style}">${style.charAt(0).toUpperCase()}${style.slice(1)}</option>`).join('')}
  </select>
</div>
<div class="be-row">
  <span class="be-label">Color</span>
  <input type="color" id="be-color" value="#000000">
</div>`;

let borderEditorInjected = false;
let activeBorderEdge = 'all';
let syncingBorderEditor = false;

function borderEditorControls() {
    const wrap = document.getElementById('be-wrap');
    if (!wrap) return null;

    return {
        wrap,
        buttons: [...wrap.querySelectorAll('[data-border-edge]')],
        widthRange: document.getElementById('be-width-range'),
        widthNumber: document.getElementById('be-width-number'),
        style: document.getElementById('be-style'),
        color: document.getElementById('be-color'),
        preview: document.getElementById('be-preview'),
        targetLabel: document.getElementById('be-target-label'),
        inputs: [...wrap.querySelectorAll('input, select, button')],
    };
}

function setBorderEditorDisabled(disabled) {
    const controls = borderEditorControls();
    if (!controls) return;
    controls.wrap.classList.toggle('is-disabled', disabled);
    controls.inputs.forEach(input => { input.disabled = disabled; });
}

function cssPropertyValue(styles, property) {
    const camel = property.replace(/-([a-z])/g, (_, char) => char.toUpperCase());
    return styles[property] ?? styles[camel] ?? '';
}

function splitCssWhitespace(value) {
    const tokens = [];
    let token = '';
    let depth = 0;
    let quote = '';

    for (const char of String(value || '').trim()) {
        if (quote) {
            token += char;
            if (char === quote) quote = '';
            continue;
        }

        if (char === '"' || char === "'") {
            quote = char;
            token += char;
        } else if (char === '(') {
            depth++;
            token += char;
        } else if (char === ')') {
            depth = Math.max(0, depth - 1);
            token += char;
        } else if (/\s/.test(char) && depth === 0) {
            if (token) {
                tokens.push(token);
                token = '';
            }
        } else {
            token += char;
        }
    }

    if (token) tokens.push(token);
    return tokens;
}

function cssQuadValue(value, edge) {
    const tokens = splitCssWhitespace(value);
    if (!tokens.length || !BORDER_EDGE_ORDER.includes(edge)) return '';

    const index = BORDER_EDGE_ORDER.indexOf(edge);
    if (tokens.length === 1) return tokens[0];
    if (tokens.length === 2) return index === 0 || index === 2 ? tokens[0] : tokens[1];
    if (tokens.length === 3) return index === 0 ? tokens[0] : index === 2 ? tokens[2] : tokens[1];
    return tokens[index] || '';
}

function normalizeBorderWidth(value, fallback = 0) {
    const keywords = { thin: 1, medium: 3, thick: 5 };
    const text = String(value || '').trim().toLowerCase();
    if (keywords[text] !== undefined) return keywords[text];

    const parsed = parseFloat(text);
    return Number.isFinite(parsed) ? Math.max(0, Math.round(parsed)) : fallback;
}

function normalizeBorderStyle(value, fallback = 'none') {
    const style = String(value || '').trim().toLowerCase();
    return BORDER_STYLE_OPTIONS.includes(style) ? style : fallback;
}

function parseBorderShorthand(value) {
    const text = String(value || '').trim();
    if (!text || text === 'none') return {};

    const tokens = splitCssWhitespace(text);
    const style = tokens.find(token => BORDER_STYLE_OPTIONS.includes(token.toLowerCase())) || '';
    const width = tokens.find(token => /^(thin|medium|thick|-?[\d.]+(?:px|em|rem|%)?)$/i.test(token)) || '';
    const color = findCssColor(text);

    return {
        width: width ? normalizeBorderWidth(width, 0) : undefined,
        style: style ? normalizeBorderStyle(style, 'solid') : undefined,
        color: color ? hexFromCssColor(color) : undefined,
    };
}

function readComponentBorder(component, edge = activeBorderEdge) {
    const styles = component?.getStyle?.() || {};
    const shorthand = parseBorderShorthand(cssPropertyValue(styles, edge === 'all' ? 'border' : `border-${edge}`));
    const baseShorthand = parseBorderShorthand(cssPropertyValue(styles, 'border'));
    const edgePrefix = edge === 'all' ? 'border' : `border-${edge}`;
    const widthValue = cssPropertyValue(styles, `${edgePrefix}-width`)
        || (edge !== 'all' ? cssQuadValue(cssPropertyValue(styles, 'border-width'), edge) : cssPropertyValue(styles, 'border-width'))
        || shorthand.width
        || baseShorthand.width
        || 0;
    const styleValue = cssPropertyValue(styles, `${edgePrefix}-style`)
        || (edge !== 'all' ? cssQuadValue(cssPropertyValue(styles, 'border-style'), edge) : cssPropertyValue(styles, 'border-style'))
        || shorthand.style
        || baseShorthand.style
        || 'none';
    const colorValue = cssPropertyValue(styles, `${edgePrefix}-color`)
        || (edge !== 'all' ? cssQuadValue(cssPropertyValue(styles, 'border-color'), edge) : cssPropertyValue(styles, 'border-color'))
        || shorthand.color
        || baseShorthand.color
        || '#000000';

    return {
        width: normalizeBorderWidth(widthValue, 0),
        style: normalizeBorderStyle(styleValue, normalizeBorderWidth(widthValue, 0) > 0 ? 'solid' : 'none'),
        color: hexFromCssColor(colorValue),
    };
}

function setBorderWidthControls(value) {
    const controls = borderEditorControls();
    if (!controls) return;
    const width = normalizeBorderWidth(value, 0);
    controls.widthRange.value = Math.min(parseFloat(controls.widthRange.max), width);
    controls.widthNumber.value = width;
}

function readBorderControls() {
    const controls = borderEditorControls();
    if (!controls) return { width: 0, style: 'none', color: '#000000' };
    return {
        width: normalizeBorderWidth(controls.widthNumber.value, 0),
        style: normalizeBorderStyle(controls.style.value, 'none'),
        color: controls.color.value || '#000000',
    };
}

function borderCssValue(border) {
    if (border.style === 'none' || border.width <= 0) return 'none';
    return `${border.width}px ${border.style} ${border.color}`;
}

function updateBorderPreview(border = readBorderControls()) {
    const controls = borderEditorControls();
    if (!controls) return;

    controls.preview.style.border = '1px solid #334155';
    BORDER_EDGE_ORDER.forEach(edge => {
        controls.preview.style[`border${edge.charAt(0).toUpperCase()}${edge.slice(1)}`] = '1px solid #334155';
    });

    const value = borderCssValue(border);
    if (activeBorderEdge === 'all') {
        controls.preview.style.border = value === 'none' ? '1px solid #334155' : value;
    } else {
        controls.preview.style[`border${activeBorderEdge.charAt(0).toUpperCase()}${activeBorderEdge.slice(1)}`] = value === 'none' ? '1px solid #334155' : value;
    }
}

function syncNativeBorderFields(border) {
    const widthInput = document.querySelector('#styles-wrap .gjs-sm-property__border-width input');
    const styleSelect = document.querySelector('#styles-wrap .gjs-sm-property__border-style select');
    const colorInput = document.querySelector('#styles-wrap .gjs-sm-property__border-color input');

    if (activeBorderEdge === 'all' && widthInput && document.activeElement !== widthInput) widthInput.value = border.width || '';
    if (activeBorderEdge === 'all' && styleSelect && document.activeElement !== styleSelect) styleSelect.value = border.style;
    if (activeBorderEdge === 'all' && colorInput && document.activeElement !== colorInput) colorInput.value = border.color;
}

function syncBorderControls(component = editor.getSelected()) {
    const controls = borderEditorControls();
    if (!controls) return;

    setBorderEditorDisabled(!component);
    syncingBorderEditor = true;

    const border = readComponentBorder(component, activeBorderEdge);
    controls.buttons.forEach(button => {
        button.classList.toggle('active', button.dataset.borderEdge === activeBorderEdge);
    });
    controls.targetLabel.textContent = activeBorderEdge === 'all' ? 'All edges' : `${BORDER_EDGE_LABELS[activeBorderEdge]} edge`;
    setBorderWidthControls(border.width);
    controls.style.value = border.style;
    controls.color.value = border.color;
    updateBorderPreview(border);
    syncNativeBorderFields(border);

    syncingBorderEditor = false;
}

function applyBorderFromControls(options = {}) {
    if (syncingBorderEditor) return;
    const component = editor.getSelected();
    const controls = borderEditorControls();
    if (!component || !controls) return;

    if (options.ensureVisible) {
        if (normalizeBorderWidth(controls.widthNumber.value, 0) <= 0) setBorderWidthControls(1);
        if (controls.style.value === 'none') controls.style.value = 'solid';
    }

    const border = readBorderControls();
    const patch = {};

    if (activeBorderEdge === 'all') {
        patch['border-width'] = `${border.width}px`;
        patch['border-style'] = border.style;
        patch['border-color'] = border.color;
        BORDER_EDGE_ORDER.forEach(edge => {
            patch[`border-${edge}-width`] = `${border.width}px`;
            patch[`border-${edge}-style`] = border.style;
            patch[`border-${edge}-color`] = border.color;
        });
    } else {
        patch[`border-${activeBorderEdge}-width`] = `${border.width}px`;
        patch[`border-${activeBorderEdge}-style`] = border.style;
        patch[`border-${activeBorderEdge}-color`] = border.color;
    }

    component.addStyle(patch);
    updateBorderPreview(border);
    syncNativeBorderFields(border);
}

function bindBorderEditorEvents(wrap) {
    wrap.querySelectorAll('[data-border-edge]').forEach(button => {
        button.addEventListener('click', () => {
            activeBorderEdge = button.dataset.borderEdge;
            syncBorderControls();
        });
    });

    ['be-width-range', 'be-width-number'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', event => {
            setBorderWidthControls(event.target.value);
            applyBorderFromControls({ ensureVisible: normalizeBorderWidth(event.target.value, 0) > 0 });
        });
    });

    document.getElementById('be-style')?.addEventListener('change', event => {
        applyBorderFromControls({ ensureVisible: event.target.value !== 'none' });
    });
    document.getElementById('be-color')?.addEventListener('input', () => {
        applyBorderFromControls({ ensureVisible: true });
    });
}

function injectBorderEdgeEditor() {
    if (borderEditorInjected) {
        syncBorderControls();
        return;
    }

    const borderSector = findStyleSectorByTitle('Border & Shadow');
    const props = borderSector?.querySelector('.gjs-sm-properties');
    if (!props) return;

    const wrap = document.createElement('div');
    wrap.id = 'be-wrap';
    wrap.innerHTML = BORDER_EDITOR_HTML;

    const borderProperty = props.querySelector('.gjs-sm-property__border');
    const shadowProperty = props.querySelector('.gjs-sm-property__box-shadow');
    if (borderProperty) borderProperty.insertAdjacentElement('afterend', wrap);
    else if (shadowProperty) shadowProperty.insertAdjacentElement('beforebegin', wrap);
    else props.appendChild(wrap);

    bindBorderEditorEvents(wrap);
    if (!props.dataset.borderEdgeEditorNativeSync) {
        props.dataset.borderEdgeEditorNativeSync = 'true';
        props.addEventListener('input', event => {
            if (event.target.closest?.('.gjs-sm-property__border')) {
                setTimeout(() => syncBorderControls(editor.getSelected()), 0);
            }
        });
        props.addEventListener('change', event => {
            if (event.target.closest?.('.gjs-sm-property__border')) {
                setTimeout(() => syncBorderControls(editor.getSelected()), 0);
            }
        });
    }

    borderEditorInjected = true;
    syncBorderControls();
}

function scheduleBorderEdgeEditorInjection() {
    setTimeout(injectBorderEdgeEditor, 0);
    setTimeout(injectBorderEdgeEditor, 150);
}

editor.on('load', scheduleBorderEdgeEditorInjection);
editor.on('component:selected', scheduleBorderEdgeEditorInjection);
editor.on('component:styleUpdate', syncBorderControls);

const SHADOW_PRESETS = {
    none:   { label: 'None', enabled: false, inset: false, x: 0,  y: 8,  blur: 24, spread: -8, color: '#000000', opacity: .18 },
    soft:   { label: 'Soft', enabled: true,  inset: false, x: 0,  y: 6,  blur: 18, spread: -6, color: '#000000', opacity: .16 },
    card:   { label: 'Card', enabled: true,  inset: false, x: 0,  y: 12, blur: 32, spread: -12, color: '#000000', opacity: .22 },
    lifted: { label: 'Lift', enabled: true,  inset: false, x: 0,  y: 20, blur: 48, spread: -18, color: '#000000', opacity: .28 },
    glow:   { label: 'Glow', enabled: true,  inset: false, x: 0,  y: 0,  blur: 28, spread: 0, color: '#6366f1', opacity: .48 },
    inner:  { label: 'Inner', enabled: true,  inset: true,  x: 0,  y: 2,  blur: 10, spread: 0, color: '#000000', opacity: .20 },
};

const SHADOW_EDITOR_HTML = `
<div class="se-presets">
  ${Object.entries(SHADOW_PRESETS).map(([id, preset]) => `<button type="button" class="se-preset" data-shadow-preset="${id}">${preset.label}</button>`).join('')}
</div>
<div class="se-preview-row">
  <div id="se-preview" aria-hidden="true"></div>
  <div class="se-toggles">
    <label class="se-toggle"><input type="checkbox" id="se-enabled"> Enabled</label>
    <label class="se-toggle"><input type="checkbox" id="se-inset"> Inset</label>
  </div>
</div>
${[
    ['x', 'X', -80, 80],
    ['y', 'Y', -80, 80],
    ['blur', 'Blur', 0, 120],
    ['spread', 'Spread', -80, 80],
].map(([name, label, min, max]) => `
<div class="se-control">
  <div class="se-control-head">
    <span class="se-control-label">${label}</span>
    <input type="range" class="se-range" data-se-name="${name}" min="${min}" max="${max}" step="1">
    <input type="number" class="se-number" data-se-name="${name}" step="1">
  </div>
</div>`).join('')}
<div class="se-color-row">
  <span class="se-color-label">Color</span>
  <input type="color" id="se-color" value="#000000">
  <input type="range" id="se-opacity" min="0" max="1" step="0.01" value="0.2">
  <span id="se-opacity-val">20%</span>
</div>`;

let shadowEditorInjected = false;
let syncingShadowEditor = false;

function shadowEditorControls() {
    const wrap = document.getElementById('se-wrap');
    if (!wrap) return null;

    return {
        wrap,
        enabled: document.getElementById('se-enabled'),
        inset: document.getElementById('se-inset'),
        color: document.getElementById('se-color'),
        opacity: document.getElementById('se-opacity'),
        opacityVal: document.getElementById('se-opacity-val'),
        preview: document.getElementById('se-preview'),
        presets: [...wrap.querySelectorAll('[data-shadow-preset]')],
        inputs: [...wrap.querySelectorAll('input, button')],
    };
}

function setShadowEditorDisabled(disabled) {
    const controls = shadowEditorControls();
    if (!controls) return;
    controls.wrap.classList.toggle('is-disabled', disabled);
    controls.inputs.forEach(input => { input.disabled = disabled; });
}

function shadowNumber(value, fallback = 0) {
    const parsed = parseFloat(value);
    return Number.isFinite(parsed) ? Math.round(parsed) : fallback;
}

function setShadowDimension(name, value) {
    const wrap = document.getElementById('se-wrap');
    if (!wrap) return;

    const rounded = shadowNumber(value);
    wrap.querySelectorAll(`[data-se-name="${name}"]`).forEach(input => {
        if (input.type === 'range') {
            const min = parseFloat(input.min);
            const max = parseFloat(input.max);
            input.value = Math.max(min, Math.min(max, rounded));
        } else {
            input.value = rounded;
        }
    });
}

function readShadowDimension(name, fallback = 0) {
    return shadowNumber(document.querySelector(`#se-wrap .se-number[data-se-name="${name}"]`)?.value, fallback);
}

function shadowRgba(hex, opacity) {
    const raw = String(hex || '#000000').replace('#', '');
    const normalized = raw.length === 3 ? raw.split('').map(char => char + char).join('') : raw.padEnd(6, '0').slice(0, 6);
    const r = parseInt(normalized.slice(0, 2), 16) || 0;
    const g = parseInt(normalized.slice(2, 4), 16) || 0;
    const b = parseInt(normalized.slice(4, 6), 16) || 0;
    const alpha = Math.round(clampOpacity(opacity) * 100) / 100;
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function buildShadowValue(shadow) {
    if (!shadow.enabled) return 'none';

    return [
        shadow.inset ? 'inset' : '',
        `${shadowNumber(shadow.x)}px`,
        `${shadowNumber(shadow.y)}px`,
        `${Math.max(0, shadowNumber(shadow.blur))}px`,
        `${shadowNumber(shadow.spread)}px`,
        shadowRgba(shadow.color, shadow.opacity),
    ].filter(Boolean).join(' ');
}

function parseBoxShadowValue(value) {
    const raw = String(value || '').trim();
    const shadow = { ...SHADOW_PRESETS.none };
    if (!raw || raw === 'none') return shadow;

    const firstShadow = splitCssList(raw)[0] || raw;
    const color = findCssColor(firstShadow);
    const withoutColor = color ? firstShadow.replace(color, ' ') : firstShadow;
    const withoutInset = withoutColor.replace(/\binset\b/ig, ' ');
    const lengths = withoutInset.match(/-?[\d.]+(?:px|em|rem|%)?/g) || [];
    const colorText = color || 'rgba(0,0,0,.2)';

    return {
        enabled: true,
        inset: /\binset\b/i.test(firstShadow),
        x: shadowNumber(lengths[0], 0),
        y: shadowNumber(lengths[1], 8),
        blur: Math.max(0, shadowNumber(lengths[2], 24)),
        spread: shadowNumber(lengths[3], 0),
        color: hexFromCssColor(colorText),
        opacity: String(colorText).trim().toLowerCase() === 'transparent' ? 0 : opacityFromCssColor(colorText),
    };
}

function readShadowControls(forceEnabled = false) {
    const controls = shadowEditorControls();
    if (!controls) return { ...SHADOW_PRESETS.none };

    return {
        enabled: forceEnabled || controls.enabled.checked,
        inset: controls.inset.checked,
        x: readShadowDimension('x', SHADOW_PRESETS.soft.x),
        y: readShadowDimension('y', SHADOW_PRESETS.soft.y),
        blur: readShadowDimension('blur', SHADOW_PRESETS.soft.blur),
        spread: readShadowDimension('spread', SHADOW_PRESETS.soft.spread),
        color: controls.color.value || '#000000',
        opacity: clampOpacity(controls.opacity.value),
    };
}

function matchingShadowPreset(shadow) {
    if (!shadow.enabled) return 'none';

    return Object.entries(SHADOW_PRESETS).find(([id, preset]) => {
        if (id === 'none') return false;
        return preset.enabled === shadow.enabled
            && preset.inset === shadow.inset
            && preset.x === shadow.x
            && preset.y === shadow.y
            && preset.blur === shadow.blur
            && preset.spread === shadow.spread
            && preset.color.toLowerCase() === shadow.color.toLowerCase()
            && Math.abs(preset.opacity - shadow.opacity) < .01;
    })?.[0] || '';
}

function setActiveShadowPreset(id) {
    const controls = shadowEditorControls();
    if (!controls) return;
    controls.presets.forEach(button => {
        button.classList.toggle('active', button.dataset.shadowPreset === id);
    });
}

function updateShadowPreview(shadow) {
    const controls = shadowEditorControls();
    if (!controls) return;

    const shadowValue = buildShadowValue(shadow);
    controls.preview.style.boxShadow = shadowValue;
    controls.opacityVal.textContent = `${Math.round(clampOpacity(shadow.opacity) * 100)}%`;
}

function syncNativeBoxShadowField(value) {
    const field = document.querySelector('#styles-wrap .gjs-sm-property__box-shadow input, #styles-wrap .gjs-sm-property__box-shadow textarea');
    if (field && document.activeElement !== field) field.value = value === 'none' ? '' : value;
}

function applyShadowFromControls(forceEnabled = false) {
    if (syncingShadowEditor) return;
    const component = editor.getSelected();
    if (!component) return;

    const controls = shadowEditorControls();
    if (!controls) return;
    if (forceEnabled) controls.enabled.checked = true;

    const shadow = readShadowControls(forceEnabled);
    const value = buildShadowValue(shadow);
    component.addStyle({ 'box-shadow': value });
    updateShadowPreview(shadow);
    syncNativeBoxShadowField(value);
    setActiveShadowPreset(matchingShadowPreset(shadow));
}

function syncShadowControls(component = editor.getSelected()) {
    const controls = shadowEditorControls();
    if (!controls) return;

    setShadowEditorDisabled(!component);
    syncingShadowEditor = true;

    const styles = component?.getStyle?.() || {};
    const shadow = parseBoxShadowValue(styles['box-shadow'] || styles.boxShadow || '');
    controls.enabled.checked = shadow.enabled;
    controls.inset.checked = shadow.inset;
    setShadowDimension('x', shadow.x);
    setShadowDimension('y', shadow.y);
    setShadowDimension('blur', shadow.blur);
    setShadowDimension('spread', shadow.spread);
    controls.color.value = shadow.color;
    controls.opacity.value = clampOpacity(shadow.opacity);
    updateShadowPreview(shadow);
    syncNativeBoxShadowField(buildShadowValue(shadow));
    setActiveShadowPreset(matchingShadowPreset(shadow));

    syncingShadowEditor = false;
}

function applyShadowPreset(id) {
    const preset = SHADOW_PRESETS[id];
    const controls = shadowEditorControls();
    if (!preset || !controls) return;

    controls.enabled.checked = preset.enabled;
    controls.inset.checked = preset.inset;
    setShadowDimension('x', preset.x);
    setShadowDimension('y', preset.y);
    setShadowDimension('blur', preset.blur);
    setShadowDimension('spread', preset.spread);
    controls.color.value = preset.color;
    controls.opacity.value = preset.opacity;
    updateShadowPreview(preset);
    setActiveShadowPreset(id);
    applyShadowFromControls(false);
}

function bindShadowEditorEvents(wrap) {
    wrap.querySelectorAll('[data-shadow-preset]').forEach(button => {
        button.addEventListener('click', () => applyShadowPreset(button.dataset.shadowPreset));
    });

    wrap.querySelectorAll('[data-se-name]').forEach(input => {
        input.addEventListener('input', () => {
            setActiveShadowPreset('');
            setShadowDimension(input.dataset.seName, input.value);
            applyShadowFromControls(true);
        });
        input.addEventListener('change', () => {
            setShadowDimension(input.dataset.seName, input.value);
            applyShadowFromControls(true);
        });
    });

    ['se-color', 'se-opacity'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', () => {
            setActiveShadowPreset('');
            applyShadowFromControls(true);
        });
    });

    document.getElementById('se-enabled')?.addEventListener('change', () => {
        setActiveShadowPreset('');
        applyShadowFromControls(false);
    });
    document.getElementById('se-inset')?.addEventListener('change', () => {
        setActiveShadowPreset('');
        applyShadowFromControls(true);
    });
}

function injectShadowEditorIntoBorder() {
    if (shadowEditorInjected) {
        syncShadowControls();
        return;
    }

    const borderSector = findStyleSectorByTitle('Border & Shadow');
    const props = borderSector?.querySelector('.gjs-sm-properties');
    if (!props) return;

    const wrap = document.createElement('div');
    wrap.id = 'se-wrap';
    wrap.innerHTML = SHADOW_EDITOR_HTML;

    const shadowProperty = props.querySelector('.gjs-sm-property__box-shadow');
    if (shadowProperty) shadowProperty.insertAdjacentElement('afterend', wrap);
    else props.appendChild(wrap);

    bindShadowEditorEvents(wrap);
    if (!props.dataset.shadowEditorNativeSync) {
        props.dataset.shadowEditorNativeSync = 'true';
        props.addEventListener('input', event => {
            if (event.target.closest?.('.gjs-sm-property__box-shadow')) {
                setTimeout(() => syncShadowControls(editor.getSelected()), 0);
            }
        });
        props.addEventListener('change', event => {
            if (event.target.closest?.('.gjs-sm-property__box-shadow')) {
                setTimeout(() => syncShadowControls(editor.getSelected()), 0);
            }
        });
    }

    shadowEditorInjected = true;
    syncShadowControls();
}

function scheduleShadowEditorInjection() {
    setTimeout(injectShadowEditorIntoBorder, 0);
    setTimeout(injectShadowEditorIntoBorder, 150);
}

editor.on('load', scheduleShadowEditorInjection);
editor.on('component:selected', scheduleShadowEditorInjection);
editor.on('component:styleUpdate', syncShadowControls);

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
