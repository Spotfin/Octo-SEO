<?php
/**
 * Plugin Name: Octo SEO
 * Description: A barebones SEO plugin for managing SEO title, meta description, and basic schema
 * Version: 1.0.0
 * Author: Spotfin Creative
 * Text Domain: octo-seo
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('OCTO_SEO_VERSION', '1.0.0');
define('OCTO_SEO_PLUGIN_FILE', __FILE__);
define('OCTO_SEO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OCTO_SEO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load required files
require_once OCTO_SEO_PLUGIN_DIR . 'inc/class-meta.php';
require_once OCTO_SEO_PLUGIN_DIR . 'inc/class-schema.php';
require_once OCTO_SEO_PLUGIN_DIR . 'inc/admin/class-settings.php';
require_once OCTO_SEO_PLUGIN_DIR . 'inc/admin/class-admin.php';

/**
 * Initialize the plugin
 */
function octo_seo_init() {
    // Initialize the meta manager
    $meta = new OctoSEO\Meta();
    $meta->init();
    
    // Initialize settings (in admin and frontend)
    $settings = new OctoSEO\Admin\Settings();
    $settings->init();
    
    // Initialize admin features (only in admin)
    if (is_admin()) {
        $admin = new OctoSEO\Admin\Admin($settings);
        $admin->init();
    }
    
    // Initialize schema (only in frontend)
    if (!is_admin()) {
        $schema = new OctoSEO\Schema();
        $schema->init();
    }
}
add_action('plugins_loaded', 'octo_seo_init');

/**
 * Plugin activation hook
 */
function octo_seo_activate() {
    // Nothing needed for now, but we can add initialization here later
}
register_activation_hook(OCTO_SEO_PLUGIN_FILE, 'octo_seo_activate');
