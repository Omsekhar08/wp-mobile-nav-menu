<?php
function wp_mnb_menu_settings_page() {
    add_options_page(
        'WP Mobile Nav Buttons',
        'Mobile Nav Buttons',
        'manage_options',
        'wp-mnb-settings',
        'wp_mnb_settings_callback'
    );
}
add_action('admin_menu', 'wp_mnb_menu_settings_page');

function wp_mnb_settings_callback() {
    // Simple settings template
    echo '<div class="wrap"><h1>WP Mobile Nav Buttons</h1></div>';
}
