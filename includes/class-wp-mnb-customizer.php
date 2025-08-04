<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_MNB_Customizer {
    
    public function __construct() {
        add_action('customize_register', array($this, 'register_customizer_controls'));
        add_action('customize_preview_init', array($this, 'enqueue_customizer_scripts'));
    }
    
    public function register_customizer_controls($wp_customize) {
        // Add section
        $wp_customize->add_section('wp_mnb_customizer', array(
            'title' => __('Mobile Nav Buttons', 'wp-mobile-nav-buttons'),
            'priority' => 160,
        ));
        
        // Enable/Disable
        $wp_customize->add_setting('wp_mnb_settings[enabled]', array(
            'default' => true,
            'type' => 'option',
            'sanitize_callback' => 'wp_validate_boolean',
        ));
        
        $wp_customize->add_control('wp_mnb_enabled', array(
            'label' => __('Enable Mobile Menu', 'wp-mobile-nav-buttons'),
            'section' => 'wp_mnb_customizer',
            'settings' => 'wp_mnb_settings[enabled]',
            'type' => 'checkbox',
        ));
        
        // Background Color
        $wp_customize->add_setting('wp_mnb_settings[bg_color]', array(
            'default' => '#ffffff',
            'type' => 'option',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport' => 'postMessage',
        ));
        
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'wp_mnb_bg_color', array(
            'label' => __('Background Color', 'wp-mobile-nav-buttons'),
            'section' => 'wp_mnb_customizer',
            'settings' => 'wp_mnb_settings[bg_color]',
        )));
        
        // Text Color
        $wp_customize->add_setting('wp_mnb_settings[text_color]', array(
            'default' => '#666666',
            'type' => 'option',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport' => 'postMessage',
        ));
        
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'wp_mnb_text_color', array(
            'label' => __('Text Color', 'wp-mobile-nav-buttons'),
            'section' => 'wp_mnb_customizer',
            'settings' => 'wp_mnb_settings[text_color]',
        )));
        
        // Active Color
        $wp_customize->add_setting('wp_mnb_settings[active_color]', array(
            'default' => '#007cba',
            'type' => 'option',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport' => 'postMessage',
        ));
        
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'wp_mnb_active_color', array(
            'label' => __('Active Color', 'wp-mobile-nav-buttons'),
            'section' => 'wp_mnb_customizer',
            'settings' => 'wp_mnb_settings[active_color]',
        )));
        
        // Style Preset
        $wp_customize->add_setting('wp_mnb_settings[style_preset]', array(
            'default' => 1,
            'type' => 'option',
            'sanitize_callback' => 'absint',
            'transport' => 'postMessage',
        ));
        
        $wp_customize->add_control('wp_mnb_style_preset', array(
            'label' => __('Style Preset', 'wp-mobile-nav-buttons'),
            'section' => 'wp_mnb_customizer',
            'settings' => 'wp_mnb_settings[style_preset]',
            'type' => 'select',
            'choices' => array(
                1 => __('Style 1 - Default', 'wp-mobile-nav-buttons'),
                2 => __('Style 2 - Dark', 'wp-mobile-nav-buttons'),
                3 => __('Style 3 - Rounded', 'wp-mobile-nav-buttons'),
                4 => __('Style 4 - Minimal', 'wp-mobile-nav-buttons'),
                5 => __('Style 5 - Colorful', 'wp-mobile-nav-buttons'),
                6 => __('Style 6 - Gradient', 'wp-mobile-nav-buttons'),
                7 => __('Style 7 - Elegant', 'wp-mobile-nav-buttons'),
            ),
        ));
        
        // Border Radius
        $wp_customize->add_setting('wp_mnb_settings[border_radius]', array(
            'default' => 0,
            'type' => 'option',
            'sanitize_callback' => 'absint',
            'transport' => 'postMessage',
        ));
        
        $wp_customize->add_control('wp_mnb_border_radius', array(
            'label' => __('Border Radius', 'wp-mobile-nav-buttons'),
            'section' => 'wp_mnb_customizer',
            'settings' => 'wp_mnb_settings[border_radius]',
            'type' => 'range',
            'input_attrs' => array(
                'min' => 0,
                'max' => 30,
                'step' => 1,
            ),
        ));
    }
    
    public function enqueue_customizer_scripts() {
        wp_enqueue_script('wp-mnb-customizer', WP_MNB_PLUGIN_URL . 'assets/js/customizer.js', array('jquery', 'customize-preview'), WP_MNB_VERSION, true);
    }
}
