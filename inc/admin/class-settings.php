<?php
/**
 * Octo SEO - Settings Class
 *
 * Handles plugin settings and admin panel
 */

declare(strict_types=1);

namespace OctoSEO\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Settings {
    /**
     * Settings page slug
     */
    const SETTINGS_PAGE = 'octo-seo-settings';
    
    /**
     * Settings option name
     */
    const OPTION_NAME = 'octo_seo_settings';
    
    /**
     * Initialize the settings
     */
    public function init(): void {
        // Add menu item
        add_action('admin_menu', [$this, 'add_menu_pages']);
        
        // Register settings
        add_action('admin_init', [$this, 'register_settings']);
    }
    
    /**
     * Add menu pages to admin menu
     */
    public function add_menu_pages(): void {
        // Add top level menu
        add_menu_page(
            __('Octo SEO', 'octo-seo'),
            __('Octo SEO', 'octo-seo'),
            'manage_options',
            self::SETTINGS_PAGE,
            [$this, 'render_settings_page'],
            'dashicons-search', // SEO-related icon
            90 // Position after Settings
        );
        
        // Add settings submenu (same as parent to avoid duplicate menu items)
        add_submenu_page(
            self::SETTINGS_PAGE,
            __('Settings', 'octo-seo'),
            __('Settings', 'octo-seo'),
            'manage_options',
            self::SETTINGS_PAGE,
            [$this, 'render_settings_page']
        );
        
    }
    
    /**
     * Register settings
     */
    public function register_settings(): void {
        register_setting(
            self::SETTINGS_PAGE,
            self::OPTION_NAME,
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_settings'],
                'default' => $this->get_default_settings(),
            ]
        );
        
        add_settings_section(
            'octo_seo_post_types_section',
            __('Post Types', 'octo-seo'),
            [$this, 'render_post_types_section'],
            self::SETTINGS_PAGE
        );
        
