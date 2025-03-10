<?php
/**
 * Octo SEO - Admin Class
 *
 * Handles admin interface for SEO settings
 */

namespace OctoSEO;

if (!defined('ABSPATH')) {
    exit;
}

class Admin {
    /**
     * Initialize the admin functionality
     */
    public function init() {
        // Add meta box for post types
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        
        // Save post metadata
        add_action('save_post', array($this, 'save_meta_box'), 10, 2);
        
        // Add admin styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    }
    
    /**
     * Add meta boxes to post edit screens
     */
    public function add_meta_boxes() {
        // Get post types that support SEO settings
        $post_types = $this->get_supported_post_types();
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'octo_seo_meta_box',
                __('Octo SEO', 'octo-seo'),
                array($this, 'render_meta_box'),
                $post_type,
                'normal',
                'high'
            );
        }
    }
    
    /**
     * Render the SEO meta box
     *
     * @param \WP_Post $post Current post
     */
    public function render_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('octo_seo_meta_box', 'octo_seo_meta_box_nonce');
        
        // Get current values
        $meta = new Meta();
        $seo_title = $meta->get_seo_title($post->ID);
        $meta_description = $meta->get_meta_description($post->ID);
        
        // Get default title and description
        $default_title = get_the_title($post->ID);
        $default_description = $this->generate_default_description($post);
        
        // Output form fields
        ?>
        <div class="octo-seo-meta-box">
            <div class="octo-seo-field">
                <label for="octo_seo_title"><?php _e('SEO Title', 'octo-seo'); ?></label>
                <input type="text" id="octo_seo_title" name="octo_seo_title" value="<?php echo esc_attr($seo_title); ?>" class="large-text" />
                <p class="description">
                    <?php _e('Enter a custom SEO title. Leave blank to use the default post title.', 'octo-seo'); ?>
                    <br>
                    <strong><?php _e('Default:', 'octo-seo'); ?></strong> <?php echo esc_html($default_title); ?>
                </p>
            </div>
            
            <div class="octo-seo-field">
                <label for="octo_seo_description"><?php _e('Meta Description', 'octo-seo'); ?></label>
                <textarea id="octo_seo_description" name="octo_seo_description" rows="3" class="large-text"><?php echo esc_textarea($meta_description); ?></textarea>
                <p class="description">
                    <?php _e('Enter a meta description. Leave blank to use an excerpt from the content.', 'octo-seo'); ?>
                    <br>
                    <strong><?php _e('Default:', 'octo-seo'); ?></strong> <?php echo esc_html($default_description); ?>
                </p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Save the meta box data
     *
     * @param int $post_id Post ID
     * @param \WP_Post $post Post object
     */
    public function save_meta_box($post_id, $post) {
        // Check if nonce is set
        if (!isset($_POST['octo_seo_meta_box_nonce'])) {
            return;
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['octo_seo_meta_box_nonce'], 'octo_seo_meta_box')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check post type and user permissions
        if (!in_array($post->post_type, $this->get_supported_post_types())) {
            return;
        }
        
        // Check permissions
        $post_type_obj = get_post_type_object($post->post_type);
        if (!current_user_can($post_type_obj->cap->edit_post, $post_id)) {
            return;
        }
        
        // Save the metadata
        $meta = new Meta();
        
        // Save SEO title
        if (isset($_POST['octo_seo_title'])) {
            $meta->save_seo_title($post_id, $_POST['octo_seo_title']);
        }
        
        // Save meta description
        if (isset($_POST['octo_seo_description'])) {
            $meta->save_meta_description($post_id, $_POST['octo_seo_description']);
        }
    }
    
    /**
     * Enqueue admin styles
     *
     * @param string $hook Current admin page
     */
    public function enqueue_admin_styles($hook) {
        if (!in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }
        
        wp_enqueue_style(
            'octo-seo-admin',
            OCTO_SEO_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            OCTO_SEO_VERSION
        );
    }
    
    /**
     * Get supported post types
     *
     * @return array Supported post types
     */
    private function get_supported_post_types() {
        $post_types = array('post', 'page');
        
        // Allow filtering of post types
        return apply_filters('octo_seo_supported_post_types', $post_types);
    }
    
    /**
     * Generate default description from post content
     *
     * @param \WP_Post $post Post object
     * @return string Default description
     */
    private function generate_default_description($post) {
        // Strip all HTML tags
        $content = wp_strip_all_tags($post->post_content);
        
        // Remove shortcodes
        $content = strip_shortcodes($content);
        
        // Trim whitespace
        $content = trim($content);
        
        // If no content, return empty string
        if (empty($content)) {
            return '';
        }
        
        // Truncate to 160 characters
        if (strlen($content) > 160) {
            $content = substr($content, 0, 157) . '...';
        }
        
        return $content;
    }
}
