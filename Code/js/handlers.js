
function showPage(page) {
    document.querySelectorAll('.ms-page').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.ms-nav-link').forEach(l => l.classList.remove('active'));

    const targetPage = document.getElementById('page-' + page);
    if (targetPage) targetPage.classList.add('active');

    const pageMap = { home: 0, shop: 1, forum: 2, account: 3 };
    if (pageMap[page] !== undefined) {
        const navLinks = document.querySelectorAll('.ms-nav-link');
        if (navLinks[pageMap[page]]) navLinks[pageMap[page]].classList.add('active');
    }

    if (page === 'home') renderHomeProducts();
    if (page === 'shop') {
        renderCategories();
        renderShopProducts();
    }
    if (page === 'forum') renderForum();
    if (page === 'cart') renderCart();
    if (page === 'account') renderAccount();
    if (page === 'auth' && AppState.currentUser) {
        showPage('account');
        return;
    }
}

function addToCart(productId) {
    const product = getCatalogProducts().find(p => p.id === productId);
    if (!product) return;

    const existingItem = AppState.cart.find(item => item.id === productId);
    if (existingItem) {
        existingItem.qty++;
    } else {
        AppState.cart.push({ ...product, qty: 1 });
    }

    updateCartCountDisplay();
    saveToLocalStorage();
    showNotification(`✓ ${product.name} добавлен в корзину`);
}

function changeQuantity(productId, delta) {
    const item = AppState.cart.find(i => i.id === productId);
    if (!item) return;

    item.qty = Math.max(1, item.qty + delta);
    updateCartCountDisplay();
    renderCart();
    saveToLocalStorage();
}

function removeFromCart(productId) {
    AppState.cart = AppState.cart.filter(i => i.id !== productId);
    updateCartCountDisplay();
    renderCart();
    saveToLocalStorage();
}

async function checkout() {
    if (!AppState.currentUser) {
        showNotification('⚠ Войдите в аккаунт для оформления заказа');
        showPage('auth');
        return;
    }

    if (!AppState.cart.length) {
        showNotification('⚠ Корзина пуста');
        return;
    }

    syncCartWithCatalog();
    if (!AppState.cart.length) {
        showNotification('⚠ В корзине нет товаров из каталога. Обновите страницу.');
        renderCart();
        return;
    }

    if (isApiAvailable()) {
        const result = await apiCheckout(AppState.cart);
        if (!result.success) {
            showNotification('⚠ ' + (result.error || 'Не удалось оформить заказ'));
            return;
        }
        showNotification('🏍️ Заказ #' + result.order_id + ' сохранён в базе данных!');
    } else {
        showNotification('🏍️ Заказ оформлен (локально). Для сохранения в БД запустите PHP-сервер.');
    }

    AppState.cart = [];
    updateCartCountDisplay();
    renderCart();
    saveToLocalStorage();
}

function filterCategory(category) {
    AppState.activeCat = category;
    renderCategories();
    renderShopProducts();
}

function filterForum(category) {
    AppState.forumCat = category;
    renderForum();
}

