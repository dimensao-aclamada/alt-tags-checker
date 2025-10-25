<?php
/**
 * Plugin Name: Alt Tag Checker
 * Description: Finds pages with images missing alt tags and easily reviews them in the admin area.
 * Version: 1.0
 * Author: Jokesigner Plugins
 * Author URI: https://jokesigner.com
 * License: Apache 2.0
 */

if (!defined('ABSPATH')) exit;

class AltTagChecker {
    private $option_name = 'atc_last_scan_results';
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Alt Tag Checker',
            'Alt Tag Checker',
            'manage_options',
            'alt-tag-checker',
            array($this, 'display_admin_page'),
            'dashicons-images-alt2',
            30
        );
    }
    
    public function enqueue_styles($hook) {
        if ($hook !== 'toplevel_page_alt-tag-checker') return;
        
        wp_enqueue_style('alt-tag-checker-style', false);
        wp_add_inline_style('alt-tag-checker-style', '
            .atc-container { max-width: 1200px; margin: 20px; }
            .atc-header { background: #fff; padding: 20px; margin-bottom: 20px; border-left: 4px solid #2271b1; }
            .atc-settings { background: #fff; padding: 20px; margin-bottom: 20px; }
            .atc-settings input[type="number"] { width: 80px; }
            .atc-settings .button { margin-right: 10px; }
            .atc-scan-info { background: #f0f0f1; padding: 10px 15px; margin-bottom: 20px; border-radius: 4px; font-size: 13px; }
            .atc-results { background: #fff; padding: 20px; }
            .atc-page-item { border-bottom: 1px solid #ddd; padding: 20px 0; }
            .atc-page-item:last-child { border-bottom: none; }
            .atc-page-title { font-size: 18px; font-weight: 600; margin-bottom: 10px; }
            .atc-page-link { color: #2271b1; text-decoration: none; }
            .atc-page-link:hover { text-decoration: underline; }
            .atc-images { display: flex; flex-wrap: wrap; gap: 15px; margin-top: 15px; }
            .atc-image-item { position: relative; display: flex; flex-direction: column; }
            .atc-thumbnail { width: 150px; height: 150px; object-fit: cover; border: 2px solid #ddd; }
            .atc-thumbnail:hover { border-color: #2271b1; }
            .atc-gallery-link { display: inline-block; margin-top: 5px; font-size: 12px; color: #2271b1; text-decoration: none; }
            .atc-gallery-link:hover { text-decoration: underline; }
            .atc-no-results { text-align: center; padding: 40px; color: #666; }
            .atc-loading { text-align: center; padding: 40px; }
        ');
    }
    
    public function display_admin_page() {
        if (!current_user_can('manage_options')) return;
        
        $depth = isset($_POST['crawl_depth']) ? intval($_POST['crawl_depth']) : 2;
        $scan_started = isset($_POST['start_scan']);
        $clear_scan = isset($_POST['clear_scan']);
        
        // Clear scan if requested
        if ($clear_scan) {
            delete_option($this->option_name);
            echo '<div class="notice notice-success is-dismissible"><p>Scan results cleared.</p></div>';
        }
        
        // Perform new scan if requested
        if ($scan_started) {
            $results = $this->crawl_site($depth);
            $scan_data = array(
                'results' => $results,
                'depth' => $depth,
                'timestamp' => current_time('mysql'),
                'pages_scanned' => count($results)
            );
            update_option($this->option_name, $scan_data);
        }
        
        // Get stored scan results
        $scan_data = get_option($this->option_name, null);
        
        ?>
        <div class="atc-container">
            <div class="atc-header">
                <h1>Alt Tag Checker</h1>
                <p>Find pages with images missing alt attributes</p>
            </div>
            
            <div class="atc-settings">
                <form method="post">
                    <label for="crawl_depth">Crawl Depth:</label>
                    <input type="number" id="crawl_depth" name="crawl_depth" value="<?php echo esc_attr($depth); ?>" min="1" max="5">
                    <button type="submit" name="start_scan" class="button button-primary">New Scan</button>
                    <?php if ($scan_data): ?>
                        <button type="submit" name="clear_scan" class="button" onclick="return confirm('Are you sure you want to clear the scan results?')">Clear Scan</button>
                    <?php endif; ?>
                </form>
            </div>
            
            <?php if ($scan_data): ?>
                <div class="atc-scan-info">
                    <strong>Last scan:</strong> <?php echo esc_html($scan_data['timestamp']); ?> 
                    | <strong>Depth:</strong> <?php echo esc_html($scan_data['depth']); ?>
                    | <strong>Pages with issues:</strong> <?php echo esc_html($scan_data['pages_scanned']); ?>
                </div>
                
                <div class="atc-results">
                    <?php $this->display_results($scan_data['results']); ?>
                </div>
            <?php else: ?>
                <div class="atc-results">
                    <div class="atc-no-results">No scan results available. Click "New Scan" to start.</div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    private function display_results($results) {
        if (empty($results)) {
            echo '<div class="atc-no-results">✓ No images with missing alt tags found!</div>';
            return;
        }
        
        echo '<h2>Found ' . count($results) . ' page(s) with missing alt tags:</h2>';
        
        foreach ($results as $page) {
            $this->display_page_result($page);
        }
    }
    
    private function display_page_result($page) {
        ?>
        <div class="atc-page-item">
            <div class="atc-page-title">
                <a href="<?php echo esc_url($page['url']); ?>" target="_blank" class="atc-page-link">
                    <?php echo esc_html($page['title']); ?>
                </a>
            </div>
            <div class="atc-images">
                <?php foreach ($page['images'] as $img): ?>
                    <div class="atc-image-item">
                        <?php if ($img['attachment_id']): ?>
                            <a href="<?php echo admin_url('post.php?post=' . $img['attachment_id'] . '&action=edit'); ?>" 
                               target="_blank">
                                <img src="<?php echo esc_url($img['src']); ?>" class="atc-thumbnail" alt="Missing alt tag">
                            </a>
                            <a href="<?php echo admin_url('post.php?post=' . $img['attachment_id'] . '&action=edit'); ?>" 
                               class="atc-gallery-link" target="_blank">
                                See in Media Library →
                            </a>
                        <?php else: ?>
                            <img src="<?php echo esc_url($img['src']); ?>" class="atc-thumbnail" alt="Missing alt tag">
                            <span class="atc-gallery-link" style="color: #999;">Not in Media Library</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
    
    private function crawl_site($max_depth) {
        $results = array();
        $visited = array();
        $to_crawl = array(array('url' => home_url('/'), 'depth' => 0));
        
        while (!empty($to_crawl)) {
            $current = array_shift($to_crawl);
            $url = $current['url'];
            $depth = $current['depth'];
            
            // Normalize URL to avoid duplicates
            $normalized_url = $this->normalize_url_for_comparison($url);
            
            if (isset($visited[$normalized_url]) || $depth > $max_depth) continue;
            $visited[$normalized_url] = true;
            
            $page_data = $this->check_page($url);
            
            if (!empty($page_data['images'])) {
                $results[] = $page_data;
            }
            
            if ($depth < $max_depth) {
                $links = $this->get_page_links($url);
                foreach ($links as $link) {
                    $normalized_link = $this->normalize_url_for_comparison($link);
                    if (!isset($visited[$normalized_link])) {
                        $to_crawl[] = array('url' => $link, 'depth' => $depth + 1);
                    }
                }
            }
        }
        
        return $results;
    }
    
    private function check_page($url) {
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) return array();
        
        $html = wp_remote_retrieve_body($response);
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        
        $images = $dom->getElementsByTagName('img');
        $missing_alt = array();
        
        foreach ($images as $img) {
            $alt = $img->getAttribute('alt');
            if (empty($alt)) {
                $src = $img->getAttribute('src');
                $attachment_id = $this->get_attachment_id($src);
                
                $missing_alt[] = array(
                    'src' => $src,
                    'attachment_id' => $attachment_id
                );
            }
        }
        
        $title = '';
        $title_tags = $dom->getElementsByTagName('title');
        if ($title_tags->length > 0) {
            $title = $title_tags->item(0)->textContent;
        }
        
        return array(
            'url' => $url,
            'title' => $title ?: $url,
            'images' => $missing_alt
        );
    }
    
    private function get_page_links($url) {
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) return array();
        
        $html = wp_remote_retrieve_body($response);
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        
        $links = array();
        $anchors = $dom->getElementsByTagName('a');
        $home_url = home_url();
        
        foreach ($anchors as $anchor) {
            $href = $anchor->getAttribute('href');
            $full_url = $this->normalize_url($href, $url);
            
            if (strpos($full_url, $home_url) === 0 && !in_array($full_url, $links)) {
                $links[] = $full_url;
            }
        }
        
        return $links;
    }
    
    private function normalize_url($url, $base) {
        if (empty($url)) return '';
        if (strpos($url, 'http') === 0) return $url;
        if ($url[0] === '/') return home_url($url);
        
        return trailingslashit($base) . $url;
    }
    
    private function normalize_url_for_comparison($url) {
        // Parse the URL
        $parsed = parse_url($url);
        
        if (!$parsed) return $url;
        
        // Remove fragment (#)
        unset($parsed['fragment']);
        
        // Remove query string (?)
        unset($parsed['query']);
        
        // Normalize path: remove trailing slash for comparison
        if (isset($parsed['path'])) {
            $parsed['path'] = rtrim($parsed['path'], '/');
            // Treat empty path as /
            if (empty($parsed['path'])) {
                $parsed['path'] = '/';
            }
        }
        
        // Rebuild URL
        $normalized = '';
        if (isset($parsed['scheme'])) $normalized .= $parsed['scheme'] . '://';
        if (isset($parsed['host'])) $normalized .= $parsed['host'];
        if (isset($parsed['port'])) $normalized .= ':' . $parsed['port'];
        if (isset($parsed['path'])) $normalized .= $parsed['path'];
        
        return $normalized;
    }
    
    private function get_attachment_id($url) {
        global $wpdb;
        
        // First try exact match
        $attachment = $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM $wpdb->posts WHERE guid='%s';", $url
        ));
        
        if (!empty($attachment)) {
            return $attachment[0];
        }
        
        // If no exact match, try removing size suffix (e.g., -300x100)
        $url_without_size = preg_replace('/-\d+x\d+(\.[^.]+)$/', '$1', $url);
        
        if ($url_without_size !== $url) {
            $attachment = $wpdb->get_col($wpdb->prepare(
                "SELECT ID FROM $wpdb->posts WHERE guid='%s';", $url_without_size
            ));
            
            if (!empty($attachment)) {
                return $attachment[0];
            }
        }
        
        // Try using WordPress function as fallback
        $attachment_id = attachment_url_to_postid($url);
        if ($attachment_id) {
            return $attachment_id;
        }
        
        // Try searching by filename in post meta
        $filename = basename($url);
        $filename_without_size = preg_replace('/-\d+x\d+(\.[^.]+)$/', '$1', $filename);
        
        $attachment = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM $wpdb->postmeta 
            WHERE meta_key = '_wp_attached_file' 
            AND meta_value LIKE %s 
            LIMIT 1",
            '%' . $wpdb->esc_like($filename_without_size)
        ));
        
        return $attachment ?: null;
    }
}

new AltTagChecker();
