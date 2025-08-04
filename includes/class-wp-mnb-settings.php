<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_MNB_Settings {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
        add_action('wp_ajax_wp_mnb_save_settings', array($this, 'save_settings'));
        add_action('wp_ajax_wp_mnb_reset_settings', array($this, 'reset_settings'));
        add_action('wp_ajax_wp_mnb_import_demo', array($this, 'import_demo'));
    }
    
    public function add_admin_menu() {
        add_options_page(
            'WP Mobile Nav Buttons',
            'Mobile Nav Buttons',
            'manage_options',
            'wp-mnb-settings',
            array($this, 'settings_page')
        );
    }
    
    public function settings_init() {
        register_setting('wp_mnb_settings', 'wp_mnb_settings');
    }
    
    public function settings_page() {
        $settings = get_option('wp_mnb_settings', array());
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <nav class="nav-tab-wrapper">
                <a href="?page=wp-mnb-settings&tab=general" class="nav-tab <?php echo $current_tab == 'general' ? 'nav-tab-active' : ''; ?>">General</a>
                <a href="?page=wp-mnb-settings&tab=menu-items" class="nav-tab <?php echo $current_tab == 'menu-items' ? 'nav-tab-active' : ''; ?>">Menu Items</a>
                <a href="?page=wp-mnb-settings&tab=styling" class="nav-tab <?php echo $current_tab == 'styling' ? 'nav-tab-active' : ''; ?>">Styling</a>
                <a href="?page=wp-mnb-settings&tab=advanced" class="nav-tab <?php echo $current_tab == 'advanced' ? 'nav-tab-active' : ''; ?>">Advanced</a>
                <a href="?page=wp-mnb-settings&tab=pro" class="nav-tab <?php echo $current_tab == 'pro' ? 'nav-tab-active' : ''; ?>">Pro Features</a>
            </nav>
            
            <form method="post" action="" id="wp-mnb-settings-form">
                <?php wp_nonce_field('wp_mnb_save_settings', 'wp_mnb_nonce'); ?>
                
                <div class="tab-content">
                    <?php
                    switch ($current_tab) {
                        case 'general':
                            $this->render_general_tab($settings);
                            break;
                        case 'menu-items':
                            $this->render_menu_items_tab($settings);
                            break;
                        case 'styling':
                            $this->render_styling_tab($settings);
                            break;
                        case 'advanced':
                            $this->render_advanced_tab($settings);
                            break;
                        case 'pro':
                            $this->render_pro_tab($settings);
                            break;
                    }
                    ?>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button-primary" value="Save Changes" id="wp-mnb-save-settings">
                    <input type="button" name="reset" class="button" value="Reset to Default" id="wp-mnb-reset-settings">
                </p>
            </form>
        </div>
        
        <div id="wp-mnb-preview-container">
            <h3>Live Preview</h3>
            <div id="wp-mnb-preview"></div>
        </div>
        <?php
    }
    
    private function render_general_tab($settings) {
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">Enable Mobile Menu</th>
                <td>
                    <label>
                        <input type="checkbox" name="wp_mnb_settings[enabled]" value="1" <?php checked(isset($settings['enabled']) ? $settings['enabled'] : true); ?>>
                        Enable mobile bottom menu
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row">Mobile Only</th>
                <td>
                    <label>
                        <input type="checkbox" name="wp_mnb_settings[mobile_only]" value="1" <?php checked(isset($settings['mobile_only']) ? $settings['mobile_only'] : true); ?>>
                        Show only on mobile devices
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row">Hide on Pages</th>
                <td>
                    <?php
                    $pages = get_pages();
                    $hidden_pages = isset($settings['hidden_pages']) ? $settings['hidden_pages'] : array();
                    ?>
                    <fieldset>
                        <?php foreach ($pages as $page): ?>
                        <label>
                            <input type="checkbox" name="wp_mnb_settings[hidden_pages][]" value="<?php echo $page->ID; ?>" <?php checked(in_array($page->ID, $hidden_pages)); ?>>
                            <?php echo esc_html($page->post_title); ?>
                        </label><br>
                        <?php endforeach; ?>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row">Animation</th>
                <td>
                    <select name="wp_mnb_settings[animation]">
                        <option value="none" <?php selected(isset($settings['animation']) ? $settings['animation'] : 'none', 'none'); ?>>None</option>
                        <option value="slide-up" <?php selected(isset($settings['animation']) ? $settings['animation'] : 'none', 'slide-up'); ?>>Slide Up</option>
                        <option value="fade-in" <?php selected(isset($settings['animation']) ? $settings['animation'] : 'none', 'fade-in'); ?>>Fade In</option>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }
    
    private function render_menu_items_tab($settings) {
        $menu_items = isset($settings['menu_items']) ? $settings['menu_items'] : array();
        ?>
        <div id="wp-mnb-menu-items-container">
            <div class="menu-items-header">
                <h3>Menu Items</h3>
                <button type="button" class="button" id="add-menu-item">Add Menu Item</button>
            </div>
            
            <div id="menu-items-list">
                <?php foreach ($menu_items as $index => $item): ?>
                <div class="menu-item-row" data-index="<?php echo $index; ?>">
                    <div class="menu-item-handle">≡</div>
                    <div class="menu-item-content">
                        <input type="text" name="wp_mnb_settings[menu_items][<?php echo $index; ?>][label]" value="<?php echo esc_attr($item['label']); ?>" placeholder="Label">
                        <input type="text" name="wp_mnb_settings[menu_items][<?php echo $index; ?>][icon]" value="<?php echo esc_attr($item['icon']); ?>" placeholder="Icon class">
                        <select name="wp_mnb_settings[menu_items][<?php echo $index; ?>][type]" class="menu-item-type">
                            <option value="custom" <?php selected($item['type'], 'custom'); ?>>Custom Link</option>
                            <option value="page" <?php selected($item['type'], 'page'); ?>>Page</option>
                            <option value="woocommerce_shop" <?php selected($item['type'], 'woocommerce_shop'); ?>>WooCommerce Shop</option>
                            <option value="woocommerce_cart" <?php selected($item['type'], 'woocommerce_cart'); ?>>WooCommerce Cart</option>
                            <option value="woocommerce_account" <?php selected($item['type'], 'woocommerce_account'); ?>>WooCommerce Account</option>
                        </select>
                        <input type="url" name="wp_mnb_settings[menu_items][<?php echo $index; ?>][url]" value="<?php echo esc_url($item['url']); ?>" placeholder="URL">
                        <label><input type="checkbox" name="wp_mnb_settings[menu_items][<?php echo $index; ?>][enabled]" value="1" <?php checked($item['enabled']); ?>> Enabled</label>
                        <button type="button" class="button remove-menu-item">Remove</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <script type="text/template" id="menu-item-template">
            <div class="menu-item-row" data-index="{{index}}">
                <div class="menu-item-handle">≡</div>
                <div class="menu-item-content">
                    <input type="text" name="wp_mnb_settings[menu_items][{{index}}][label]" placeholder="Label">
                    <input type="text" name="wp_mnb_settings[menu_items][{{index}}][icon]" placeholder="Icon class">
                    <select name="wp_mnb_settings[menu_items][{{index}}][type]" class="menu-item-type">
                        <option value="custom">Custom Link</option>
                        <option value="page">Page</option>
                        <option value="woocommerce_shop">WooCommerce Shop</option>
                        <option value="woocommerce_cart">WooCommerce Cart</option>
                        <option value="woocommerce_account">WooCommerce Account</option>
                    </select>
                    <input type="text" name="wp_mnb_settings[menu_items][{{index}}][url]" placeholder="URL">
                    <label><input type="checkbox" name="wp_mnb_settings[menu_items][{{index}}][enabled]" value="1" checked> Enabled</label>
                    <button type="button" class="button remove-menu-item">Remove</button>
                </div>
            </div>
        </script>
        <?php
    }
    
    private function render_styling_tab($settings) {
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">Style Preset</th>
                <td>
                    <div class="style-presets">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                        <div class="style-preset <?php echo $i > 7 ? 'pro-style' : ''; ?>" data-style="<?php echo $i; ?>">
                            <input type="radio" name="wp_mnb_settings[style_preset]" value="<?php echo $i; ?>" <?php checked(isset($settings['style_preset']) ? $settings['style_preset'] : 1, $i); ?> <?php echo $i > 7 ? 'disabled' : ''; ?>>
                            <div class="preview-style style-<?php echo $i; ?>">
                                <div class="preview-item"><i class="icon"></i><span>Home</span></div>
                                <div class="preview-item"><i class="icon"></i><span>Shop</span></div>
                                <div class="preview-item active"><i class="icon"></i><span>Cart</span></div>
                            </div>
                            <span class="style-name">Style <?php echo $i; ?></span>
                            <?php if ($i > 7): ?>
                            <span class="pro-badge">PRO</span>
                            <?php endif; ?>
                        </div>
                        <?php endfor; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row">Background Color</th>
                <td>
                    <input type="text" name="wp_mnb_settings[bg_color]" value="<?php echo esc_attr(isset($settings['bg_color']) ? $settings['bg_color'] : '#ffffff'); ?>" class="wp-color-picker">
                </td>
            </tr>
            <tr>
                <th scope="row">Text Color</th>
                <td>
                    <input type="text" name="wp_mnb_settings[text_color]" value="<?php echo esc_attr(isset($settings['text_color']) ? $settings['text_color'] : '#666666'); ?>" class="wp-color-picker">
                </td>
            </tr>
            <tr>
                <th scope="row">Active Color</th>
                <td>
                    <input type="text" name="wp_mnb_settings[active_color]" value="<?php echo esc_attr(isset($settings['active_color']) ? $settings['active_color'] : '#007cba'); ?>" class="wp-color-picker">
                </td>
            </tr>
            <tr>
                <th scope="row">Border Radius</th>
                <td>
                    <input type="range" name="wp_mnb_settings[border_radius]" value="<?php echo esc_attr(isset($settings['border_radius']) ? $settings['border_radius'] : 0); ?>" min="0" max="30" class="slider">
                    <span class="slider-value"><?php echo isset($settings['border_radius']) ? $settings['border_radius'] : 0; ?>px</span>
                </td>
            </tr>
            <tr>
                <th scope="row">Shadow</th>
                <td>
                    <label>
                        <input type="checkbox" name="wp_mnb_settings[enable_shadow]" value="1" <?php checked(isset($settings['enable_shadow']) ? $settings['enable_shadow'] : true); ?>>
                        Enable shadow effect
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }
    
    private function render_advanced_tab($settings) {
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">Custom CSS</th>
                <td>
                    <textarea name="wp_mnb_settings[custom_css]" rows="10" cols="50" placeholder="/* Your custom CSS here */"><?php echo esc_textarea(isset($settings['custom_css']) ? $settings['custom_css'] : ''); ?></textarea>
                    <p class="description">Add your custom CSS to override default styles.</p>
                </td>
            </tr>
            <tr>
                <th scope="row">Import/Export</th>
                <td>
                    <button type="button" class="button" id="export-settings">Export Settings</button>
                    <input type="file" id="import-settings" accept=".json" style="display: none;">
                    <button type="button" class="button" onclick="document.getElementById('import-settings').click()">Import Settings</button>
                </td>
            </tr>
            <tr>
                <th scope="row">Demo Import</th>
                <td>
                    <button type="button" class="button" id="import-demo-1">Import Demo 1</button>
                    <button type="button" class="button" id="import-demo-2">Import Demo 2</button>
                    <button type="button" class="button" id="import-demo-3">Import Demo 3</button>
                </td>
            </tr>
        </table>
        <?php
    }
    
    private function render_pro_tab($settings) {
        ?>
        <div class="pro-features-tab">
            <h2>Upgrade to Pro</h2>
            <p>Unlock powerful features to enhance your mobile navigation experience:</p>
            
            <div class="pro-features-grid">
                <div class="pro-feature">
                    <h3>Submenu Support</h3>
                    <p>Create multi-level navigation with submenu support for better content organization.</p>
                </div>
                <div class="pro-feature">
                    <h3>Role-Based Menu Control</h3>
                    <p>Show different menu items based on user roles for personalized experience.</p>
                </div>
                <div class="pro-feature">
                    <h3>Advanced Styling Options</h3>
                    <p>Access to 5 additional premium styles (Style 8-12) with advanced customization.</p>
                </div>
                <div class="pro-feature">
                    <h3>WooCommerce Integration</h3>
                    <p>Live cart count, wishlist support, and advanced WooCommerce features.</p>
                </div>
                <div class="pro-feature">
                    <h3>Icon Upload</h3>
                    <p>Upload and use custom icons for your menu items.</p>
                </div>
                <div class="pro-feature">
                    <h3>Premium Support</h3>
                    <p>Get priority support from our expert team.</p>
                </div>
            </div>
            
            <div class="pro-cta">
                <a href="#" class="button button-primary button-hero">Upgrade to Pro - $29</a>
                <p>30-day money-back guarantee</p>
            </div>
        </div>
        <?php
    }
    
    public function save_settings() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wp_mnb_nonce'], 'wp_mnb_save_settings')) {
            wp_die('Unauthorized access');
        }
        
        $settings = array();
        if (isset($_POST['wp_mnb_settings'])) {
            $settings = $this->sanitize_settings($_POST['wp_mnb_settings']);
        }
        
        update_option('wp_mnb_settings', $settings);
        
        wp_send_json_success(array('message' => 'Settings saved successfully!'));
    }
    
    public function reset_settings() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'], 'wp_mnb_nonce')) {
            wp_die('Unauthorized access');
        }
        
        delete_option('wp_mnb_settings');
        
        wp_send_json_success(array('message' => 'Settings reset to default!'));
    }
    
    public function import_demo() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'], 'wp_mnb_nonce')) {
            wp_die('Unauthorized access');
        }
        
        $demo_id = sanitize_text_field($_POST['demo_id']);
        $demo_settings = $this->get_demo_settings($demo_id);
        
        update_option('wp_mnb_settings', $demo_settings);
        
        wp_send_json_success(array('message' => 'Demo imported successfully!'));
    }
    
    private function sanitize_settings($settings) {
        $sanitized = array();
        
        $sanitized['enabled'] = isset($settings['enabled']);
        $sanitized['mobile_only'] = isset($settings['mobile_only']);
        $sanitized['style_preset'] = intval($settings['style_preset']);
        $sanitized['bg_color'] = sanitize_hex_color($settings['bg_color']);
        $sanitized['text_color'] = sanitize_hex_color($settings['text_color']);
        $sanitized['active_color'] = sanitize_hex_color($settings['active_color']);
        $sanitized['border_radius'] = intval($settings['border_radius']);
        $sanitized['enable_shadow'] = isset($settings['enable_shadow']);
        $sanitized['animation'] = sanitize_text_field($settings['animation']);
        $sanitized['custom_css'] = wp_strip_all_tags($settings['custom_css']);
        
        // Sanitize menu items
        if (isset($settings['menu_items'])) {
            $sanitized['menu_items'] = array();
            foreach ($settings['menu_items'] as $item) {
                $sanitized['menu_items'][] = array(
                    'label' => sanitize_text_field($item['label']),
                    'icon' => sanitize_text_field($item['icon']),
                    'type' => sanitize_text_field($item['type']),
                    'url' => esc_url_raw($item['url']),
                    'enabled' => isset($item['enabled'])
                );
            }
        }
        
        // Sanitize hidden pages
        if (isset($settings['hidden_pages'])) {
            $sanitized['hidden_pages'] = array_map('intval', $settings['hidden_pages']);
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
                'bg_color' => '#000000',
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
