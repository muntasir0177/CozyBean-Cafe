document.addEventListener('DOMContentLoaded', function() {
    initializeCart();
    setupAddToCartButtons();
});

function initializeCart() {
    if (!localStorage.getItem('cart')) {
        localStorage.setItem('cart', JSON.stringify([]));
    }
    updateCartCount();
}

function setupAddToCartButtons() {
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            addToCart(productId);
        });
    });
}

function addToCart(productId) {
    let cart = JSON.parse(localStorage.getItem('cart'));
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({ id: productId, quantity: 1 });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    showAlert('Item added to cart!', 'success');
}

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    document.getElementById('cart-count').textContent = totalItems;
}

function showAlert(message, type = 'success') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.role = 'alert';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container') || document.body;
    container.prepend(alert);
    
    setTimeout(() => {
        alert.classList.remove('show');
        setTimeout(() => alert.remove(), 150);
    }, 3000);
}