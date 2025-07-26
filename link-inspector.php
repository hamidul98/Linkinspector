<?php
/**
 * Plugin Name: Link Inspector Dashboard
 * Description: Comprehensive link analysis and management tool for WordPress websites
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: link-inspector
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('LINK_INSPECTOR_VERSION', '1.0.0');
define('LINK_INSPECTOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LINK_INSPECTOR_PLUGIN_URL', plugin_dir_url(__FILE__));

// Main plugin class
class LinkInspectorPlugin {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        
        // Hook activation
        register_activation_hook(__FILE__, array($this, 'create_tables'));
    }
    
    public function init() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // AJAX handlers
        add_action('wp_ajax_link_inspector_sync', array($this, 'ajax_sync_links'));
        add_action('wp_ajax_link_inspector_export_csv', array($this, 'ajax_export_csv'));
        add_action('wp_ajax_link_inspector_get_stats', array($this, 'ajax_get_stats'));
    }
    
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Internal links table
        $table_internal = $wpdb->prefix . 'link_inspector_internal';
        $sql_internal = "CREATE TABLE $table_internal (
            id int(11) NOT NULL AUTO_INCREMENT,
            link_url text NOT NULL,
            used_in_pages text,
            anchor_text text,
            first_found datetime DEFAULT CURRENT_TIMESTAMP,
            status varchar(20) DEFAULT 'active',
            PRIMARY KEY (id),
            KEY link_url_idx (link_url(191))
        ) $charset_collate;";
        
        // External links table
        $table_external = $wpdb->prefix . 'link_inspector_external';
        $sql_external = "CREATE TABLE $table_external (
            id int(11) NOT NULL AUTO_INCREMENT,
            external_url text NOT NULL,
            used_in_pages text,
            anchor_text text,
            status_code varchar(10),
            status varchar(20) DEFAULT 'active',
            last_checked datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY external_url_idx (external_url(191))
        ) $charset_collate;";
        
        // Broken links table
        $table_broken = $wpdb->prefix . 'link_inspector_broken';
        $sql_broken = "CREATE TABLE $table_broken (
            id int(11) NOT NULL AUTO_INCREMENT,
            broken_url text NOT NULL,
            error_type varchar(100),
            found_in_pages text,
            last_checked datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY broken_url_idx (broken_url(191))
        ) $charset_collate;";
        
        // Orphan pages table
        $table_orphan = $wpdb->prefix . 'link_inspector_orphan';
        $sql_orphan = "CREATE TABLE $table_orphan (
            id int(11) NOT NULL AUTO_INCREMENT,
            page_title text,
            url text,
            post_type varchar(50),
            published_on datetime,
            PRIMARY KEY (id),
            KEY url_idx (url(191))
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_internal);
        dbDelta($sql_external);
        dbDelta($sql_broken);
        dbDelta($sql_orphan);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Link Inspector',
            'Link Inspector',
            'manage_options',
            'link-inspector',
            array($this, 'dashboard_page'),
            'dashicons-admin-links',
            30
        );
    }
    
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'toplevel_page_link-inspector') {
            return;
        }
        
        wp_enqueue_style('link-inspector-admin', LINK_INSPECTOR_PLUGIN_URL . 'assets/admin.css', array(), LINK_INSPECTOR_VERSION);
        wp_enqueue_script('link-inspector-admin', LINK_INSPECTOR_PLUGIN_URL . 'assets/admin.js', array('jquery'), LINK_INSPECTOR_VERSION, true);
        
        wp_localize_script('link-inspector-admin', 'linkInspectorAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('link_inspector_nonce')
        ));
    }
    
    public function dashboard_page() {
        include LINK_INSPECTOR_PLUGIN_DIR . 'templates/dashboard.php';
    }
    
    public function ajax_sync_links() {
        check_ajax_referer('link_inspector_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        // Clear existing data
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}link_inspector_internal");
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}link_inspector_external");
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}link_inspector_broken");
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}link_inspector_orphan");
        
        // Scan all published posts and pages
        $this->scan_content();
        
        wp_send_json_success('Sync completed successfully');
    }
    
    private function scan_content() {
        global $wpdb;
        
        // Get all published posts and pages
        $args = array(
            'post_type' => array('post', 'page'),
            'post_status' => 'publish',
            'numberposts' => -1,
            'suppress_filters' => false
        );
        
        $posts = get_posts($args);
        
        $internal_links = array();
        $external_links = array();
        $all_urls = array();
        
        foreach ($posts as $post) {
            $content = $post->post_content;
            $post_url = get_permalink($post->ID);
            $all_urls[$post->ID] = $post_url;
            
            // Extract links from content
            preg_match_all('/<a\s+[^>]*href\s*=\s*["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $content, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $link_url = $match[1];
                $anchor_text = trim(strip_tags($match[2]));
                
                if (empty($anchor_text)) {
                    $anchor_text = 'No anchor text';
                }
                
                // Skip empty or invalid URLs
                if (empty($link_url) || $link_url === '#' || strpos($link_url, 'javascript:') === 0 || strpos($link_url, 'mailto:') === 0) {
                    continue;
                }
                
                // Determine if internal or external
                if ($this->is_internal_link($link_url)) {
                    $internal_links[] = array(
                        'url' => $link_url,
                        'anchor' => $anchor_text,
                        'found_in' => $post->post_title,
                        'post_id' => $post->ID
                    );
                } else {
                    $external_links[] = array(
                        'url' => $link_url,
                        'anchor' => $anchor_text,
                        'found_in' => $post->post_title,
                        'post_id' => $post->ID
                    );
                }
            }
        }
        
        // Save internal links
        $this->save_internal_links($internal_links);
        
        // Save external links and check status
        $this->save_external_links($external_links);
        
        // Find orphan pages
        $this->find_orphan_pages($all_urls);
    }
    
    private function is_internal_link($url) {
        $site_url = get_site_url();
        $home_url = get_home_url();
        
        // Check if it's a relative URL or contains the site URL
        return (strpos($url, '/') === 0 && strpos($url, '//') !== 0) || 
               strpos($url, $site_url) === 0 || 
               strpos($url, $home_url) === 0;
    }
    
    private function save_internal_links($links) {
        global $wpdb;
        $table = $wpdb->prefix . 'link_inspector_internal';
        
        $grouped_links = array();
        foreach ($links as $link) {
            $url = $link['url'];
            if (!isset($grouped_links[$url])) {
                $grouped_links[$url] = array(
                    'anchors' => array(),
                    'pages' => array()
                );
            }
            $grouped_links[$url]['anchors'][] = $link['anchor'];
            $grouped_links[$url]['pages'][] = $link['found_in'];
        }
        
        foreach ($grouped_links as $url => $data) {
            $wpdb->insert($table, array(
                'link_url' => $url,
                'used_in_pages' => implode(', ', array_unique($data['pages'])),
                'anchor_text' => implode(', ', array_unique($data['anchors']))
            ));
        }
    }
    
    private function save_external_links($links) {
        global $wpdb;
        $table = $wpdb->prefix . 'link_inspector_external';
        $broken_table = $wpdb->prefix . 'link_inspector_broken';
        
        $grouped_links = array();
        foreach ($links as $link) {
            $url = $link['url'];
            if (!isset($grouped_links[$url])) {
                $grouped_links[$url] = array(
                    'anchors' => array(),
                    'pages' => array()
                );
            }
            $grouped_links[$url]['anchors'][] = $link['anchor'];
            $grouped_links[$url]['pages'][] = $link['found_in'];
        }
        
        foreach ($grouped_links as $url => $data) {
            // Check URL status
            $status_info = $this->check_url_status($url);
            
            if ($status_info['is_broken']) {
                // Save to broken links
                $wpdb->insert($broken_table, array(
                    'broken_url' => $url,
                    'error_type' => $status_info['error'],
                    'found_in_pages' => implode(', ', array_unique($data['pages']))
                ));
            } else {
                // Save to external links
                $wpdb->insert($table, array(
                    'external_url' => $url,
                    'used_in_pages' => implode(', ', array_unique($data['pages'])),
                    'anchor_text' => implode(', ', array_unique($data['anchors'])),
                    'status_code' => $status_info['code'],
                    'status' => 'active'
                ));
            }
        }
    }
    
    private function check_url_status($url) {
        // Add timeout and user agent
        $args = array(
            'timeout' => 15,
            'user-agent' => 'WordPress Link Inspector',
            'sslverify' => false,
            'headers' => array(
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
            )
        );
        
        $response = wp_remote_head($url, $args);
        
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            
            if (strpos($error_message, 'cURL error 6') !== false || strpos($error_message, 'name or service not known') !== false) {
                return array(
                    'is_broken' => true,
                    'error' => 'DNS Error',
                    'code' => '0'
                );
            }
            
            return array(
                'is_broken' => true,
                'error' => 'Connection Error',
                'code' => '0'
            );
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code >= 400) {
            $error_type = $status_code . ' Not Found';
            if ($status_code == 404) {
                $error_type = '404 Not Found';
            } elseif ($status_code == 410) {
                $error_type = '410 Gone';
            } elseif ($status_code >= 500) {
                $error_type = $status_code . ' Server Error';
            }
            
            return array(
                'is_broken' => true,
                'error' => $error_type,
                'code' => $status_code
            );
        }
        
        return array(
            'is_broken' => false,
            'error' => '',
            'code' => $status_code
        );
    }
    
    private function find_orphan_pages($linked_urls) {
        global $wpdb;
        $table = $wpdb->prefix . 'link_inspector_orphan';
        
        $args = array(
            'post_type' => array('post', 'page'),
            'post_status' => 'publish',
            'numberposts' => -1,
            'suppress_filters' => false
        );
        
        $all_posts = get_posts($args);
        
        foreach ($all_posts as $post) {
            $post_url = get_permalink($post->ID);
            $relative_url = str_replace(array(get_site_url(), get_home_url()), '', $post_url);
            
            // Check if this URL is linked from anywhere
            $is_linked = false;
            
            // Check internal links table for references to this page
            $internal_links = $wpdb->get_results("SELECT link_url FROM {$wpdb->prefix}link_inspector_internal");
            
            foreach ($internal_links as $link) {
                $link_url = $link->link_url;
                
                // Normalize URLs for comparison
                $normalized_link = str_replace(array(get_site_url(), get_home_url()), '', $link_url);
                
                if ($normalized_link === $relative_url || 
                    $link_url === $post_url || 
                    strpos($link_url, $post->post_name) !== false) {
                    $is_linked = true;
                    break;
                }
            }
            
            // Skip homepage and main pages that are typically in menus
            if ($post->ID == get_option('page_on_front') || 
                $post->ID == get_option('page_for_posts') ||
                $relative_url === '/' || 
                empty($relative_url)) {
                $is_linked = true;
            }
            
            if (!$is_linked) {
                $wpdb->insert($table, array(
                    'page_title' => $post->post_title,
                    'url' => $relative_url,
                    'post_type' => ucfirst($post->post_type),
                    'published_on' => $post->post_date
                ));
            }
        }
    }
    
    public function ajax_export_csv() {
        check_ajax_referer('link_inspector_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        // Generate CSV data for anchor text analysis
        global $wpdb;
        $internal_table = $wpdb->prefix . 'link_inspector_internal';
        
        $results = $wpdb->get_results("SELECT anchor_text, used_in_pages FROM $internal_table");
        
        $csv_data = array();
        $csv_data[] = array('Anchor Text', 'Pages Used In', 'Total Frequency');
        
        $anchor_stats = array();
        foreach ($results as $result) {
            $anchors = explode(', ', $result->anchor_text);
            $pages = explode(', ', $result->used_in_pages);
            
            foreach ($anchors as $anchor) {
                if (!isset($anchor_stats[$anchor])) {
                    $anchor_stats[$anchor] = array(
                        'pages' => array(),
                        'frequency' => 0
                    );
                }
                $anchor_stats[$anchor]['pages'] = array_merge($anchor_stats[$anchor]['pages'], $pages);
                $anchor_stats[$anchor]['frequency']++;
            }
        }
        
        foreach ($anchor_stats as $anchor => $stats) {
            $csv_data[] = array(
                $anchor,
                count(array_unique($stats['pages'])),
                $stats['frequency']
            );
        }
        
        wp_send_json_success(array('csv_data' => $csv_data));
    }
    
    public function ajax_get_stats() {
        check_ajax_referer('link_inspector_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $stats = $this->get_stats();
        wp_send_json_success($stats);
    }
    
    public function get_stats() {
        global $wpdb;
        
        // Ensure tables exist
        $this->create_tables();
        
        $internal_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}link_inspector_internal");
        $external_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}link_inspector_external");
        $broken_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}link_inspector_broken");
        $orphan_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}link_inspector_orphan");
        
        return array(
            'internal' => $internal_count,
            'external' => $external_count,
            'broken' => $broken_count,
            'orphan' => $orphan_count
        );
    }
    
    public function get_internal_links() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}link_inspector_internal ORDER BY first_found DESC");
    }
    
    public function get_external_links() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}link_inspector_external ORDER BY last_checked DESC");
    }
    
    public function get_broken_links() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}link_inspector_broken ORDER BY last_checked DESC");
    }
    
    public function get_orphan_pages() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}link_inspector_orphan ORDER BY published_on DESC");
    }
    
    public function get_anchor_text_analysis() {
        global $wpdb;
        $results = $wpdb->get_results("SELECT anchor_text, used_in_pages FROM {$wpdb->prefix}link_inspector_internal");
        
        $anchor_stats = array();
        foreach ($results as $result) {
            $anchors = explode(', ', $result->anchor_text);
            $pages = explode(', ', $result->used_in_pages);
            
            foreach ($anchors as $anchor) {
                if (empty(trim($anchor))) continue;
                
                if (!isset($anchor_stats[$anchor])) {
                    $anchor_stats[$anchor] = array(
                        'pages' => array(),
                        'frequency' => 0
                    );
                }
                $anchor_stats[$anchor]['pages'] = array_merge($anchor_stats[$anchor]['pages'], $pages);
                $anchor_stats[$anchor]['frequency']++;
            }
        }
        
        // Sort by frequency
        uasort($anchor_stats, function($a, $b) {
            return $b['frequency'] - $a['frequency'];
        });
        
        return $anchor_stats;
    }
    
    public function get_low_linked_pages_count() {
        global $wpdb;
        
        // Get all published posts and pages
        $args = array(
            'post_type' => array('post', 'page'),
            'post_status' => 'publish',
            'numberposts' => -1,
            'suppress_filters' => false
        );
        
        $posts = get_posts($args);
        $low_linked_count = 0;
        
        foreach ($posts as $post) {
            $post_url = get_permalink($post->ID);
            $relative_url = str_replace(array(get_site_url(), get_home_url()), '', $post_url);
            
            // Count internal links pointing to this page
            $link_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}link_inspector_internal WHERE link_url LIKE %s OR link_url LIKE %s",
                '%' . $relative_url . '%',
                '%' . $post->post_name . '%'
            ));
            
            if ($link_count < 2) {
                $low_linked_count++;
            }
        }
        
        return $low_linked_count;
    }
    
    public function get_content_inventory() {
        global $wpdb;
        
        $args = array(
            'post_type' => array('post', 'page'),
            'post_status' => 'publish',
            'numberposts' => -1,
            'suppress_filters' => false
        );
        
        $posts = get_posts($args);
        $inventory = array();
        
        // Get orphan pages for comparison
        $orphan_urls = $wpdb->get_col("SELECT url FROM {$wpdb->prefix}link_inspector_orphan");
        
        foreach ($posts as $post) {
            $post_url = get_permalink($post->ID);
            $relative_url = str_replace(array(get_site_url(), get_home_url()), '', $post_url);
            
            // Count internal links pointing to this page
            $internal_links = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}link_inspector_internal WHERE link_url LIKE %s OR link_url LIKE %s",
                '%' . $relative_url . '%',
                '%' . $post->post_name . '%'
            ));
            
            // Calculate word count
            $word_count = str_word_count(strip_tags($post->post_content));
            
            // Check if orphan
            $is_orphan = in_array($relative_url, $orphan_urls);
            
            $inventory[] = (object) array(
                'ID' => $post->ID,
                'post_title' => $post->post_title,
                'post_type' => $post->post_type,
                'post_date' => $post->post_date,
                'post_modified' => $post->post_modified,
                'url' => $post_url,
                'url_display' => $relative_url,
                'word_count' => $word_count,
                'internal_links' => $internal_links,
                'is_orphan' => $is_orphan
            );
        }
        
        return $inventory;
    }
    
    public function get_redirects() {
        global $wpdb;
        
        // Create redirects table if it doesn't exist
        $table_redirects = $wpdb->prefix . 'link_inspector_redirects';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_redirects (
            id int(11) NOT NULL AUTO_INCREMENT,
            from_url text NOT NULL,
            to_url text NOT NULL,
            redirect_type varchar(10) DEFAULT '301',
            created_on datetime DEFAULT CURRENT_TIMESTAMP,
            hit_count int(11) DEFAULT 0,
            status varchar(20) DEFAULT 'active',
            PRIMARY KEY (id),
            KEY from_url_idx (from_url(191))
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        return $wpdb->get_results("SELECT * FROM $table_redirects ORDER BY created_on DESC");
    }
}

// Initialize the plugin
new LinkInspectorPlugin();