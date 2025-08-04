<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_MNB_WooCommerce {
    
    public function __construct() {
        if (!class_exists('WooCommerce')) {
            return;
        }
        
        add_action('wp_enqueue_scripts', array($this, 'enqueue_woocommerce_scripts'));
        add_action('woocommerce_add_to_cart', array($this, 'update_cart_count'));
        add_action('woocommerce_cart_item_removed', array($this, 'update_cart_count'));
        add_action('wp_ajax_wp_mnb_get_cart_count', array($this, 'get_cart_count_ajax'));
        add_action('wp_ajax_nopriv_wp_mnb_get_cart_count', array($this, 'get_cart_count_ajax'));
        
        // Cart fragments for AJAX cart updates
        add_filter('woocommerce_add_to_cart_fragments', array($this, 'cart_count_fragment'));
    }
    
    public function enqueue_woocommerce_scripts() {
        if (!wp_is_mobile()) {
            return;
        }
        
        wp_enqueue_script('wp-mnb-woocommerce', WP_MNB_PLUGIN_URL . 'assets/js/woocommerce.js', array('jquery'), WP_MNB_VERSION, true);
        
        wp_localize_script('wp-mnb-woocommerce', 'wp_mnb_wc', array(
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'cart_total' => WC()->cart->get_cart_total(),
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_mnb_wc_nonce'),
        ));
    }
    
    public function update_cart_count() {
        // This will be handled by WooCommerce fragments
    }
    
    public function get_cart_count_ajax() {
        check_ajax_referer('wp_mnb_wc_nonce', 'nonce');
        
        $cart_count = WC()->cart->get_cart_contents_count();
        $cart_total = WC()->cart->get_cart_total();
        
        wp_send_json_success(array(
            'count' => $cart_count,
            'total' => $cart_total,
        ));
    }
    
    public function cart_count_fragment($fragments) {
        $cart_count = WC()->cart->get_cart_contents_count();
        
        ob_start();
        ?>
        <span class="wp-mnb-cart-count" id="wp-mnb-cart-count"><?php echo $cart_count; ?></span>
        <?php
        $fragments['#wp-mnb-cart-count'] = ob_get_clean();
        
        return $fragments;
    }
}
