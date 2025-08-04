<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_MNB_Ajax_Handler {
    
    public function __construct() {
        add_action('wp_ajax_wp_mnb_save_settings', array($this, 'save_settings'));
        add_action('wp_ajax_wp_mnb_reset_settings', array($this, 'reset_settings'));
        add_action('wp_ajax_wp_mnb_import_demo', array($this, 'import_demo'));
        add_action('wp_ajax_wp_mnb_export_settings', array($this, 'export_settings'));
        add_action('wp_ajax_wp_mnb_upload_icon', array($this, 'upload_icon'));
        add_action('wp_ajax_wp_mnb_get_preview', array($this, 'get_preview'));
    }
    
    public function save_settings() {
        check_ajax_referer('wp_mnb_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $settings = isset($_POST['settings']) ? $_POST['settings'] : array();
        $sanitized_settings = $this->sanitize_settings($settings);
        
        update_option('wp_mnb_settings', $sanitized_settings);
        
        wp_send_json_success(array('message' => 'Settings saved successfully!'));
    }
    
    public function reset_settings() {
        check_ajax_referer('wp_mnb_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        delete_option('wp_mnb_settings');
        
        wp_send_json_success(array('message' => 'Settings reset successfully!'));
    }
    
    public function import_demo() {
        check_ajax_referer('wp_mnb_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $demo_id = sanitize_text_field($_POST['demo_id']);
        $demo_settings = $this->get_demo_settings($demo_id);
        
        update_option('wp_mnb_settings', $demo_settings);
        
        wp_send_json_success(array(
            'message' => 'Demo imported successfully!',
            'settings' => $demo_settings
        ));
    }
    
    public function export_settings() {
        check_ajax_referer('wp_mnb_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $settings = get_option('wp_mnb_settings', array());
        
        wp_send_json_success(array(
            'settings' => $settings,
            'filename' => 'wp-mnb-settings-' . date('Y-m-d') . '.json'
        ));
    }
    
    public function upload_icon() {
        check_ajax_referer('wp_mnb_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        $uploaded_file = $_FILES['icon'];
        $upload_overrides = array('test_form' => false);
        
        $movefile = wp_handle_upload($uploaded_file, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            wp_send_json_success(array(
                'url' => $movefile['url'],
                'message' => 'Icon uploaded successfully!'
            ));
        } else {
            wp_send_json_error($movefile['error']);
        }
    }
    
    public function get_preview() {
        check_ajax_referer('wp_mnb_nonce', 'nonce');
        
        $settings = isset($_POST['settings']) ? $_POST['settings'] : array();
        
        ob_start();
        $this->render_preview($settings);
        $preview_html = ob_get_clean();
        
        wp_send_json_success(array('preview' => $preview_html));
    }
    
    private function render_preview($settings) {
        $style_class = 'wp-mnb-style-' . (isset($settings['style_preset']) ? $settings['style_preset'] : 1);
        $menu_items = isset($settings['menu_items']) ? $settings['menu_items'] : array();
        
        ?>
        <div class="wp-mnb-preview-container <?php echo esc_attr($style_class); ?>">
            <nav class="wp-mnb-nav">
                <?php foreach ($menu_items as $item): ?>
                    <?php if (isset($item['enabled']) && $item['enabled']): ?>
                    <a href="#" class="wp-mnb-item">
                        <div class="wp-mnb-icon-wrapper">
                            <?php if (filter_var($item['icon'], FILTER_VALIDATE_URL)): ?>
                                <img src="<?php echo esc_url($item['icon']); ?>" alt="<?php echo esc_attr($item['label']); ?>" class="wp-mnb-custom-icon">
                            <?php else: ?>
                                <i class="<?php echo esc_attr($item['icon']); ?> wp-mnb-icon"></i>
                            <?php endif; ?>
                        </div>
                        <span class="wp-mnb-label"><?php echo esc_html($item['label']); ?></span>
                    </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        </div>
        <?php
    }
    
    private function sanitize_settings($settings) {
        // Add sanitization logic here
        // This is a simplified version - you should expand this based on your needs
        $sanitized = array();
        
        if (isset($settings['enabled'])) {
            $sanitized['enabled'] = (bool) $settings['enabled'];
        }
        
        if (isset($settings['mobile_only'])) {
            $sanitized['mobile_only'] = (bool) $settings['mobile_only'];
        }
        
        if (isset($settings['style_preset'])) {
            $sanitized['style_preset'] = intval($settings['style_preset']);
        }
        
        if (isset($settings['bg_color'])) {
            $sanitized['bg_color'] = sanitize_hex_color($settings['bg_color']);
        }
        
        if (isset($settings['text_color'])) {
            $sanitized['text_color'] = sanitize_hex_color($settings['text_color']);
        }
        
        if (isset($settings['active_color'])) {
            $sanitized['active_color'] = sanitize_hex_color($settings['active_color']);
        }
        
        return $sanitized;
    }
    
    private function get_demo_settings($demo_id) {
        $demos = array(
            '1' => array(
                'enabled' => true,
                'mobile_only' => true,
                'style_preset' => 1,
                'bg_color' => '#ffffff',
                'text_color' => '#333333',
                'active_color' => '#007cba',
                'menu_items' => array(
                    array('label' => 'Home', 'icon' => 'fas fa-home', 'type' => 'custom', 'url' => home_url(), 'enabled' => true),
                    array('label' => 'Shop', 'icon' => 'fas fa-shopping-bag', 'type' => 'woocommerce_shop', 'url' => '', 'enabled' => true),
                    array('label' => 'Cart', 'icon' => 'fas fa-shopping-cart', 'type' => 'woocommerce_cart', 'url' => '', 'enabled' => true),
                    array('label' => 'Account', 'icon' => 'fas fa-user', 'type' => 'woocommerce_account', 'url' => '', 'enabled' => true),
                )
            ),
            '2' => array(
                'enabled' => true,
                'mobile_only' => true,
                'style_preset' => 2,
                'bg_color' => '#1a1a1a',
                'text_color' => '#ffffff',
                'active_color' => '#ff6b6b',
                'menu_items' => array(
                    array('label' => 'Home', 'icon' => 'fas fa-home', 'type' => 'custom', 'url' => home_url(), 'enabled' => true),
                    array('label' => 'Categories', 'icon' => 'fas fa-th-large', 'type' => 'custom', 'url' => home_url('/categories'), 'enabled' => true),
                    array('label' => 'Search', 'icon' => 'fas fa-search', 'type' => 'custom', 'url' => home_url('/search'), 'enabled' => true),
                    array('label' => 'Profile', 'icon' => 'fas fa-user-circle', 'type' => 'custom', 'url' => home_url('/profile'), 'enabled' => true),
                )
            ),
            '3' => array(
                'enabled' => true,
                'mobile_only' => true,
                'style_preset' => 3,
                'bg_color' => '#f8f9fa',
                'text_color' => '#6c757d',
                'active_color' => '#28a745',
                'border_radius' => 15,
                'menu_items' => array(
                    array('label' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'type' => 'custom', 'url' => home_url('/dashboard'), 'enabled' => true),
                    array('label' => 'Orders', 'icon' => 'fas fa-receipt', 'type' => 'custom', 'url' => home_url('/orders'), 'enabled' => true),
                    array('label' => 'Wishlist', 'icon' => 'fas fa-heart', 'type' => 'custom', 'url' => home_url('/wishlist'), 'enabled' => true),
                    array('label' => 'Settings', 'icon' => 'fas fa-cog', 'type' => 'custom', 'url' => home_url('/settings'), 'enabled' => true),
                )
            )
        );
        
        return isset($demos[$demo_id]) ? $demos[$demo_id] : $demos['1'];
    }
}
