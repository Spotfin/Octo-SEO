<?php
/**
 * Octo SEO - Meta Class
 *
 * Handles SEO metadata for posts and pages
 */

namespace OctoSEO;

if (!defined('ABSPATH')) {
    exit;
}

class Meta {
    /**
     * Initialize the meta functionality
     */
    public function init() {
        // Filter the page title
        add_filter('wp_title', array($this, 'filter_title'), 10, 3);
        add_filter('document_title_parts', array($this, 'filter_document_title_parts'), 10);
        
        // Add meta tags to wp_head
        add_action('wp_head', array($this, 'output_meta_tags'), 1);
    }
    
    /**
     * Filter the page title
     *
     * @param string $title Original title
     * @param string $sep Title separator
     * @param string $location Location of the title
     * @return string Modified title
     */
    public function filter_title($title, $sep, $location) {
        if (!is_singular()) {
            return $title;
        }
        
        $post_id = get_the_ID();
        $seo_title = $this->get_seo_title($post_id);
        
        if ($seo_title) {
            return $seo_title;
        }
        
        return $title;
    }
    
    /**
     * Filter document title parts (WP 4.4+)
     *
     * @param array $title_parts The document title parts.
     * @return array Modified title parts
     */
    public function filter_document_title_parts($title_parts) {
        if (!is_singular()) {
            return $title_parts;
        }
        
        $post_id = get_the_ID();
        $seo_title = $this->get_seo_title($post_id);
        
        if ($seo_title) {
            $title_parts['title'] = $seo_title;
        }
        
        return $title_parts;
    }
    
    /**
     * Output meta tags
     */
    public function output_meta_tags() {
        // Only on singular pages
        if (!is_singular()) {
            return;
        }
        
        $post_id = get_the_ID();
        $description = $this->get_meta_description($post_id);
        
        if ($description) {
            echo '<meta name="description" content="' . esc_attr($description) . '" />' . "\n";
        }
    }
    
    /**
     * Get SEO title for a post
     *
     * @param int $post_id Post ID
     * @return string SEO title or empty string
     */
    public function get_seo_title($post_id) {
        return get_post_meta($post_id, '_octo_seo_title', true);
    }
    
    /**
     * Get meta description for a post
     *
     * @param int $post_id Post ID
     * @return string Meta description or empty string
     */
    public function get_meta_description($post_id) {
        return get_post_meta($post_id, '_octo_seo_description', true);
    }
    
    /**
     * Save SEO title
     *
     * @param int $post_id Post ID
     * @param string $title SEO title
     */
    public function save_seo_title($post_id, $title) {
        update_post_meta($post_id, '_octo_seo_title', sanitize_text_field($title));
    }
    
    /**
     * Save meta description
     *
     * @param int $post_id Post ID
     * @param string $description Meta description
     */
    public function save_meta_description($post_id, $description) {
        update_post_meta($post_id, '_octo_seo_description', sanitize_text_field($description));
    }
}
