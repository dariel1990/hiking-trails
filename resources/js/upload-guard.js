/**
 * Upload size guard.
 *
 * PHP rejects any request bigger than post_max_size with a PostTooLargeException
 * before the app ever sees it, and a single file over upload_max_filesize is
 * silently dropped. This guard checks the payload in the browser first — for
 * native form posts, programmatic form.submit() calls, and fetch() FormData
 * uploads — and shows a clear message instead of sending a doomed request.
 *
 * Limits come from <meta name="upload-max-post-bytes|upload-max-file-bytes">
 * rendered by partials/upload-limits-meta.blade.php.
 */

const MULTIPART_OVERHEAD_PER_FIELD = 200;

function readLimit(name) {
    const value = Number(document.querySelector(`meta[name="${name}"]`)?.content);

    return Number.isFinite(value) && value > 0 ? value : 0;
}

export function formatBytes(bytes) {
    if (bytes >= 1024 * 1024) {
        return `${(bytes / (1024 * 1024)).toFixed(1).replace(/\.0$/, '')} MB`;
    }
    if (bytes >= 1024) {
        return `${Math.round(bytes / 1024)} KB`;
    }

    return `${bytes} bytes`;
}

const encoder = new TextEncoder();

/**
 * Returns a user-facing problem message when the FormData would exceed the
 * server limits, or null when it's fine to send.
 */
export function uploadProblem(formData) {
    const maxPost = readLimit('upload-max-post-bytes');
    const maxFile = readLimit('upload-max-file-bytes');

    if (!maxPost && !maxFile) {
        return null;
    }

    let total = 0;
    const oversized = [];

    for (const [name, value] of formData.entries()) {
        total += MULTIPART_OVERHEAD_PER_FIELD + encoder.encode(name).length;

        if (value instanceof File) {
            total += value.size;
            if (maxFile && value.size > maxFile) {
                oversized.push(value);
            }
        } else {
            total += encoder.encode(String(value)).length;
        }
    }

    if (oversized.length) {
        const names = oversized.map((file) => `“${file.name}” (${formatBytes(file.size)})`).join(', ');

        return `${oversized.length === 1 ? 'This file is' : 'These files are'} too large: ${names}. Each file must be under ${formatBytes(maxFile)}.`;
    }

    if (maxPost && total > maxPost) {
        return `The upload is too large (${formatBytes(total)}). The total size of the files you send at once must be under ${formatBytes(maxPost)}. Try fewer or smaller files.`;
    }

    return null;
}

let toastTimer = null;

export function showUploadError(message) {
    let toast = document.getElementById('upload-guard-toast');

    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'upload-guard-toast';
        toast.setAttribute('role', 'alert');
        toast.className = 'fixed top-4 left-4 right-4 sm:left-auto sm:max-w-md z-[10000] flex items-start gap-3 rounded-xl border border-red-200 bg-white p-4 shadow-2xl';
        toast.innerHTML = `
            <svg class="h-5 w-5 flex-shrink-0 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
            </svg>
            <div class="flex-1 text-sm">
                <p class="font-semibold text-gray-900">Upload too large</p>
                <p class="mt-1 text-gray-600" data-message></p>
            </div>
            <button type="button" class="text-gray-400 hover:text-gray-600" aria-label="Dismiss" data-dismiss>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>`;
        toast.querySelector('[data-dismiss]').addEventListener('click', () => toast.remove());
        document.body.appendChild(toast);
    }

    toast.querySelector('[data-message]').textContent = message;

    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.remove(), 10000);
}

function formProblem(form, submitter = null) {
    if ((form.getAttribute('method') || 'get').toLowerCase() !== 'post') {
        return null;
    }

    let formData;
    try {
        formData = submitter ? new FormData(form, submitter) : new FormData(form);
    } catch {
        formData = new FormData(form);
    }

    return uploadProblem(formData);
}

function clearOversizedFileInputs(form) {
    const maxFile = readLimit('upload-max-file-bytes');

    form.querySelectorAll('input[type="file"]').forEach((input) => {
        if (maxFile && Array.from(input.files || []).some((file) => file.size > maxFile)) {
            input.value = '';
        }
    });
}

export function installUploadGuard() {
    if (window.__uploadGuardInstalled) {
        return;
    }
    window.__uploadGuardInstalled = true;

    // Capture phase on window runs before any page-level submit handlers (loading
    // spinners, Alpine @submit), so a blocked submit leaves the page untouched.
    window.addEventListener('submit', (event) => {
        const problem = formProblem(event.target, event.submitter);
        if (problem) {
            event.preventDefault();
            event.stopImmediatePropagation();
            showUploadError(problem);
        }
    }, true);

    // form.submit() skips the submit event (e.g. the avatar picker's onchange).
    const nativeSubmit = HTMLFormElement.prototype.submit;
    HTMLFormElement.prototype.submit = function () {
        const problem = formProblem(this);
        if (problem) {
            showUploadError(problem);
            clearOversizedFileInputs(this);

            return;
        }

        return nativeSubmit.call(this);
    };

    if (typeof window.fetch === 'function') {
        const nativeFetch = window.fetch.bind(window);

        window.fetch = function (input, init) {
            if (init?.body instanceof FormData) {
                const problem = uploadProblem(init.body);
                if (problem) {
                    showUploadError(problem);

                    // Resolve with the same shape the server's 413 handler returns,
                    // so existing `data.message` / `data.error` handling shows it.
                    return Promise.resolve(new Response(JSON.stringify({ message: problem, error: problem }), {
                        status: 413,
                        statusText: 'Payload Too Large',
                        headers: { 'Content-Type': 'application/json' },
                    }));
                }
            }

            return nativeFetch(input, init).then((response) => {
                if (response.status === 413) {
                    showUploadError('The upload is too large for the server to accept. Try fewer or smaller files.');
                }

                return response;
            });
        };
    }
}
