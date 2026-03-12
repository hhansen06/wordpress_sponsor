<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Widget 1 – Display sponsor logos in a 3-column grid.
 * Order is by priority (ascending). Optionally randomised.
 */
class Sponsor_Widget_Grid extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(
            'sponsor_widget_grid',
            __('Sponsors: Grid', 'wordpress-sponsor'),
            ['description' => __('Displays sponsor logos in a 3-column grid, ordered by priority.', 'wordpress-sponsor')]
        );
    }

    public function widget($args, $instance): void
    {
        $random = !empty($instance['random']);
        $sponsors = wp_sponsor_get_sponsors($random);

        // Keep only sponsors that have a logo assigned.
        $sponsors = array_filter(
            $sponsors,
            fn($s) => (bool) get_the_post_thumbnail_url($s->ID, 'medium')
        );

        if (empty($sponsors)) {
            return;
        }

        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title']
                . esc_html(apply_filters('widget_title', $instance['title']))
                . $args['after_title'];
        }

        echo '<div class="wp-sponsor-grid">';

        foreach ($sponsors as $sponsor) {
            $logo_url = get_the_post_thumbnail_url($sponsor->ID, 'medium');
            $link = get_post_meta($sponsor->ID, '_sponsor_link', true);

            echo '<div class="wp-sponsor-grid__item">';

            if ($link) {
                echo '<a href="' . esc_url($link) . '" target="_blank" rel="noopener noreferrer">';
            }

            echo '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr($sponsor->post_title) . '" loading="lazy" />';

            if ($link) {
                echo '</a>';
            }

            echo '</div>';
        }

        echo '</div>';

        echo $args['after_widget'];
    }

    public function form($instance): void
    {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $random = !empty($instance['random']);
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_html_e('Title:', 'wordpress-sponsor'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
                value="<?php echo esc_attr($title); ?>" />
        </p>
        <p>
            <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('random')); ?>"
                name="<?php echo esc_attr($this->get_field_name('random')); ?>" value="1" <?php checked($random); ?> />
            <label for="<?php echo esc_attr($this->get_field_id('random')); ?>">
                <?php esc_html_e('Random order', 'wordpress-sponsor'); ?>
            </label>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance): array
    {
        return [
            'title' => sanitize_text_field($new_instance['title']),
            'random' => !empty($new_instance['random']) ? 1 : 0,
        ];
    }
}
