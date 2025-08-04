(function($) {
    'use strict';

    $(document).ready(function() {
        initMobileNav();
    });

    function initMobileNav() {
        const $container = $('#wp-mnb-container');
        if (!$container.length) return;

        // Initialize cart count updates
        initCartUpdates();
        
        // Handle menu item clicks
        handleMenuClicks();
        
        // Initialize animations
        initAnimations();
        
        // Handle scroll behavior
        initScrollBehavior();
        
        // Handle touch gestures
        initTouchGestures();
    }

    function initCartUpdates() {
        if (typeof wc_add_to_cart_params === 'undefined') return;

        // Listen for cart updates
        $(document.body).on('added_to_cart removed_from_cart', function() {
            updateCartCount();
        });

        // Listen for WooCommerce fragments update
        $(document.body).on('wc_fragments_refreshed wc_fragments_loaded', function() {
            updateCartCount();
        });
    }

    function updateCartCount() {
        $.ajax({
            url: wp_mnb_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_mnb_get_cart_count',
                nonce: wp_mnb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    const $cartCount = $('#wp-mnb-cart-count');
                    $cartCount.text(response.data.count);
                    
                    // Animate cart count update
                    $cartCount.addClass('wp-mnb-bounce');
                    setTimeout(() => $cartCount.removeClass('wp-mnb-bounce'), 300);
                }
            }
        });
    }

    function handleMenuClicks() {
        $('.wp-mnb-item').on('click', function(e) {
            const $item = $(this);
            
            // Remove active class from all items
            $('.wp-mnb-item').removeClass('active');
            
            // Add active class to clicked item
            $item.addClass('active');
            
            // Handle special item types
            if ($item.data('cart-item')) {
                // Cart item - could show mini cart or navigate to cart
                handleCartClick(e, $item);
            }
            
            // Track click analytics if needed
            trackMenuClick($item.data('item-index'));
        });
    }

    function handleCartClick(e, $item) {
        // Example: Show mini cart overlay instead of navigating
        // This could be a pro feature
        if (window.wpMnbSettings && window.wpMnbSettings.showMiniCart) {
            e.preventDefault();
            showMiniCart();
        }
    }

    function showMiniCart() {
        // Pro feature: Show mini cart overlay
        console.log('Mini cart would open here (Pro feature)');
    }

    function initAnimations() {
        const $container = $('#wp-mnb-container');
        const animation = $container.data('animation');
        
        if (animation && animation !== 'none') {
            $container.addClass('wp-mnb-animated');
        }
    }

    function initScrollBehavior() {
        let lastScrollTop = 0;
        const $container = $('#wp-mnb-container');
        
        $(window).scroll(function() {
            const scrollTop = $(this).scrollTop();
            
            // Hide/show on scroll (optional feature)
            if (window.wpMnbSettings && window.wpMnbSettings.hideOnScroll) {
                if (scrollTop > lastScrollTop && scrollTop > 100) {
                    // Scrolling down
                    $container.addClass('wp-mnb-hidden');
                } else {
                    // Scrolling up
                    $container.removeClass('wp-mnb-hidden');
                }
            }
            
            lastScrollTop = scrollTop;
        });
    }

    function initTouchGestures() {
        const $container = $('#wp-mnb-container');
        let startY = 0;
        let startTime = 0;
        
        $container.on('touchstart', function(e) {
            startY = e.originalEvent.touches[0].clientY;
            startTime = Date.now();
        });
        
        $container.on('touchend', function(e) {
            const endY = e.originalEvent.changedTouches[0].clientY;
            const endTime = Date.now();
            const deltaY = startY - endY;
            const deltaTime = endTime - startTime;
            
            // Swipe down to hide menu (if enabled)
            if (deltaY < -50 && deltaTime < 300) {
                if (window.wpMnbSettings && window.wpMnbSettings.swipeToHide) {
                    $container.addClass('wp-mnb-hidden');
                    setTimeout(() => $container.removeClass('wp-mnb-hidden'), 3000);
                }
            }
        });
    }

    function trackMenuClick(itemIndex) {
        // Analytics tracking
        if (typeof gtag !== 'undefined') {
            gtag('event', 'mobile_menu_click', {
                'item_index': itemIndex,
                'event_category': 'Mobile Navigation'
            });
        }
    }

    // Submenu handling (Pro feature)
    function initSubmenuHandling() {
        $('.wp-mnb-item').on('click', function(e) {
            const $submenu = $(this).find('.wp-mnb-submenu');
            if ($submenu.length) {
                e.preventDefault();
                toggleSubmenu($submenu);
            }
        });
    }

    function toggleSubmenu($submenu) {
        // Pro feature implementation
        $('.wp-mnb-submenu').not($submenu).removeClass('active');
        $submenu.toggleClass('active');
    }

    // Initialize pro features if available
    if (window.wpMnbProActive) {
        initSubmenuHandling();
    }

    // CSS animation classes
    const style = document.createElement('style');
    style.textContent = `
        .wp-mnb-bounce {
            animation: wpMnbBounce 0.3s ease-out;
        }
        
        @keyframes wpMnbBounce {
            0% { transform: scale(1); }
            50% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }
        
        .wp-mnb-submenu.active {
            display: block;
            animation: wpMnbSlideUp 0.3s ease-out;
        }
        
        @keyframes wpMnbSlideUp {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }
    `;
    document.head.appendChild(style);

})(jQuery);
