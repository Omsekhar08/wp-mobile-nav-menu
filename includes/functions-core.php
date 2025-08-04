<?php
function wp_mnb_render_mobile_menu() {
    if (!wp_is_mobile()) return;

    $menu_items = get_option('wp_mnb_menu_items', array(
        array('icon' => 'home', 'label' => 'Home', 'link' => home_url()),
        array('icon' => 'cart', 'label' => 'Cart', 'link' => wc_get_cart_url()),
        // Add more default items here
    ));

    echo '<nav id="wp-mnb-mobile-nav" class="wp-mnb-bottom-menu">';
    foreach ($menu_items as $item) {
        echo '<a href="' . esc_url($item['link']) . '" class="wp-mnb-icon-link">';
        echo '<span class="wp-mnb-icon ' . esc_attr($item['icon']) . '"></span>';
        echo '<span class="wp-mnb-label">' . esc_html($item['label']) . '</span>';
        echo '</a>';
    }
    echo '</nav>';
}
add_action('wp_footer', 'wp_mnb_render_mobile_menu');
