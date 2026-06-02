
function renderHomeProducts() {
    const hotProducts = getCatalogProducts().filter(p => p.hot);
    const container = document.getElementById('home-products');
    if (container) {
        container.innerHTML = hotProducts.map(productCardTemplate).join('');
    }
}

function productCardTemplate(product) {
    return `<div class="ms-product">
        <div class="ms-product-img">
            <img src="${product.image}" alt="${product.name}" onerror="this.onerror=null;this.src='assets/products/placeholder.svg'">
        </div>
        <div class="ms-product-body">
            <div class="ms-product-brand">${product.brand}</div>
            <div class="ms-product-name">${product.name}</div>
            <div class="ms-product-tags">${product.tags.map(t => `<span class="ms-product-tag">${t}</span>`).join('')}</div>
            <div class="ms-product-footer">
                <div class="ms-product-price">${product.price.toLocaleString('ru-RU')} <span>₽</span></div>
                <button class="ms-add-btn" onclick="window.addToCart(${product.id})">В КОРЗИНУ</button>
            </div>
        </div>
    </div>`;
}

function renderCategories() {
    const categories = ['Все', 'Шлемы', 'Куртки', 'Перчатки', 'Штаны', 'Обувь'];
    const categoryImages = {
        'Все': 'assets/icons/3.png',
        'Шлемы': 'assets/icons/2.png',
        'Куртки': 'assets/icons/1.png',
        'Перчатки': 'assets/icons/6.png',
        'Штаны': 'assets/icons/5.png',
        'Обувь': 'assets/icons/4.png'
    };
    
    const container = document.getElementById('cat-filter');
    if (container) {
        container.innerHTML = categories.map(cat =>
            `<div class="ms-cat ${AppState.activeCat === cat ? 'active' : ''}" onclick="window.filterCategory('${cat}')">
                <div class="ms-cat-icon">
                    <img src="${categoryImages[cat]}" alt="${cat}">
                </div>
                <div class="ms-cat-name">${cat}</div>
            </div>`
        ).join('');
    }
}

function renderShopProducts() {
    const catalog = getCatalogProducts();
    const filtered = AppState.activeCat === 'Все'
        ? catalog
        : catalog.filter(p => p.category === AppState.activeCat);
    const container = document.getElementById('shop-products');
    if (container) {
        container.innerHTML = filtered.map(productCardTemplate).join('');
    }
}

function renderForum() {
    const forumCategories = ['Все', 'Маршруты', 'Техника', 'Экипировка', 'Мероприятия', 'Разное'];
    const catsContainer = document.getElementById('forum-cats');
    if (catsContainer) {
        catsContainer.innerHTML = forumCategories.map(cat =>
            `<button class="ms-forum-cat-btn ${AppState.forumCat === cat ? 'active' : ''}" onclick="window.filterForum('${cat}')">${cat}</button>`
        ).join('');
    }
    
    const threads = AppState.forumCat === 'Все' 
        ? AppState.threads 
        : AppState.threads.filter(t => t.cat === AppState.forumCat);
    const threadsContainer = document.getElementById('forum-threads');
    if (threadsContainer) {
        threadsContainer.innerHTML = threads.length
            ? threads.map(thread => {
                const canDelete = canUserDeleteThread(thread);
                const deleteBtn = canDelete
                    ? `<button type="button" class="ms-thread-delete" onclick="event.stopPropagation(); window.deleteThread(${thread.id})" title="Удалить вашу тему">
                        <span class="ms-thread-delete-icon" aria-hidden="true">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </span>
                        <span>Удалить</span>
                       </button>`
                    : '';
                return `
                <div class="ms-thread">
                    <div class="ms-thread-main">
                        <div class="ms-thread-cat">${thread.cat}</div>
                        <div class="ms-thread-title">${thread.title}</div>
                        <div class="ms-thread-meta">Автор: <strong>${thread.author}</strong> · ${thread.date}</div>
                    </div>
                    <div class="ms-thread-side">
                        <div class="ms-thread-stats">
                            <div class="ms-thread-stat"><strong>${thread.replies}</strong> ответов</div>
                            <div class="ms-thread-stat"><strong>${thread.views}</strong> просмотров</div>
                        </div>
                        ${deleteBtn}
                    </div>
                </div>`;
            }).join('')
            : `<div class="ms-empty"><div class="ms-empty-icon">💬</div><p>В этой категории пока нет тем</p></div>`;
    }
}

function renderCart() {
    const container = document.getElementById('cart-items');
    if (!container) return;
    
    if (!AppState.cart.length) {
        container.innerHTML = `<div class="ms-empty"><div class="ms-empty-icon">🛒</div><p>Корзина пуста. Добавьте товары из каталога.</p></div>`;
        return;
    }
    
    const subtotal = AppState.cart.reduce((sum, item) => sum + item.price * item.qty, 0);
    const delivery = subtotal > 20000 ? 0 : 490;
    
    container.innerHTML = AppState.cart.map(item => `
        <div class="ms-cart-item">
            <div class="ms-cart-item-icon">
                <img src="${item.image}" alt="${item.name}" onerror="this.onerror=null;this.src='assets/products/placeholder.svg'">
            </div>
            <div class="ms-cart-item-info">
                <div class="ms-cart-item-brand">${item.brand}</div>
                <div class="ms-cart-item-name">${item.name}</div>
                <div class="ms-cart-item-size">Категория: ${item.category}</div>
            </div>
            <div class="ms-qty-control">
                <button class="ms-qty-btn" onclick="window.changeQuantity(${item.id}, -1)">−</button>
                <span class="ms-qty-num">${item.qty}</span>
                <button class="ms-qty-btn" onclick="window.changeQuantity(${item.id}, 1)">+</button>
            </div>
            <div class="ms-cart-item-price">${(item.price * item.qty).toLocaleString('ru-RU')} ₽</div>
            <button class="ms-remove-btn" onclick="window.removeFromCart(${item.id})">✕</button>
        </div>
    `).join('') + `
        <div class="ms-cart-summary">
            <div class="ms-cart-summary-row"><span>Товары (${AppState.cart.reduce((s, i) => s + i.qty, 0)} шт.)</span><span>${subtotal.toLocaleString('ru-RU')} ₽</span></div>
            <div class="ms-cart-summary-row"><span>Доставка</span><span>${delivery === 0 ? '<span style="color:#97c459">Бесплатно</span>' : delivery.toLocaleString('ru-RU') + ' ₽'}</span></div>
            <div class="ms-cart-summary-total"><span>Итого</span><span>${(subtotal + delivery).toLocaleString('ru-RU')} ₽</span></div>
            <button class="ms-checkout-btn" onclick="window.checkout()">Оформить заказ</button>
        </div>`;
}

