<?php

if (!defined('ABSPATH')) {
    exit;
}

class Sponsor_Post_Type
{

    public static function init(): void
    {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('init', [__CLASS__, 'register_post_status']);
        add_action('wp_insert_post', [__CLASS__, 'set_default_priority'], 10, 2);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_sponsor', [__CLASS__, 'save_meta']);
        add_action('admin_footer-post.php', [__CLASS__, 'append_post_status_list']);
        add_action('admin_footer-post-new.php', [__CLASS__, 'append_post_status_list']);
        add_filter('display_post_states', [__CLASS__, 'display_post_states'], 10, 2);
        add_filter('manage_sponsor_posts_columns', [__CLASS__, 'admin_columns']);
        add_action('manage_sponsor_posts_custom_column', [__CLASS__, 'admin_column_content'], 10, 2);
        add_filter('manage_edit-sponsor_sortable_columns', [__CLASS__, 'sortable_columns']);
        add_action('admin_head-post.php', [__CLASS__, 'hide_add_new_button']);
    }

    public static function hide_add_new_button(): void
    {
        global $post;
        if (!$post || $post->post_type !== 'sponsor') {
            return;
        }
        echo '<style>.page-title-action { display: none !important; }</style>';
    }

    public static function register_post_type(): void
    {
        register_post_type(
            'sponsor',
            [
                'labels' => [
                    'name' => __('Sponsors', 'wordpress-sponsor'),
                    'singular_name' => __('Sponsor', 'wordpress-sponsor'),
                    'add_new_item' => __('Add New Sponsor', 'wordpress-sponsor'),
                    'edit_item' => __('Edit Sponsor', 'wordpress-sponsor'),
                    'not_found' => __('No sponsors found.', 'wordpress-sponsor'),
                ],
                'public' => false,
                'show_ui' => true,
                'show_in_menu' => true,
                'menu_icon' => 'dashicons-star-filled',
                'supports' => ['title', 'thumbnail'],
                'has_archive' => false,
            ]
        );
    }

    public static function register_post_status(): void
    {
        register_post_status(
            'inactive',
            [
                'label' => _x('Inactive', 'post status', 'wordpress-sponsor'),
                'public' => false,
                'exclude_from_search' => true,
                'show_in_admin_all_list' => true,
                'show_in_admin_status_list' => true,
                'label_count' => _n_noop(
                    'Inactive <span class="count">(%s)</span>',
                    'Inactive <span class="count">(%s)</span>',
                    'wordpress-sponsor'
                ),
            ]
        );
    }

    /**
     * Ensure every new sponsor gets a default priority so ordering always works.
     */
    public static function set_default_priority(int $post_id, WP_Post $post): void
    {
        if ($post->post_type !== 'sponsor') {
            return;
        }
        if ('' === get_post_meta($post_id, '_sponsor_priority', true)) {
            update_post_meta($post_id, '_sponsor_priority', 10);
        }
    }

    public static function add_meta_boxes(): void
    {
        add_meta_box(
            'sponsor_details',
            __('Sponsor Details', 'wordpress-sponsor'),
            [__CLASS__, 'render_meta_box'],
            'sponsor',
            'normal',
            'high'
        );
    }

    public static function render_meta_box(WP_Post $post): void
    {
        wp_nonce_field('sponsor_save_meta', 'sponsor_meta_nonce');
        $link = get_post_meta($post->ID, '_sponsor_link', true); // input id/name uses prefix to avoid conflicts
        $priority = get_post_meta($post->ID, '_sponsor_priority', true);
        if ('' === $priority) {
            $priority = 10;
        }
        ?>
        <p>
            <label for="wps_link"><strong><?php esc_html_e('Link URL', 'wordpress-sponsor'); ?></strong></label><br />
            <input type="text" id="wps_link" name="wps_link"
                   value="<?php echo esc_attr($link); ?>"
                   style="width:100%;display:block!important;box-sizing:border-box;background:#fff;color:#3c434a;border:1px solid #8c8f94;padding:4px 8px;min-height:30px;"
                   placeholder="https://example.com" />
        </p>
        <p>
            <label for="sponsor_priority"><strong><?php esc_html_e('Priority', 'wordpress-sponsor'); ?></strong></label><br />
            <input type="number" id="sponsor_priority" name="sponsor_priority"
                   value="<?php echo esc_attr($priority); ?>"
                   min="1" max="999" style="width:80px" />
            <span class="description"><?php esc_html_e('Lower number = higher priority (1 = first).', 'wordpress-sponsor'); ?></span>
        </p>
        <?php
    }

    public static function save_meta(int $post_id): void
    {
        if (
            !isset($_POST['sponsor_meta_nonce'])
            || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sponsor_meta_nonce'])), 'sponsor_save_meta')
        ) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['wps_link'])) {
            update_post_meta($post_id, '_sponsor_link', esc_url_raw(wp_unslash($_POST['wps_link'])));
        }

        if (isset($_POST['sponsor_priority'])) {
            update_post_meta($post_id, '_sponsor_priority', absint($_POST['sponsor_priority']));
        }
    }

    /** Inject the "Inactive" option into the post status dropdown. */
    public static function append_post_status_list(): void
    {
        global $post;
        if (!$post || $post->post_type !== 'sponsor') {
            return;
        }
        $selected = selected($post->post_status, 'inactive', false);
        $inactive_label = esc_js(__('Inactive', 'wordpress-sponsor'));
        ?>
        <script>
            jQuery(function ($) {
                $('#post_status').append(
                    '<option value="inactive" <?php echo esc_attr($selected); ?>><?php echo esc_html($inactive_label); ?></option>'
                );
                <?php if ('inactive' === $post->post_status): ?>
                    $('#post-status-display').text('<?php echo esc_js(__('Inactive', 'wordpress-sponsor')); ?>');
                <?php endif; ?>
            });
        </script>
        <?php
    }

    public static function display_post_states(array $states, WP_Post $post): array
    {
        if ($post->post_type === 'sponsor' && $post->post_status === 'inactive') {
            $states['inactive'] = __('Inactive', 'wordpress-sponsor');
        }
        return $states;
    }

    public static function admin_columns(array $columns): array
    {
        $new = [];
        foreach ($columns as $key => $label) {
            $new[$key] = $label;
            if ($key === 'title') {
                $new['sponsor_logo'] = __('Logo', 'wordpress-sponsor');
                $new['sponsor_priority'] = __('Priority', 'wordpress-sponsor');
                $new['sponsor_link'] = __('Link', 'wordpress-sponsor');
            }
        }
        return $new;
    }

    public static function admin_column_content(string $column, int $post_id): void
    {
        switch ($column) {
            case 'sponsor_logo':
                $thumb = get_the_post_thumbnail($post_id, [60, 60]);
                echo $thumb ?: '&mdash;';
                break;

            case 'sponsor_priority':
                $p = get_post_meta($post_id, '_sponsor_priority', true);
                echo esc_html('' !== $p ? $p : '&mdash;');
                break;

            case 'sponsor_link':
                $link = get_post_meta($post_id, '_sponsor_link', true);
                if ($link) {
                    echo '<a href="' . esc_url($link) . '" target="_blank" rel="noopener noreferrer">'
                        . esc_html($link)
                        . '</a>';
                } else {
                    echo '&mdash;';
                }
                break;
        }
    }

    public static function sortable_columns(array $columns): array
    {
        $columns['sponsor_priority'] = 'sponsor_priority';
        return $columns;
    }
}

Sponsor_Post_Type::init();
