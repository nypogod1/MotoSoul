
document.addEventListener('DOMContentLoaded', async function() {
    loadFromLocalStorage();

    if (isApiAvailable()) {
        await loadCatalogFromServer();
        await loadForumFromServer();
    }

    renderHomeProducts();
    updateCartCountDisplay();
    updateNavUserDisplay();

    window.showPage = showPage;
    window.addToCart = addToCart;
    window.changeQuantity = changeQuantity;
    window.removeFromCart = removeFromCart;
    window.checkout = checkout;
    window.filterCategory = filterCategory;
    window.filterForum = filterForum;
    window.toggleNewPost = toggleNewPost;
    window.submitPost = submitPost;
    window.deleteThread = deleteThread;
    window.switchAuthTab = switchAuthTab;
    window.doLogin = doLogin;
    window.doRegister = doRegister;
    window.doLogout = doLogout;
});