        add_settings_field(
            'octo_seo_post_types',
            __('Enable SEO for:', 'octo-seo'),
            [$this, 'render_post_types_field'],
            self::SETTINGS_PAGE,
            'octo_seo_post_types_section'
        );
    }
    /**
          * Render settings page
          */
    public function render_settings_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php $this->render_info_panel(); ?>
            
            <form action="options.php" method="post">
                <?php
                settings_fields(self::SETTINGS_PAGE);
                do_settings_sections(self::SETTINGS_PAGE);
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
          * Render information panel
          */
    private function render_info_panel(): void {
        ?>
        <div class="octo-seo-info-panel">
            <style>
                .octo-seo-info-panel {
                    background: #fff;
                    border-left: 4px solid #2271b1;
                    box-shadow: 0 1px 1px rgba(0,0,0,.04);
                    margin: 20px 0;
                    padding: 12px 20px;
                }
                .octo-seo-info-panel h2 {
                    margin-top: 0;
                }
                .octo-seo-info-panel p {
                    margin-bottom: 10px;
                }
                .octo-seo-info-panel .octo-seo-logo {
                    float: right;
                    max-width: 150px;
                    margin-left: 20px;
                }
                .octo-seo-info-panel .octo-seo-info-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    grid-gap: 20px;
                }
                .octo-seo-info-panel .octo-seo-info-section {
                    margin-bottom: 15px;
                }
                @media (max-width: 782px) {
                    .octo-seo-info-panel .octo-seo-info-grid {
                        grid-template-columns: 1fr;
                    }
                }
            </style>
            
            <h2><?php _e('About Octo SEO', 'octo-seo'); ?></h2>
            
            <div class="octo-seo-info-grid">
                <div class="octo-seo-info-section">
                    <h3><?php _e('Plugin Information', 'octo-seo'); ?></h3>
                    <p>
                        <?php _e('Octo SEO is a lightweight SEO plugin designed to help you optimize your content for search engines without the bloat of larger SEO plugins.', 'octo-seo'); ?>
                    </p>
                    <p>
                        <?php _e('Version:', 'octo-seo'); ?> <strong><?php echo esc_html(OCTO_SEO_VERSION); ?></strong>
                    </p>
                    <p>
                        <?php _e('Features:', 'octo-seo'); ?>
                    </p>
                    <ul>
                        <li><?php _e('Custom SEO titles for any content', 'octo-seo'); ?></li>
                        <li><?php _e('Meta descriptions to improve click-through rates', 'octo-seo'); ?></li>
                        <li><?php _e('Schema.org structured data for better search visibility', 'octo-seo'); ?></li>
                        <li><?php _e('Works with any post type you choose', 'octo-seo'); ?></li>
                    </ul>
                </div>
                
                <div class="octo-seo-info-section">
                    <h3><?php _e('Support Information', 'octo-seo'); ?></h3>
                    <p>
                        <?php _e('Developed and maintained by:', 'octo-seo'); ?>
                        <br>
                        <a href="https://spotfincreative.com" target="_blank">
                            <strong>Spotfin Creative</strong>
                        </a>
                    </p>
                    <p>
                        <?php _e('Need help with this plugin?', 'octo-seo'); ?>
                        <br>
                        <?php _e('Contact our support team:', 'octo-seo'); ?>
                        <br>
                        <a href="mailto:support@spotfincreative.com">support@spotfincreative.com</a>
                    </p>
                    <p>
                        <?php _e('Visit our website for more WordPress solutions:', 'octo-seo'); ?>
                        <br>
                        <a href="https://spotfincreative.com" target="_blank">spotfincreative.com</a>
                    </p>
                </div>
            </div>
            
            <div style="clear: both;"></div>
        </div>
        <?php
    }
    /**
     * Render post types section description
     */
    public function render_post_types_section(): void {
        echo '<p>' . esc_html__('Select which post types should have SEO options.', 'octo-seo') . '</p>';
    }
    
    /**
     * Render post types field
     */
    public function render_post_types_field(): void {
        $settings = $this->get_settings();
        $post_types = $this->get_available_post_types();
        
        foreach ($post_types as $post_type => $label) {
            $checked = in_array($post_type, $settings['post_types'], true);
            
            echo '<label style="display: block; margin-bottom: 5px;">';
            echo '<input type="checkbox" name="' . esc_attr(self::OPTION_NAME) . '[post_types][]" value="' . esc_attr($post_type) . '" ' . checked($checked, true, false) . '>';
            echo ' ' . esc_html($label);
            echo '</label>';
        }
    }
    
    /**
     * Sanitize settings
     *
     * @param array $input Input array
     * @return array Sanitized settings
     */
    public function sanitize_settings($input): array {
        $sanitized = [];
        
        // Sanitize post types
        $sanitized['post_types'] = [];
        
        if (isset($input['post_types']) && is_array($input['post_types'])) {
            $available_post_types = array_keys($this->get_available_post_types());
            
            foreach ($input['post_types'] as $post_type) {
                if (in_array($post_type, $available_post_types, true)) {
                    $sanitized['post_types'][] = $post_type;
                }
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Get all available post types
     *
     * @return array Post types with labels
     */
    public function get_available_post_types(): array {
        $post_types = [];
        $post_type_objects = get_post_types(['public' => true], 'objects');
        
        foreach ($post_type_objects as $post_type => $object) {
            $post_types[$post_type] = $object->labels->name;
        }
        
        return $post_types;
    }
    
    /**
     * Get default settings
     *
     * @return array Default settings
     */
    public function get_default_settings(): array {
        return [
            'post_types' => ['post', 'page'],
        ];
    }
    
    /**
     * Get settings
     *
     * @return array Settings
     */
    public function get_settings(): array {
        $settings = get_option(self::OPTION_NAME, $this->get_default_settings());
        return wp_parse_args($settings, $this->get_default_settings());
    }
    
    /**
     * Get enabled post types
     *
     * @return array Post types that should have SEO options
     */
    public function get_enabled_post_types(): array {
        $settings = $this->get_settings();
        return $settings['post_types'];
    }
}
