// Base path configuration
const BASE_PATH = '/reverie';

// Cart Drawer functionality
document.addEventListener('DOMContentLoaded', function() {
    const cartIcon = document.getElementById('cart-icon');
    const cartDrawer = document.getElementById('cart-drawer');
    const cartOverlay = document.getElementById('cart-overlay');
    const cartClose = document.getElementById('cart-close');
    
    // Open cart drawer
    if (cartIcon) {
        cartIcon.addEventListener('click', function() {
            openCart();
        });
    }
    
    // Close cart drawer
    if (cartClose) {
        cartClose.addEventListener('click', closeCart);
    }
    
    if (cartOverlay) {
        cartOverlay.addEventListener('click', closeCart);
    }
    
    function openCart() {
        cartDrawer.classList.add('open');
        document.body.style.overflow = 'hidden';
        loadCartItems();
    }
    
    function closeCart() {
        cartDrawer.classList.remove('open');
        document.body.style.overflow = '';
    }
    
    // Load cart items via AJAX
    function loadCartItems() {
        fetch(`${BASE_PATH}/api/cart.php?action=get`)
            .then(res => res.json())
            .then(data => {
                renderCart(data);
            })
            .catch(err => console.error('Cart load error:', err));
    }
    
    // Render cart HTML
    function renderCart(data) {
        const cartBody = document.getElementById('cart-body');
        
        if (!data.items || data.items.length === 0) {
            cartBody.innerHTML = `
                <div class="cart-empty">
                    <svg class="cart-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                        <path d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/>
                    </svg>
                    <p style="font-size: 14px; color: var(--smoke);">Your cart is empty</p>
                    <a href="${BASE_PATH}/shop.php" style="display: inline-block; margin-top: 16px; color: var(--gold); font-size: 12px; letter-spacing: 0.2em; text-transform: uppercase; text-decoration: none; border-bottom: 1px solid var(--gold);">Continue shopping</a>
                </div>
            `;
            return;
        }
        
        let itemsHTML = '';
        data.items.forEach(item => {
            itemsHTML += `
                <div class="cart-item" data-key="${item.cart_key}">
                    <div class="cart-item-image">
                        <img src="${item.image || BASE_PATH + '/assets/images/placeholder.svg'}" alt="${item.name}">
                    </div>
                    <div class="cart-item-info">
                        <h4>${item.name}</h4>
                        ${item.variant ? `<div class="cart-item-variant">${item.variant}</div>` : ''}
                        <div class="cart-item-price">₹${item.price}</div>
                    </div>
                    <div class="cart-item-actions">
                        <button class="cart-item-remove" onclick="removeCartItem('${item.cart_key}')">Remove</button>
                        <div class="cart-item-qty">
                            <button onclick="updateCartQty('${item.cart_key}', ${item.quantity - 1})">−</button>
                            <span>${item.quantity}</span>
                            <button onclick="updateCartQty('${item.cart_key}', ${item.quantity + 1})">+</button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        const shippingNote = data.subtotal >= 999 
            ? 'Free shipping applied'
            : `Add ₹${(999 - data.subtotal).toFixed(0)} for free shipping`;
        
        cartBody.innerHTML = `
            <div style="max-height: calc(100vh - 280px); overflow-y: auto;">
                ${itemsHTML}
            </div>
            <div class="cart-drawer-footer">
                <div class="cart-subtotal">
                    <span>Subtotal</span>
                    <span>₹${data.subtotal}</span>
                </div>
                <div class="cart-shipping-note">${shippingNote}</div>
                <a href="${BASE_PATH}/checkout.php" class="cart-checkout-btn">Proceed to checkout</a>
            </div>
        `;
    }
    
    // Update cart quantity
    window.updateCartQty = function(cartKey, newQty) {
        if (newQty < 1) return;
        
        fetch(`${BASE_PATH}/api/cart.php`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=update&cart_key=${cartKey}&quantity=${newQty}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadCartItems();
                updateCartBadge(data.cart_count);
            }
        })
        .catch(err => console.error('Update cart error:', err));
    };
    
    // Remove cart item
    window.removeCartItem = function(cartKey) {
        if (confirm('Remove this item from cart?')) {
            fetch(`${BASE_PATH}/api/cart.php`, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=remove&cart_key=${cartKey}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadCartItems();
                    updateCartBadge(data.cart_count);
                }
            })
            .catch(err => console.error('Remove cart error:', err));
        }
    };
    
    // Add to cart from product page
    window.addToCart = function(productId, quantity, variant) {
        const data = {
            product_id: productId,
            quantity: quantity || 1
        };
        
        if (variant) {
            data.variant_id = variant;
        }
        
        fetch(`${BASE_PATH}/api/cart.php?action=add`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                updateCartBadge(data.cart_count);
                openCart();
            } else {
                alert(data.message || 'Could not add to cart');
            }
        })
        .catch(err => {
            console.error('Add to cart error:', err);
            alert('Error adding to cart. Please try again.');
        });
    };
    
    // Update cart badge
    function updateCartBadge(count) {
        let badge = document.querySelector('.cart-badge');
        if (count > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'cart-badge';
                cartIcon.parentElement.appendChild(badge);
            }
            badge.textContent = count;
        } else if (badge) {
            badge.remove();
        }
    }
    
    // Product image hover effect
    const productCards = document.querySelectorAll('.product');
    productCards.forEach(card => {
        const mainImg = card.querySelector('.product-image img');
        if (mainImg && mainImg.dataset.hover) {
            const originalSrc = mainImg.src;
            const hoverSrc = mainImg.dataset.hover;
            
            card.addEventListener('mouseenter', () => {
                mainImg.src = hoverSrc;
            });
            
            card.addEventListener('mouseleave', () => {
                mainImg.src = originalSrc;
            });
        }
    });
    
    // Wishlist toggle (visual only - not persistent)
    const wishlistBtns = document.querySelectorAll('.wishlist');
    wishlistBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.toggle('active');
            // TODO: Add backend API call to save wishlist
        });
    });
    
    // Filter interaction
    const filters = document.querySelectorAll('.filter');
    filters.forEach(f => {
        f.addEventListener('click', function() {
            filters.forEach(x => x.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Fade-in observer for elements
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.15 });

    document.querySelectorAll('.category, .product, .testimonial, .promise').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
        observer.observe(el);
    });
});
