@extends('admin.layout')

@section('title', 'Uploads')
@section('heading', 'Uploads')

@section('content')
    <div class="max-w-6xl space-y-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <form id="upload-form" action="{{ route('admin.uploads.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input id="media-input" name="media[]" type="file" accept="{{ $uploadConfig['accepted_mime_types'] }}" multiple class="sr-only">

                <label id="drop-zone" for="media-input"
                       class="group flex min-h-64 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center transition hover:border-blue-400 hover:bg-blue-50">
                    <span class="mb-4 inline-flex h-14 w-14 items-center justify-center rounded-full bg-white text-blue-700 shadow-sm ring-1 ring-gray-200 transition group-hover:ring-blue-200">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 4v12m0-12l-4 4m4-4l4 4"/>
                        </svg>
                    </span>
                    <span class="text-base font-semibold text-gray-800">Drop images or videos here, or click to choose</span>
                    <span class="mt-2 text-sm text-gray-500">Upload up to {{ $uploadConfig['max_files'] }} {{ $uploadConfig['accepted_extension_label'] }} files. Max {{ $uploadConfig['max_file_label'] }} each.</span>
                    <span class="mt-1 text-xs text-gray-400">Batch limit {{ $uploadConfig['max_post_label'] }}.</span>
                    <span class="mt-1 text-xs text-gray-400">Files are stored in /uploads/content/</span>
                </label>
            </form>

            <div id="upload-status" class="mt-4 hidden rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800"></div>
            <div id="upload-errors" class="mt-4 hidden rounded-lg border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-800"></div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 px-6 py-4">
                <div>
                    <h2 class="text-sm font-semibold text-gray-800">Content Media</h2>
                    <p class="mt-1 text-xs text-gray-500">Use these URLs inside page content, builder images, videos, and custom sections.</p>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-3">
                    <span id="upload-count" class="text-xs font-medium text-gray-400">{{ $uploads->count() }} files</span>
                    <div id="extension-filters" class="{{ $uploads->isEmpty() ? 'hidden' : '' }} flex flex-wrap items-center gap-2 border-r border-gray-200 pr-3">
                        <span class="text-xs font-semibold text-gray-400">Hide</span>
                        <label class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500">
                            <input type="checkbox" value="jpg" class="hide-extension-checkbox rounded border-gray-300 text-blue-700 focus:ring-blue-500">
                            JPG
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500">
                            <input type="checkbox" value="png" class="hide-extension-checkbox rounded border-gray-300 text-blue-700 focus:ring-blue-500">
                            PNG
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500">
                            <input type="checkbox" value="webp" class="hide-extension-checkbox rounded border-gray-300 text-blue-700 focus:ring-blue-500">
                            WebP
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500">
                            <input type="checkbox" value="mp4" class="hide-extension-checkbox rounded border-gray-300 text-blue-700 focus:ring-blue-500">
                            MP4
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500">
                            <input type="checkbox" value="webm" class="hide-extension-checkbox rounded border-gray-300 text-blue-700 focus:ring-blue-500">
                            WebM
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500">
                            <input type="checkbox" value="mov" class="hide-extension-checkbox rounded border-gray-300 text-blue-700 focus:ring-blue-500">
                            MOV
                        </label>
                    </div>
                    <label id="select-all-wrap" class="{{ $uploads->isEmpty() ? 'hidden' : '' }} inline-flex items-center gap-2 text-xs font-medium text-gray-500">
                        <input id="select-all-uploads" type="checkbox" class="rounded border-gray-300 text-blue-700 focus:ring-blue-500">
                        Select all
                    </label>
                    <button id="delete-selected-btn" type="button" disabled
                            class="inline-flex items-center gap-1.5 rounded-md border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7m3 0l1-2h2l1 2"/>
                        </svg>
                        <span id="delete-selected-label">Delete selected</span>
                    </button>
                </div>
            </div>

            <div id="gallery" class="{{ $uploads->isEmpty() ? 'hidden' : '' }} grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($uploads as $upload)
                    <article class="upload-card overflow-hidden rounded-lg border border-gray-200 bg-white" data-filename="{{ $upload['name'] }}" data-extension="{{ $upload['extension'] }}" data-type="{{ $upload['type'] }}">
                        <div class="relative aspect-[4/3] bg-gray-100">
                            <label class="absolute left-2 top-2 inline-flex h-7 w-7 items-center justify-center rounded-md bg-white/90 shadow-sm ring-1 ring-gray-200">
                                <input type="checkbox" value="{{ $upload['name'] }}" class="upload-checkbox rounded border-gray-300 text-blue-700 focus:ring-blue-500">
                            </label>
                            @if($upload['type'] === 'video')
                                <video src="{{ $upload['url'] }}" class="h-full w-full object-cover" muted playsinline preload="metadata"></video>
                                <span class="absolute bottom-2 right-2 rounded bg-black/70 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white">Video</span>
                            @else
                                <img src="{{ $upload['url'] }}" alt="{{ $upload['name'] }}" class="h-full w-full object-cover">
                            @endif
                        </div>
                        <div class="space-y-3 p-3">
                            <div>
                                <h3 class="truncate text-sm font-medium text-gray-800" title="{{ $upload['name'] }}">{{ $upload['name'] }}</h3>
                                <p class="mt-0.5 text-xs text-gray-400">{{ number_format($upload['size'] / 1024, 1) }} KB</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="text" readonly value="{{ $upload['url'] }}"
                                       class="min-w-0 flex-1 rounded-md border border-gray-200 bg-gray-50 px-2 py-1.5 text-xs text-gray-600">
                                <button type="button" data-copy-url="{{ $upload['url'] }}"
                                        class="rounded-md border border-gray-200 px-2.5 py-1.5 text-xs font-medium text-gray-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                                    Copy
                                </button>
                                <button type="button" data-delete-upload="{{ $upload['name'] }}"
                                        class="rounded-md border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-50">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div id="empty-state" class="{{ $uploads->isEmpty() ? '' : 'hidden' }} p-12 text-center">
                <svg class="mx-auto mb-3 h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-8-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm text-gray-500">No content uploads yet.</p>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const form = document.getElementById('upload-form');
        const input = document.getElementById('media-input');
        const dropZone = document.getElementById('drop-zone');
        const statusBox = document.getElementById('upload-status');
        const errorBox = document.getElementById('upload-errors');
        const gallery = document.getElementById('gallery');
        const emptyState = document.getElementById('empty-state');
        const uploadCount = document.getElementById('upload-count');
        const extensionFilters = document.getElementById('extension-filters');
        const selectAllWrap = document.getElementById('select-all-wrap');
        const selectAllUploads = document.getElementById('select-all-uploads');
        const deleteSelectedBtn = document.getElementById('delete-selected-btn');
        const deleteSelectedLabel = document.getElementById('delete-selected-label');
        const uploadConfig = @json($uploadConfig);
        const destroyManyUrl = @json(route('admin.uploads.destroy-many'));
        let fileCount = {{ $uploads->count() }};

        function showMessage(el, message) {
            el.textContent = message;
            el.classList.remove('hidden');
        }

        function hideMessage(el) {
            el.textContent = '';
            el.classList.add('hidden');
        }

        function formatSize(bytes) {
            if (bytes >= 1024 * 1024) {
                return `${(bytes / 1024 / 1024).toFixed(1).replace(/\.0$/, '')} MB`;
            }

            return `${(bytes / 1024).toFixed(1)} KB`;
        }

        function setBusy(isBusy) {
            dropZone.classList.toggle('pointer-events-none', isBusy);
            dropZone.classList.toggle('opacity-60', isBusy);
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function escapeAttr(value) {
            return escapeHtml(value);
        }

        function getHiddenExtensions() {
            return new Set(Array.from(document.querySelectorAll('.hide-extension-checkbox:checked')).map(checkbox => checkbox.value));
        }

        function extensionFromName(filename) {
            const parts = String(filename || '').toLowerCase().split('.');
            return parts.length > 1 ? parts.pop() : '';
        }

        function mediaTypeFromExtension(filename) {
            return ['mp4', 'webm', 'mov', 'm4v', 'ogg', 'ogv'].includes(extensionFromName(filename)) ? 'video' : 'image';
        }

        function isAllowedMediaFile(file) {
            const extension = extensionFromName(file.name);
            const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'mp4', 'webm', 'mov', 'm4v', 'ogg', 'ogv'];

            return file.type.startsWith('image/') || file.type.startsWith('video/') || allowedExtensions.includes(extension);
        }

        function updateGalleryState() {
            const cards = Array.from(gallery.querySelectorAll('.upload-card'));
            const hiddenExtensions = getHiddenExtensions();
            cards.forEach(card => {
                const isHidden = hiddenExtensions.has(card.dataset.extension);
                card.classList.toggle('hidden', isHidden);
                if (isHidden) {
                    const checkbox = card.querySelector('.upload-checkbox');
                    if (checkbox) checkbox.checked = false;
                }
            });

            const visibleCards = cards.filter(card => !card.classList.contains('hidden'));
            const checkboxes = visibleCards.map(card => card.querySelector('.upload-checkbox')).filter(Boolean);
            const checked = checkboxes.filter(checkbox => checkbox.checked);

            fileCount = cards.length;
            const visibleCount = visibleCards.length;
            uploadCount.textContent = hiddenExtensions.size
                ? `${visibleCount} of ${fileCount} file${fileCount === 1 ? '' : 's'}`
                : `${fileCount} file${fileCount === 1 ? '' : 's'}`;
            gallery.classList.toggle('hidden', fileCount === 0);
            emptyState.classList.toggle('hidden', fileCount !== 0);
            extensionFilters.classList.toggle('hidden', fileCount === 0);
            selectAllWrap.classList.toggle('hidden', fileCount === 0);
            deleteSelectedBtn.classList.toggle('hidden', fileCount === 0);
            deleteSelectedBtn.disabled = checked.length === 0;
            deleteSelectedLabel.textContent = checked.length ? `Delete selected (${checked.length})` : 'Delete selected';

            selectAllUploads.disabled = visibleCount === 0;
            selectAllUploads.checked = checkboxes.length > 0 && checked.length === checkboxes.length;
            selectAllUploads.indeterminate = checked.length > 0 && checked.length < checkboxes.length;
        }

        function renderUpload(upload) {
            emptyState.classList.add('hidden');
            gallery.classList.remove('hidden');
            const safeName = escapeHtml(upload.name);
            const safeUrl = escapeAttr(upload.url);
            const safeExtension = escapeAttr(extensionFromName(upload.name));
            const safeType = escapeAttr(upload.type || mediaTypeFromExtension(upload.name));
            const preview = safeType === 'video'
                ? `<video src="${safeUrl}" class="h-full w-full object-cover" muted playsinline preload="metadata"></video><span class="absolute bottom-2 right-2 rounded bg-black/70 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white">Video</span>`
                : `<img src="${safeUrl}" alt="${safeName}" class="h-full w-full object-cover">`;
            gallery.insertAdjacentHTML('afterbegin', `
                <article class="upload-card overflow-hidden rounded-lg border border-gray-200 bg-white" data-filename="${safeName}" data-extension="${safeExtension}" data-type="${safeType}">
                    <div class="relative aspect-[4/3] bg-gray-100">
                        <label class="absolute left-2 top-2 inline-flex h-7 w-7 items-center justify-center rounded-md bg-white/90 shadow-sm ring-1 ring-gray-200">
                            <input type="checkbox" value="${safeName}" class="upload-checkbox rounded border-gray-300 text-blue-700 focus:ring-blue-500">
                        </label>
                        ${preview}
                    </div>
                    <div class="space-y-3 p-3">
                        <div>
                            <h3 class="truncate text-sm font-medium text-gray-800" title="${safeName}">${safeName}</h3>
                            <p class="mt-0.5 text-xs text-gray-400">${formatSize(upload.size)}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="text" readonly value="${safeUrl}" class="min-w-0 flex-1 rounded-md border border-gray-200 bg-gray-50 px-2 py-1.5 text-xs text-gray-600">
                            <button type="button" data-copy-url="${safeUrl}" class="rounded-md border border-gray-200 px-2.5 py-1.5 text-xs font-medium text-gray-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">Copy</button>
                            <button type="button" data-delete-upload="${safeName}" class="rounded-md border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-50">Delete</button>
                        </div>
                    </div>
                </article>
            `);
            updateGalleryState();
        }

        function htmlToMessage(html) {
            const text = String(html || '')
                .replace(/<br\s*\/?>/gi, '\n')
                .replace(/<[^>]+>/g, ' ')
                .replace(/&nbsp;/gi, ' ')
                .replace(/&quot;/gi, '"')
                .replace(/&#039;/gi, "'")
                .replace(/&lt;/gi, '<')
                .replace(/&gt;/gi, '>')
                .replace(/&amp;/gi, '&')
                .replace(/\s+/g, ' ')
                .trim();

            if (text.includes('POST Content-Length') || text.includes('Maximum request length exceeded')) {
                return `This upload is larger than the server batch limit of ${uploadConfig.max_post_label}. Try fewer or smaller files.`;
            }

            if (text.includes('unable to create a temporary file')) {
                return 'The server could not create temporary upload files. The upload temp folder may be missing or not writable.';
            }

            return text || 'Upload failed.';
        }

        async function parseUploadResponse(response) {
            const text = await response.text();
            const contentType = response.headers.get('content-type') || '';

            if (contentType.includes('application/json')) {
                return JSON.parse(text);
            }

            if (text.trim().startsWith('{')) {
                try {
                    return JSON.parse(text);
                } catch (error) {
                    throw new Error('Upload failed because the server returned malformed JSON.');
                }
            }

            throw new Error(htmlToMessage(text));
        }

        function validateFiles(mediaFiles) {
            if (mediaFiles.length > uploadConfig.max_files) {
                return `Upload ${uploadConfig.max_files} files or fewer at a time.`;
            }

            const oversized = mediaFiles.filter(file => file.size > uploadConfig.max_file_bytes);
            if (oversized.length) {
                return `${oversized[0].name} is larger than ${uploadConfig.max_file_label}.`;
            }

            const totalSize = mediaFiles.reduce((sum, file) => sum + file.size, 0);
            if (uploadConfig.max_post_bytes > 0 && totalSize > uploadConfig.max_post_bytes) {
                return `This batch is ${formatSize(totalSize)}, which is larger than the ${uploadConfig.max_post_label} server limit. Try fewer files.`;
            }

            return '';
        }

        async function uploadFiles(files) {
            const mediaFiles = Array.from(files).filter(isAllowedMediaFile);
            if (!mediaFiles.length) {
                showMessage(errorBox, 'Choose one or more image or video files to upload.');
                return;
            }

            const validationError = validateFiles(mediaFiles);
            if (validationError) {
                hideMessage(statusBox);
                showMessage(errorBox, validationError);
                return;
            }

            const data = new FormData();
            mediaFiles.forEach(file => data.append('media[]', file));

            hideMessage(errorBox);
            showMessage(statusBox, `Uploading ${mediaFiles.length} file${mediaFiles.length === 1 ? '' : 's'}...`);
            setBusy(true);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                    },
                    body: data,
                });
                const result = await parseUploadResponse(response);

                if (!response.ok) {
                    const errors = result.errors ? Object.values(result.errors).flat().join(' ') : result.message;
                    throw new Error(errors || 'Upload failed.');
                }

                result.uploads.forEach(renderUpload);
                showMessage(statusBox, `Uploaded ${result.uploads.length} file${result.uploads.length === 1 ? '' : 's'}.`);
            } catch (error) {
                hideMessage(statusBox);
                showMessage(errorBox, error.message || 'Upload failed.');
            } finally {
                input.value = '';
                setBusy(false);
            }
        }

        input.addEventListener('change', () => uploadFiles(input.files));

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, event => {
                event.preventDefault();
                dropZone.classList.add('border-blue-500', 'bg-blue-50');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, event => {
                event.preventDefault();
                dropZone.classList.remove('border-blue-500', 'bg-blue-50');
            });
        });

        dropZone.addEventListener('drop', event => uploadFiles(event.dataTransfer.files));

        async function deleteUploads(filenames) {
            const uniqueNames = Array.from(new Set(filenames.filter(Boolean)));
            if (!uniqueNames.length) return;

            const label = uniqueNames.length === 1 ? 'this file' : `${uniqueNames.length} files`;
            if (!window.confirm(`Delete ${label}?`)) return;

            hideMessage(errorBox);
            showMessage(statusBox, `Deleting ${label}...`);

            try {
                const response = await fetch(destroyManyUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ filenames: uniqueNames }),
                });
                const result = await parseUploadResponse(response);

                if (!response.ok) {
                    const errors = result.errors ? Object.values(result.errors).flat().join(' ') : result.message;
                    throw new Error(errors || 'Delete failed.');
                }

                result.deleted.forEach(filename => {
                    gallery.querySelector(`.upload-card[data-filename="${CSS.escape(filename)}"]`)?.remove();
                });

                updateGalleryState();
                showMessage(statusBox, `Deleted ${result.deleted.length} file${result.deleted.length === 1 ? '' : 's'}.`);
            } catch (error) {
                hideMessage(statusBox);
                showMessage(errorBox, error.message || 'Delete failed.');
            }
        }

        selectAllUploads.addEventListener('change', () => {
            gallery.querySelectorAll('.upload-card:not(.hidden) .upload-checkbox').forEach(checkbox => {
                checkbox.checked = selectAllUploads.checked;
            });
            updateGalleryState();
        });

        deleteSelectedBtn.addEventListener('click', () => {
            const selected = Array.from(gallery.querySelectorAll('.upload-card:not(.hidden) .upload-checkbox:checked')).map(checkbox => checkbox.value);
            deleteUploads(selected);
        });

        document.querySelectorAll('.hide-extension-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateGalleryState);
        });

        gallery.addEventListener('change', event => {
            if (event.target.matches('.upload-checkbox')) updateGalleryState();
        });

        document.addEventListener('click', async event => {
            const deleteButton = event.target.closest('[data-delete-upload]');
            if (deleteButton) {
                deleteUploads([deleteButton.dataset.deleteUpload]);
                return;
            }

            const button = event.target.closest('[data-copy-url]');
            if (!button) return;

            await navigator.clipboard.writeText(button.dataset.copyUrl);
            const original = button.textContent;
            button.textContent = 'Copied';
            setTimeout(() => { button.textContent = original; }, 1200);
        });

        updateGalleryState();
    })();
    </script>
@endsection