function toggleNewPost() {
    const form = document.getElementById('new-post-form');
    if (form) {
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
    const warning = document.getElementById('post-auth-warning');
    if (warning) {
        warning.style.display = AppState.currentUser ? 'none' : 'block';
    }
}

async function submitPost() {
    if (!AppState.currentUser) {
        showNotification('⚠ Нужно войти в аккаунт');
        return;
    }

    const category = document.getElementById('post-cat').value;
    const title = document.getElementById('post-title').value.trim();
    const text = document.getElementById('post-text').value.trim();

    if (!title || !text) {
        showNotification('⚠ Заполните все поля');
        return;
    }

    if (isApiAvailable()) {
        const result = await apiCreateThread({ category, title, content: text });
        if (result.success && result.data) {
            addThread(normalizeDbThread(result.data));
            document.getElementById('post-title').value = '';
            document.getElementById('post-text').value = '';
            document.getElementById('new-post-form').style.display = 'none';
            saveToLocalStorage();
            renderForum();
            showNotification('✓ Тема опубликована!');
            return;
        }
        if (!result.success) {
            showNotification('⚠ ' + (result.error || 'Не удалось опубликовать'));
            return;
        }
    }

    const newThread = {
        id: Date.now(),
        cat: category,
        title: title,
        author: AppState.currentUser.name,
        author_id: AppState.currentUser.id,
        date: 'только что',
        replies: 0,
        views: 1,
        fromServer: false
    };

    addThread(newThread);

    document.getElementById('post-title').value = '';
    document.getElementById('post-text').value = '';
    document.getElementById('new-post-form').style.display = 'none';

    saveToLocalStorage();
    renderForum();
    showNotification('✓ Тема опубликована!');
}

async function deleteThread(threadId) {
    const thread = AppState.threads.find(t => Number(t.id) === Number(threadId));
    if (!canUserDeleteThread(thread)) {
        showNotification('⚠ Можно удалять только свои темы');
        return;
    }

    if (!confirm('Удалить эту тему?')) {
        return;
    }

    if (thread.fromServer && isApiAvailable()) {
        const result = await apiDeleteThread(threadId);
        if (!result.success) {
            showNotification('⚠ ' + (result.error || 'Не удалось удалить'));
            return;
        }
    }

    removeThread(threadId);
    saveToLocalStorage();
    renderForum();
    showNotification('✓ Тема удалена');
}

function switchAuthTab(tab) {
    const tabs = document.querySelectorAll('.ms-auth-tab');
    tabs.forEach((t, i) => {
        t.classList.toggle('active', (i === 0 && tab === 'login') || (i === 1 && tab === 'register'));
    });

    const loginForm = document.getElementById('auth-login');
    const registerForm = document.getElementById('auth-register');

    if (loginForm) loginForm.style.display = tab === 'login' ? 'block' : 'none';
    if (registerForm) registerForm.style.display = tab === 'register' ? 'block' : 'none';
}

function showAuthError(container, message) {
    if (container) {
        container.innerHTML = '<div class="ms-error-banner">' + message + '</div>';
    }
}

async function doLogin() {
    const email = document.getElementById('login-email').value.trim();
    const password = document.getElementById('login-pass').value;
    const messageContainer = document.getElementById('login-msg');

    if (!email || !password) {
        showAuthError(messageContainer, 'Заполните все поля');
        return;
    }

    if (!validateEmail(email)) {
        showAuthError(messageContainer, 'Некорректный email');
        return;
    }

    if (isApiAvailable()) {
        const result = await apiLogin(email, password);
        if (result.success && result.user) {
            setCurrentUser(result.user);
            if (messageContainer) messageContainer.innerHTML = '';
            saveToLocalStorage();
            showPage('account');
            showNotification('👋 Добро пожаловать, ' + result.user.name + '!');
            return;
        }
        if (result.status === 401) {
            showAuthError(messageContainer, 'Неверный email или пароль');
            return;
        }
        if (!result.success) {
            showAuthError(messageContainer, result.error || 'Ошибка входа');
            return;
        }
    }

    const user = findUserByCredentials(email, password);
    if (!user) {
        showAuthError(messageContainer, 'Неверный email или пароль');
        return;
    }

    setCurrentUser(user);
    if (messageContainer) messageContainer.innerHTML = '';
    saveToLocalStorage();
    showPage('account');
    showNotification('👋 Добро пожаловать, ' + user.name + '!');
}

async function doRegister() {
    const name = document.getElementById('reg-name').value.trim();
    const email = document.getElementById('reg-email').value.trim();
    const age = parseInt(document.getElementById('reg-age').value, 10);
    const password = document.getElementById('reg-pass').value;
    const messageContainer = document.getElementById('reg-msg');

    if (!name || !email || !password) {
        showAuthError(messageContainer, 'Заполните обязательные поля');
        return;
    }

    if (!validateEmail(email)) {
        showAuthError(messageContainer, 'Некорректный email');
        return;
    }

    if (password.length < 6) {
        showAuthError(messageContainer, 'Пароль минимум 6 символов');
        return;
    }

    if (isApiAvailable()) {
        const result = await apiRegister({
            name,
            email,
            password,
            age: isNaN(age) ? null : age,
            role: 'Мотоциклист'
        });

        if (!result.success) {
            const msg = result.error || 'Не удалось зарегистрироваться';
            if (result.status === 409) {
                showAuthError(messageContainer, 'Этот email уже зарегистрирован');
            } else {
                showAuthError(messageContainer, msg);
            }
            return;
        }

        const serverUser = result.data;
        const localUser = {
            id: serverUser.id,
            name: serverUser.name,
            email: serverUser.email,
            age: serverUser.age,
            role: serverUser.role || 'Мотоциклист',
            password: password
        };
        if (!isEmailTaken(email)) {
            addUser(localUser);
        }

        const loginResult = await apiLogin(email, password);
        if (loginResult.success && loginResult.user) {
            setCurrentUser(loginResult.user);
        } else {
            setCurrentUser(serverUser);
        }
        if (messageContainer) messageContainer.innerHTML = '';
        saveToLocalStorage();
        showPage('account');
        showNotification('🏍️ Аккаунт создан и сохранён в базе данных!');
        return;
    }

    if (isEmailTaken(email)) {
        showAuthError(messageContainer, 'Этот email уже зарегистрирован');
        return;
    }

    const newUser = {
        id: Date.now(),
        name: name,
        email: email,
        age: isNaN(age) ? null : age,
        role: 'Мотоциклист',
        password: password
    };

    addUser(newUser);
    setCurrentUser(newUser);
    if (messageContainer) messageContainer.innerHTML = '';
    saveToLocalStorage();
    showPage('account');
    showNotification('🏍️ Аккаунт создан! Добро пожаловать, ' + name + '!');
}

async function doLogout() {
    if (isApiAvailable()) {
        await apiLogout();
    }
    setCurrentUser(null);
    saveToLocalStorage();
    showPage('home');
    showNotification('До встречи на дороге!');
}
