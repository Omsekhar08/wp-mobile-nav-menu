<?php
register_activation_hook(__FILE__, 'wp_mnb_activate');
register_deactivation_hook(__FILE__, 'wp_mnb_deactivate');

function wp_mnb_activate() {
    // Any activation logic (default settings etc.)
}

function wp_mnb_deactivate() {
    // Cleanup if needed
}
