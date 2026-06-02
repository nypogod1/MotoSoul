const STORAGE_KEY = 'motosoul_app_v1';

const AppState = {
    users: [],
    currentUser: null,
    cart: [],
    threads: [],
    activeCat: 'Все',
    forumCat: 'Все',
    notifTimer: null
};

let catalogProducts = null;

function getCatalogProducts() {
    return catalogProducts && catalogProducts.length ? catalogProducts : PRODUCTS;
}

function setCatalogProducts(list) {
    catalogProducts = list;
}

function syncCartWithCatalog() {
    const catalog = getCatalogProducts();
    const byId = new Map(catalog.map(p => [p.id, p]));
    const byName = new Map(catalog.map(p => [p.name.toLowerCase(), p]));

    AppState.cart = AppState.cart.map(item => {
        if (byId.has(item.id)) {
            return { ...byId.get(item.id), qty: item.qty };
        }
        const match = byName.get(item.name.toLowerCase());
        if (match) {
            return { ...match, qty: item.qty };
        }
        return item;
    }).filter(item => byId.has(item.id));
}

function loadFromLocalStorage() {
    AppState.users = DEFAULT_USERS.map(u => ({ ...u }));
    AppState.threads = DEFAULT_THREADS.map(t => ({ ...t }));

    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) {
            updateCartCountDisplay();
            updateNavUserDisplay();
            return;
        }

        const saved = JSON.parse(raw);

        if (Array.isArray(saved.users) && saved.users.length) {
            AppState.users = saved.users;
        }
        if (Array.isArray(saved.threads) && saved.threads.length) {
            AppState.threads = saved.threads;
        }
        if (Array.isArray(saved.cart)) {
            AppState.cart = saved.cart;
        }
        if (saved.currentUser) {
            const fresh = AppState.users.find(
                u => u.id === saved.currentUser.id || u.email === saved.currentUser.email
            );
            AppState.currentUser = fresh ? sanitizeUser(fresh) : null;
        }
    } catch (e) {
        console.warn('Не удалось загрузить данные из localStorage', e);
    }

    updateCartCountDisplay();
    updateNavUserDisplay();
}

function saveToLocalStorage() {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify({
            users: AppState.users,
            currentUser: AppState.currentUser,
            cart: AppState.cart,
            threads: AppState.threads
        }));
    } catch (e) {
        console.warn('Не удалось сохранить в localStorage', e);
    }
}

function sanitizeUser(user) {
    const { password, ...safe } = user;
    return safe;
}

function setCurrentUser(user) {
    AppState.currentUser = user ? sanitizeUser(user) : null;
    updateNavUserDisplay();
}

function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function findUserByCredentials(email, password) {
    const normalizedEmail = email.trim().toLowerCase();
    const user = AppState.users.find(
        u => u.email.trim().toLowerCase() === normalizedEmail && u.password === password
    );
    return user ? sanitizeUser(user) : null;
}

function isEmailTaken(email) {
    const normalizedEmail = email.trim().toLowerCase();
    return AppState.users.some(u => u.email.trim().toLowerCase() === normalizedEmail);
}

function addUser(user) {
    AppState.users.push(user);
}

function addThread(thread) {
    AppState.threads.unshift(thread);
}

function removeThread(threadId) {
    AppState.threads = AppState.threads.filter(t => Number(t.id) !== Number(threadId));
}

function canUserDeleteThread(thread) {
    if (!AppState.currentUser || !thread) return false;
    if (thread.author_id != null && AppState.currentUser.id != null) {
        return Number(thread.author_id) === Number(AppState.currentUser.id);
    }
    return thread.author === AppState.currentUser.name;
}

function normalizeDbProduct(p) {
    let tags = p.tags;
    if (typeof tags === 'string') {
        tags = tags.replace(/^\{|\}$/g, '').split(',').map(t => t.trim().replace(/^"|"$/g, '')).filter(Boolean);
    }
    if (!Array.isArray(tags)) tags = [];

    return {
        id: parseInt(p.id, 10),
        brand: p.brand,
        name: p.name,
        price: parseInt(p.price, 10),
        category: p.category,
        tags,
        image: p.image || 'assets/products/placeholder.svg',
        hot: p.hot === true || p.hot === 't' || p.hot === 1 || p.hot === '1'
    };
}

function normalizeDbThread(t) {
    const created = t.created_at ? new Date(t.created_at) : null;
    let dateLabel = 'недавно';
    if (created && !isNaN(created.getTime())) {
        dateLabel = created.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', year: 'numeric' });
    }
    return {
        id: parseInt(t.id, 10),
        cat: t.category,
        title: t.title,
        author: t.author_name,
        author_id: t.author_id != null ? parseInt(t.author_id, 10) : null,
        date: dateLabel,
        replies: parseInt(t.replies, 10) || 0,
        views: parseInt(t.views, 10) || 0,
        fromServer: true
    };
}