function renderAccount() {
    const container = document.getElementById('account-content');
    if (!container) return;
    
    if (!AppState.currentUser) {
        container.innerHTML = `<div class="ms-empty"><div class="ms-empty-icon">🔐</div><p>Войдите в аккаунт или зарегистрируйтесь</p><br><button class="ms-btn-primary" onclick="window.showPage('auth')">Войти</button></div>`;
        return;
    }
    
    const user = AppState.currentUser;
    container.innerHTML = `
        <div class="ms-account-info" style="margin-bottom:24px">
            <div class="ms-account-avatar">${user.name.slice(0, 2).toUpperCase()}</div>
            <div>
                <div class="ms-account-name">${user.name}</div>
                <div class="ms-account-role">${user.role || 'Мотоциклист'}</div>
            </div>
        </div>
        <div class="ms-account-grid">
            <div class="ms-account-card">
                <div class="ms-account-card-title">Профиль</div>
                <div class="ms-account-row"><span>Имя</span><span>${user.name}</span></div>
                <div class="ms-account-row"><span>Email</span><span>${user.email}</span></div>
                <div class="ms-account-row"><span>Возраст</span><span>${user.age || '—'}</span></div>
                <div class="ms-account-row"><span>ID пользователя</span><span>#${user.id}</span></div>
            </div>
            <div class="ms-account-card">
                <div class="ms-account-card-title">Активность</div>
                <div class="ms-account-row"><span>Постов на форуме</span><span>${AppState.threads.filter(t => t.author === user.name).length}</span></div>
                <div class="ms-account-row"><span>Статус</span><span style="color:#97c459">Активен</span></div>
            </div>
        </div>
        <div class="ms-account-card" style="margin-top:16px">
            <div class="ms-account-card-title">Мои заказы</div>
            <div id="account-orders"><p style="color:var(--ms-gray);font-size:13px">Загрузка...</p></div>
        </div>`;

    loadAccountOrders();
}

async function loadAccountOrders() {
    const container = document.getElementById('account-orders');
    if (!container || !AppState.currentUser) return;

    if (!isApiAvailable()) {
        container.innerHTML = '<p style="color:var(--ms-gray);font-size:13px">Заказы в БД доступны при запуске через PHP-сервер.</p>';
        return;
    }

    const result = await apiGetMyOrders();

    if (!result.success) {
        container.innerHTML = '<p style="color:#e85d4a;font-size:13px">' + (result.error || 'Не удалось загрузить заказы') + '. Войдите снова.</p>';
        return;
    }

    const orders = result.data || [];

    if (!orders.length) {
        container.innerHTML = '<p style="color:var(--ms-gray);font-size:13px">У вас пока нет заказов. Добавьте товары в корзину и оформите покупку.</p>';
        return;
    }

    container.innerHTML = orders.map(order => {
        const itemsHtml = (order.items || []).map(item =>
            `<div class="ms-account-row"><span>${item.name || 'Товар #' + item.product_id} × ${item.quantity}</span><span>${(item.price_at_time * item.quantity).toLocaleString('ru-RU')} ₽</span></div>`
        ).join('');
        const date = order.created_at ? new Date(order.created_at).toLocaleString('ru-RU') : '—';
        const status = order.status || 'pending';
        return `<div style="border-top:1px solid var(--ms-border);padding-top:12px;margin-top:12px">
            <div class="ms-account-row"><span><strong>Заказ #${order.id}</strong></span><span style="color:#97c459">${status}</span></div>
            <div class="ms-account-row"><span>Дата</span><span>${date}</span></div>
            <div class="ms-account-row"><span>Сумма</span><span><strong>${Number(order.total_amount).toLocaleString('ru-RU')} ₽</strong></span></div>
            ${itemsHtml}
        </div>`;
    }).join('');
}

function updateCartCountDisplay() {
    const total = AppState.cart.reduce((sum, item) => sum + item.qty, 0);
    const counter = document.getElementById('cart-count');
    if (counter) counter.textContent = total;
}

function updateNavUserDisplay() {
    const label = document.getElementById('nav-user-label');
    if (label) {
        label.textContent = AppState.currentUser ? AppState.currentUser.name : 'Войти';
    }
}

function showNotification(message) {
    const notif = document.getElementById('ms-notif');
    if (!notif) return;
    
    notif.textContent = message;
    notif.classList.add('show');
    
    if (AppState.notifTimer) clearTimeout(AppState.notifTimer);
    AppState.notifTimer = setTimeout(() => notif.classList.remove('show'), 3000);
}