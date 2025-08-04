<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_MNB_Menu_Renderer {
    
    private $settings;
    
    public function __construct() {
        $this->settings = get_option('wp_mnb_settings', array());
    }
    
    public function render() {
        if (!$this->should_render()) {
            return;
        }
        
        $menu_items = $this->get_menu_items();
        $style_class = 'wp-mnb-style-' . (isset($this->settings['style_preset']) ? $this->settings['style_preset'] : 1);
        
        ?>
        <div id="wp-mnb-container" class="wp-mnb-container <?php echo esc_attr($style_class); ?>" data-animation="<?php echo esc_attr($this->get_animation()); ?>">
            <nav class="wp-mnb-nav">
                <?php foreach ($menu_items as $index => $item): ?>
                    <?php if ($item['enabled']): ?>
                    <a href="<?php echo esc_url($this->get_item_url($item)); ?>" 
                       class="wp-mnb-item <?php echo $this->is_current_item($item) ? 'active' : ''; ?>" 
                       data-item-index="<?php echo $index; ?>"
                       <?php echo $this->get_item_attributes($item); ?>>
                        
                        <div class="wp-mnb-icon-wrapper">
                            <?php if ($this->is_woocommerce_cart($item)): ?>
                                <span class="wp-mnb-cart-count" id="wp-mnb-cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
                            <?php endif; ?>
                            
                            <?php if ($this->is_custom_icon($item['icon'])): ?>
                                <img src="<?php echo esc_url($item['icon']); ?>" alt="<?php echo esc_attr($item['label']); ?>" class="wp-mnb-custom-icon">
                            <?php else: ?>
                                <i class="<?php echo esc_attr($item['icon']); ?> wp-mnb-icon"></i>
                            <?php endif; ?>
                        </div>
                        
                        <span class="wp-mnb-label"><?php echo esc_html($item['label']); ?></span>
                        
                        <?php if ($this->has_submenu($item)): ?>
                            <div class="wp-mnb-submenu">
                                <?php $this->render_submenu($item); ?>
                            </div>
                        <?php endif; ?>
                    </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        </div>
        
        <?php if (!empty($this->settings['custom_css'])): ?>
        <style>
            <?php echo wp_strip_all_tags($this->settings['custom_css']); ?>
        </style>
        <?php endif; ?>
        <?php
    }
    
    private function should_render() {
        $enabled = isset($this->settings['enabled']) ? $this->settings['enabled'] : true;
        $mobile_only = isset($this->settings['mobile_only']) ? $this->settings['mobile_only'] : true;
        
        if (!$enabled) {
            return false;
        }
        
        if ($mobile_only && !wp_is_mobile()) {
            return false;
        }
        
        // Check if hidden on current page
        $hidden_pages = isset($this->settings['hidden_pages']) ? $this->settings['hidden_pages'] : array();
        if (is_page() && in_array(get_the_ID(), $hidden_pages)) {
            return false;
        }
        
        return true;
    }
    
    private function get_menu_items() {
        $default_items = array(
            array(
                'icon' => 'fas fa-home',
                'label' => 'Home',
                'url' => home_url(),
                'type' => 'custom',
                'enabled' => true
            )
        );
        
        return isset($this->settings['menu_items']) ? $this->settings['menu_items'] : $default_items;
    }
    
    private function get_item_url($item) {
        switch ($item['type']) {
            case 'woocommerce_shop':
                return class_exists('WooCommerce') ? wc_get_page_permalink('shop') : home_url();
            case 'woocommerce_cart':
                return class_exists('WooCommerce') ? wc_get_cart_url() : home_url();
            case 'woocommerce_account':
                return class_exists('WooCommerce') ? wc_get_page_permalink('myaccount') : home_url();
            case 'page':
                return get_permalink($item['page_id']);
            default:
                return $item['url'];
        }
    }
    
    private function is_current_item($item) {
        $current_url = home_url($_SERVER['REQUEST_URI']);
        $item_url = $this->get_item_url($item);
        
        return $current_url === $item_url;
    }
    
    private function is_woocommerce_cart($item) {
        return $item['type'] === 'woocommerce_cart' && class_exists('WooCommerce');
    }
    
    private function is_custom_icon($icon) {
        return filter_var($icon, FILTER_VALIDATE_URL) !== false;
    }
    
    private function get_animation() {
        return isset($this->settings['animation']) ? $this->settings['animation'] : 'none';
    }
    
    private function get_item_attributes($item) {
        $attributes = '';
        
        if ($item['type'] === 'woocommerce_cart') {
            $attributes .= ' data-cart-item="true"';
        }
        
        return $attributes;
    }
    
    private function has_submenu($item) {
        // Pro feature - return false for free version
        return false;
    }
    
    private function render_submenu($item) {
        // Pro feature - empty for free version
    }
}
