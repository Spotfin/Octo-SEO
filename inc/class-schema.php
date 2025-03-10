<?php
/**
 * Octo SEO - Schema Class
 *
 * Handles schema.org structured data
 */

namespace OctoSEO;

if (!defined('ABSPATH')) {
    exit;
}

class Schema {
    /**
     * Initialize the schema functionality
     */
    public function init() {
        // Add schema to wp_head
        add_action('wp_head', array($this, 'output_schema'), 10);
    }
    
    /**
     * Output schema.org structured data
     */
    public function output_schema() {
        // Only add schema on singular pages for now
        if (!is_singular()) {
            return;
        }
        
        $post_id = get_the_ID();
        $schema_data = $this->generate_schema($post_id);
        
        if (!empty($schema_data)) {
            echo "\n<!-- Octo SEO Schema -->\n";
            echo '<script type="application/ld+json">' . PHP_EOL;
            echo json_encode($schema_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            echo PHP_EOL . '</script>' . PHP_EOL;
        }
    }
    
    /**
     * Generate schema data for a post
     *
     * @param int $post_id Post ID
     * @return array Schema data
     */
    private function generate_schema($post_id) {
        // Get post data
        $post = get_post($post_id);
        
        if (!$post) {
            return array();
        }
        
        // Get post metadata
        $meta = new Meta();
        $seo_title = $meta->get_seo_title($post_id) ?: get_the_title($post_id);
        $description = $meta->get_meta_description($post_id) ?: $this->get_default_description($post);
        
        // Base schema
        $schema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                // WebPage schema
                $this->get_webpage_schema($post, $seo_title, $description),
                
                // Website schema
                $this->get_website_schema(),
                
                // Organization schema
                $this->get_organization_schema()
            ]
        ];
        
        // Add Article schema for posts
        if ('post' === $post->post_type) {
            $schema['@graph'][] = $this->get_article_schema($post, $seo_title, $description);
        }
        
        return $schema;
    }
    
    /**
     * Get WebPage schema
     *
     * @param \WP_Post $post Post object
     * @param string $title Page title
     * @param string $description Page description
     * @return array WebPage schema
     */
    private function get_webpage_schema($post, $title, $description) {
        $schema = [
            '@type' => 'WebPage',
            '@id' => get_permalink($post->ID) . '#webpage',
            'url' => get_permalink($post->ID),
            'name' => $title,
            'description' => $description,
            'inLanguage' => get_bloginfo('language'),
            'isPartOf' => [
                '@id' => home_url('/') . '#website'
            ],
            'datePublished' => get_the_date('c', $post),
            'dateModified' => get_the_modified_date('c', $post)
        ];
        
        // Add featured image if available
        if (has_post_thumbnail($post->ID)) {
            $schema['primaryImageOfPage'] = [
                '@id' => get_permalink($post->ID) . '#primaryimage'
            ];
            
            $schema['image'] = $this->get_image_schema($post->ID);
        }
        
        return $schema;
    }
    
    /**
     * Get Website schema
     *
     * @return array Website schema
     */
    private function get_website_schema() {
        return [
            '@type' => 'WebSite',
            '@id' => home_url('/') . '#website',
            'url' => home_url('/'),
            'name' => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'inLanguage' => get_bloginfo('language'),
            'publisher' => [
                '@id' => home_url('/') . '#organization'
            ]
        ];
    }
    
    /**
     * Get Organization schema
     *
     * @return array Organization schema
     */
    private function get_organization_schema() {
        $schema = [
            '@type' => 'Organization',
            '@id' => home_url('/') . '#organization',
            'name' => get_bloginfo('name'),
            'url' => home_url('/')
        ];
        
        // Add logo if available
        $custom_logo_id = get_theme_mod('custom_logo');
        if ($custom_logo_id) {
            $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
            
            if ($logo_url) {
                $schema['logo'] = [
                    '@type' => 'ImageObject',
                    'url' => $logo_url,
                    'contentUrl' => $logo_url
                ];
                
                $schema['image'] = [
                    '@id' => home_url('/') . '#logo'
                ];
            }
        }
        
        return $schema;
    }
    
    /**
     * Get Article schema
     *
     * @param \WP_Post $post Post object
     * @param string $title Article title
     * @param string $description Article description
     * @return array Article schema
     */
    private function get_article_schema($post, $title, $description) {
        $schema = [
            '@type' => 'Article',
            '@id' => get_permalink($post->ID) . '#article',
            'isPartOf' => [
                '@id' => get_permalink($post->ID) . '#webpage'
            ],
            'author' => $this->get_author_schema($post->post_author),
            'headline' => $title,
            'datePublished' => get_the_date('c', $post),
            'dateModified' => get_the_modified_date('c', $post),
            'commentCount' => get_comments_number($post->ID),
            'publisher' => [
                '@id' => home_url('/') . '#organization'
            ],
            'description' => $description,
            'inLanguage' => get_bloginfo('language'),
            'mainEntityOfPage' => [
                '@id' => get_permalink($post->ID) . '#webpage'
            ]
        ];
        
        // Add featured image if available
        if (has_post_thumbnail($post->ID)) {
            $schema['image'] = $this->get_image_schema($post->ID);
        }
        
        return $schema;
    }
    
    /**
     * Get Author schema
     *
     * @param int $author_id Author ID
     * @return array Author schema
     */
    private function get_author_schema($author_id) {
        return [
            '@type' => 'Person',
            '@id' => get_author_posts_url($author_id) . '#person',
            'name' => get_the_author_meta('display_name', $author_id),
            'url' => get_author_posts_url($author_id)
        ];
    }
    
    /**
     * Get Image schema
     *
     * @param int $post_id Post ID
     * @return array Image schema
     */
    private function get_image_schema($post_id) {
        $image_id = get_post_thumbnail_id($post_id);
        
        if (!$image_id) {
            return [];
        }
        
        $image_url = wp_get_attachment_image_url($image_id, 'full');
        
        if (!$image_url) {
            return [];
        }
        
        $schema = [
            '@type' => 'ImageObject',
            '@id' => get_permalink($post_id) . '#primaryimage',
            'url' => $image_url,
            'contentUrl' => $image_url
        ];
        
        // Add image dimensions if available
        $metadata = wp_get_attachment_metadata($image_id);
        
        if (isset($metadata['width']) && isset($metadata['height'])) {
            $schema['width'] = $metadata['width'];
            $schema['height'] = $metadata['height'];
        }
        
        return $schema;
    }
    
    /**
     * Get default description from post content
     *
     * @param \WP_Post $post Post object
     * @return string Default description
     */
    private function get_default_description($post) {
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
