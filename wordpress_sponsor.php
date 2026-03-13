<?php
/**
 * Plugin Name: WordPress Sponsor
 * Description: Sponsor management with logo display widgets.
 * Version: @VERSION@
 * Author: Henrik Hansen
 * Text Domain: h2-wordpress-sponsor
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WP_SPONSOR_PATH', plugin_dir_path(__FILE__));
define('WP_SPONSOR_URL', plugin_dir_url(__FILE__));

require_once WP_SPONSOR_PATH . 'includes/class-sponsor-post-type.php';
require_once WP_SPONSOR_PATH . 'includes/class-sponsor-widget-grid.php';
require_once WP_SPONSOR_PATH . 'includes/class-sponsor-widget-scroll.php';

/**
 * Returns published sponsors ordered by priority (or randomly).
 *
 * @param bool $random Randomise order.
 * @return WP_Post[]
 */
function wp_sponsor_get_sponsors(bool $random = false): array
{
    $args = [
        'post_type' => 'sponsor',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'no_found_rows' => true,
    ];

    if ($random) {
        $args['orderby'] = 'rand';
    } else {
        $args['meta_key'] = '_sponsor_priority';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'ASC';
    }

    return get_posts($args);
}

add_action(
    'wp_enqueue_scripts',
    function () {
        wp_enqueue_style('wp-sponsor', WP_SPONSOR_URL . 'assets/css/sponsors.css', [], '1.0.0');
        wp_enqueue_script('wp-sponsor-scroll', WP_SPONSOR_URL . 'assets/js/sponsors-scroll.js', [], '1.0.0', true);
    }
);

add_action(
    'widgets_init',
    function () {
        register_widget('Sponsor_Widget_Grid');
        register_widget('Sponsor_Widget_Scroll');
    }
);
