<?php
/*
Plugin Name: WP Mobile Nav Buttons
Plugin URI:https://homexfashions.com/wp-mobile-nav-buttons
Description: The ultimate WordPress plugin for creating streamlined bottom navigation menu for mobile users with advanced customization options.
Version: 1.0.0
Author: Om Sekhar Sura
Author URI: https://homexfashions.com
Text Domain: wp-mobile-nav-buttons
Domain Path: /languages
Requires at least: 5.0
Tested up to: 6.3
Requires PHP: 7.4
License: GPL v2 or later
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WP_MNB_VERSION', '1.0.0');
define('WP_MNB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_MNB_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WP_MNB_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main WP Mobile Nav Buttons Class
 */
class WP_Mobile_Nav_Buttons {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_footer', array($this, 'render_mobile_menu'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once WP_MNB_PLUGIN_PATH . 'includes/class-wp-mnb-settings.php';
        require_once WP_MNB_PLUGIN_PATH . 'includes/class-wp-mnb-menu-renderer.php';
        require_once WP_MNB_PLUGIN_PATH . 'includes/class-wp-mnb-customizer.php';
        require_once WP_MNB_PLUGIN_PATH . 'includes/class-wp-mnb-woocommerce.php';
        require_once WP_MNB_PLUGIN_PATH . 'includes/class-wp-mnb-pro-features.php';
        require_once WP_MNB_PLUGIN_PATH . 'includes/class-wp-mnb-ajax-handler.php';
        
        // Initialize classes
        if (is_admin()) {
            new WP_MNB_Settings();
            new WP_MNB_Customizer();
        }
        
        new WP_MNB_Menu_Renderer();
        new WP_MNB_Ajax_Handler();
        
        // Load WooCommerce integration if WooCommerce is active
        if (class_exists('WooCommerce')) {
            new WP_MNB_WooCommerce();
        }
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('wp-mobile-nav-buttons', false, basename(dirname(__FILE__)) . '/languages');
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        if (!$this->should_load_assets()) {
            return;
        }
        
        wp_enqueue_style('wp-mnb-frontend', WP_MNB_PLUGIN_URL . 'assets/css/frontend.css', array(), WP_MNB_VERSION);
        wp_enqueue_script('wp-mnb-frontend', WP_MNB_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), WP_MNB_VERSION, true);
        
        // Localize script
        wp_localize_script('wp-mnb-frontend', 'wp_mnb_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_mnb_nonce'),
            'cart_url' => class_exists('WooCommerce') ? wc_get_cart_url() : '',
        ));
        
        // Add dynamic styles
        $this->add_dynamic_styles();
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'settings_page_wp-mnb-settings') {
            return;
        }
        
        wp_enqueue_style('wp-mnb-admin', WP_MNB_PLUGIN_URL . 'assets/css/admin.css', array(), WP_MNB_VERSION);
        wp_enqueue_script('wp-mnb-admin', WP_MNB_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'wp-color-picker'), WP_MNB_VERSION, true);
        wp_enqueue_media();
    }
    
    /**
     * Check if assets should be loaded
     */
    private function should_load_assets() {
        $settings = get_option('wp_mnb_settings', array());
        $enabled = isset($settings['enabled']) ? $settings['enabled'] : true;
        $mobile_only = isset($settings['mobile_only']) ? $settings['mobile_only'] : true;
        
        return $enabled && (!$mobile_only || wp_is_mobile());
    }
    
    /**
     * Add dynamic styles
     */
    private function add_dynamic_styles() {
        $settings = get_option('wp_mnb_settings', array());
        $style_id = isset($settings['style_preset']) ? $settings['style_preset'] : 1;
        
        $css = $this->generate_dynamic_css($settings, $style_id);
        
        if (!empty($css)) {
            wp_add_inline_style('wp-mnb-frontend', $css);
        }
    }
    
    /**
     * Generate dynamic CSS
     */
    private function generate_dynamic_css($settings, $style_id) {
        $css = '';
        
        // Background color
        if (!empty($settings['bg_color'])) {
            $css .= '.wp-mnb-container { background-color: ' . sanitize_hex_color($settings['bg_color']) . ' !important; }';
        }
        
        // Text color
        if (!empty($settings['text_color'])) {
            $css .= '.wp-mnb-item { color: ' . sanitize_hex_color($settings['text_color']) . ' !important; }';
        }
        
        // Active color
        if (!empty($settings['active_color'])) {
            $css .= '.wp-mnb-item.active, .wp-mnb-item:hover { color: ' . sanitize_hex_color($settings['active_color']) . ' !important; }';
        }
        
        // Border radius
        if (isset($settings['border_radius'])) {
            $radius = intval($settings['border_radius']);
            $css .= '.wp-mnb-container { border-top-left-radius: ' . $radius . 'px; border-top-right-radius: ' . $radius . 'px; }';
        }
        
        return $css;
    }
    
    /**
     * Render mobile menu
     */
    public function render_mobile_menu() {
        if (!$this->should_load_assets()) {
            return;
        }
        
        $renderer = new WP_MNB_Menu_Renderer();
        $renderer->render();
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        $default_settings = array(
            'enabled' => true,
            'mobile_only' => true,
            'style_preset' => 1,
            'bg_color' => '#ffffff',
            'text_color' => '#666666',
            'active_color' => '#007cba',
            'border_radius' => 0,
            'menu_items' => array(
                array(
                    'icon' => 'fas fa-home',
                    'label' => 'Home',
                    'url' => home_url(),
                    'type' => 'custom',
                    'enabled' => true
                ),
                array(
                    'icon' => 'fas fa-shopping-cart',
                    'label' => 'Shop',
                    'url' => '',
                    'type' => 'woocommerce_shop',
                    'enabled' => true
                ),
                array(
                    'icon' => 'fas fa-user',
                    'label' => 'Account',
                    'url' => '',
                    'type' => 'woocommerce_account',
                    'enabled' => true
                ),
                array(
                    'icon' => 'fas fa-envelope',
                    'label' => 'Contact',
                    'url' => home_url('/contact'),
                    'type' => 'custom',
                    'enabled' => true
                ),
            )
        );
        
        add_option('wp_mnb_settings', $default_settings);
        
        // Create upload directory
        $upload_dir = wp_upload_dir();
        $mnb_dir = $upload_dir['basedir'] . '/wp-mobile-nav-buttons/';
        if (!file_exists($mnb_dir)) {
            wp_mkdir_p($mnb_dir);
        }
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Cleanup if needed
    }
}

// Initialize the plugin
new WP_Mobile_Nav_Buttons();
