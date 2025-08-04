<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_MNB_Pro_Features {
    
    private $is_pro = false; // Set to true when pro version is active
    
    public function __construct() {
        add_action('wp_mnb_render_pro_notice', array($this, 'render_pro_notice'));
        add_filter('wp_mnb_available_styles', array($this, 'limit_styles_for_free'));
        add_filter('wp_mnb_menu_features', array($this, 'limit_features_for_free'));
    }
    
    public function is_pro_active() {
        return $this->is_pro;
    }
    
    public function render_pro_notice($feature) {
        if ($this->is_pro_active()) {
            return;
        }
        
        ?>
        <div class="wp-mnb-pro-notice">
            <h4>🚀 Pro Feature</h4>
            <p><?php echo esc_html($feature); ?> is available in the Pro version.</p>
            <a href="#" class="button button-primary">Upgrade to Pro</a>
        </div>
        <?php
    }
    
    public function limit_styles_for_free($styles) {
        if ($this->is_pro_active()) {
            return $styles;
        }
        
        // Limit to first 7 styles for free version
        return array_slice($styles, 0, 7, true);
    }
    
    public function limit_features_for_free($features) {
        if ($this->is_pro_active()) {
            return $features;
        }
        
        // Remove pro features for free version
        $pro_features = array('submenu', 'role_based_control', 'advanced_woocommerce', 'custom_icons');
        
        foreach ($pro_features as $pro_feature) {
            if (isset($features[$pro_feature])) {
                $features[$pro_feature]['enabled'] = false;
                $features[$pro_feature]['pro_only'] = true;
            }
        }
        
        return $features;
    }
    
    public function get_pro_features_list() {
        return array(
            'submenu' => array(
                'title' => 'Submenu Support',
                'description' => 'Create multi-level navigation with submenu support for better content organization.',
                'icon' => 'fas fa-list'
            ),
            'role_based_control' => array(
                'title' => 'Role-Based Menu Control',
                'description' => 'Show different menu items based on user roles for personalized experience.',
                'icon' => 'fas fa-users'
            ),
            'advanced_styling' => array(
                'title' => 'Advanced Styling Options',
                'description' => 'Access to 5 additional premium styles (Style 8-12) with advanced customization.',
                'icon' => 'fas fa-palette'
            ),
            'woocommerce_integration' => array(
                'title' => 'Advanced WooCommerce Integration',
                'description' => 'Live cart count, wishlist support, and advanced WooCommerce features.',
                'icon' => 'fab fa-wordpress'
            ),
            'custom_icons' => array(
                'title' => 'Custom Icon Upload',
                'description' => 'Upload and use custom icons for your menu items.',
                'icon' => 'fas fa-upload'
            ),
            'premium_support' => array(
                'title' => 'Premium Support',
                'description' => 'Get priority support from our expert team.',
                'icon' => 'fas fa-headset'
            )
        );
    }
}
