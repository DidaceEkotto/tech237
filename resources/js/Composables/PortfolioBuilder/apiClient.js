function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

async function request(method, url, body = null) {
    const isFormData = typeof FormData !== 'undefined' && body instanceof FormData;
    const headers = {
        Accept: 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
        'X-Requested-With': 'XMLHttpRequest',
    };
    if (!isFormData && body !== null) {
        headers['Content-Type'] = 'application/json';
    }

    const res = await fetch(url, {
        method,
        headers,
        credentials: 'same-origin',
        body: body === null ? undefined : (isFormData ? body : JSON.stringify(body)),
    });

    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
        const err = new Error(data.message || 'Erreur serveur');
        err.response = { status: res.status, data };
        throw err;
    }
    return { data };
}

export default {
    get: (url) => request('GET', url),
    post: (url, body = {}) => request('POST', url, body),
    patch: (url, body = {}) => request('PATCH', url, body),
    delete: (url) => request('DELETE', url),
};
