
const API_BASE = (function detectApiBase() {
    if (window.location.protocol === 'file:') {
        return null;
    }
    const basePath = window.location.pathname.replace(/\/[^/]*$/, '') || '';
    return window.location.origin + basePath + '/api/index.php';
})();

async function apiRequest(endpoint, options = {}) {
    if (!API_BASE) {
        return { success: false, error: 'Откройте сайт через PHP-сервер, а не как файл file://' };
    }

    const url = API_BASE + endpoint;
    const config = {
        credentials: 'include',
        headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
        ...options
    };

    if (config.body && typeof config.body === 'object') {
        config.body = JSON.stringify(config.body);
    }

    try {
        const response = await fetch(url, config);
        let data = {};
        try {
            data = await response.json();
        } catch (e) {
            data = {};
        }

        if (!response.ok) {
            let err = data.error || ('Ошибка сервера (' + response.status + ')');
            if (!data.error && typeof data === 'string') {
                err = 'Ошибка сервера (' + response.status + ')';
            }
            return {
                success: false,
                error: err,
                status: response.status
            };
        }
        return data;
    } catch (err) {
        console.warn('API:', endpoint, err.message);
        return { success: false, error: 'Сервер недоступен. Запустите: php -S localhost:8000 router.php' };
    }
}

function isApiAvailable() {
    return !!API_BASE;
}

async function apiRegister(userData) {
    return apiRequest('/users', {
        method: 'POST',
        body: {
            name: userData.name,
            email: userData.email,
            password: userData.password,
            age: userData.age || null,
            role: userData.role || 'Мотоциклист'
        }
    });
}

async function apiLogin(email, password) {
    return apiRequest('/auth/login', {
        method: 'POST',
        body: { email: email.trim(), password }
    });
}

async function apiLogout() {
    return apiRequest('/auth/logout', { method: 'POST' });
}

async function apiCheckout(cartItems) {
    const items = cartItems.map(item => ({
        product_id: item.id,
        quantity: item.qty,
        price: item.price
    }));
    return apiRequest('/cart/checkout', {
        method: 'POST',
        body: { items }
    });
}

async function apiGetMyOrders() {
    return apiRequest('/orders/my', { method: 'GET' });
}

async function apiGetProducts() {
    return apiRequest('/products', { method: 'GET' });
}

async function apiGetForumThreads(category) {
    const q = category && category !== 'Все' ? '?category=' + encodeURIComponent(category) : '';
    return apiRequest('/forum/threads' + q, { method: 'GET' });
}

async function apiCreateThread(data) {
    return apiRequest('/forum/threads', {
        method: 'POST',
        body: {
            category: data.category,
            title: data.title,
            content: data.content
        }
    });
}

async function apiDeleteThread(threadId) {
    return apiRequest('/forum/threads/' + threadId, { method: 'DELETE' });
}

async function loadCatalogFromServer() {
    const result = await apiGetProducts();
    if (result && result.success && Array.isArray(result.data) && result.data.length) {
        setCatalogProducts(result.data.map(normalizeDbProduct));
        syncCartWithCatalog();
        saveToLocalStorage();
        return true;
    }
    return false;
}

async function loadForumFromServer() {
    const result = await apiGetForumThreads();
    if (result && result.success && Array.isArray(result.data)) {
        const serverThreads = result.data.map(normalizeDbThread);
        const localOnly = AppState.threads.filter(t => !t.fromServer);
        AppState.threads = [...serverThreads, ...localOnly];
        saveToLocalStorage();
        return true;
    }
    return false;
}
