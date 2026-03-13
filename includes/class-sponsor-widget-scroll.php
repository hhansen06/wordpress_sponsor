<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Widget 2 – Scroll sponsor logos from bottom to top (infinite ticker).
 *
 * Logos are duplicated in the output so the CSS animation can loop
 * seamlessly: the track animates from translateY(0) to translateY(-50%).
 * JavaScript calculates the duration based on content height × speed setting.
 */
class Sponsor_Widget_Scroll extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(
            'sponsor_widget_scroll',
            __('Sponsors: Scroll', 'wordpress-sponsor'),
            ['description' => __('Scrolls sponsor logos upward in an infinite ticker.', 'wordpress-sponsor')]
        );
    }

    public function widget($args, $instance): void
    {
        $random = !empty($instance['random']);
        $sponsors = wp_sponsor_get_sponsors($random);

        // Only show sponsors that have a logo.
        $sponsors = array_values(
            array_filter(
                $sponsors,
                fn($s) => (bool) get_the_post_thumbnail_url($s->ID, 'medium')
            )
        );

        if (empty($sponsors)) {
            return;
        }

        $height  = absint($instance['height']  ?? 300);
        $speed   = absint($instance['speed']   ?? 50);  // px/s — used by JS
        $spacing = absint($instance['spacing'] ?? 8);   // px — gap between logos

        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title']
                . esc_html(apply_filters('widget_title', $instance['title']))
                . $args['after_title'];
        }

        printf(
            '<div class="wp-sponsor-scroll" style="height:%dpx;" data-speed="%d">',
            $height,
            $speed
        );

        // Inline the spacing so each item picks it up without a separate stylesheet.
        echo '<style>.wp-sponsor-scroll-item{padding-top:' . $spacing . 'px;padding-bottom:' . $spacing . 'px;}</style>';

        echo '<div class="wp-sponsor-scroll-track">';

        // Render items twice for seamless looping.
        for ($pass = 0; $pass < 2; $pass++) {
            foreach ($sponsors as $sponsor) {
                $logo_url = get_the_post_thumbnail_url($sponsor->ID, 'medium');
                $link = get_post_meta($sponsor->ID, '_sponsor_link', true);

                echo '<div class="wp-sponsor-scroll-item">';

                if ($link) {
                    echo '<a href="' . esc_url($link) . '" target="_blank" rel="noopener noreferrer">';
                }

                echo '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr($sponsor->post_title) . '" loading="lazy" />';

                if ($link) {
                    echo '</a>';
                }

                echo '</div>';
            }
        }

        echo '</div>'; // .wp-sponsor-scroll-track
        echo '</div>'; // .wp-sponsor-scroll

        echo $args['after_widget'];
    }

    public function form($instance): void
    {
        $title   = $instance['title']   ?? '';
        $height  = $instance['height']  ?? 300;
        $speed   = $instance['speed']   ?? 50;
        $spacing = $instance['spacing'] ?? 8;
        $random  = !empty($instance['random']);
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
            <label for="<?php echo esc_attr($this->get_field_id('height')); ?>">
                <?php esc_html_e('Visible height (px):', 'wordpress-sponsor'); ?>
            </label>
            <input type="number" id="<?php echo esc_attr($this->get_field_id('height')); ?>"
                name="<?php echo esc_attr($this->get_field_name('height')); ?>" value="<?php echo esc_attr($height); ?>"
                min="50" max="2000" style="width:80px" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('speed')); ?>">
                <?php esc_html_e('Speed (px / second):', 'wordpress-sponsor'); ?>
            </label>
            <input type="number" id="<?php echo esc_attr($this->get_field_id('speed')); ?>"
                name="<?php echo esc_attr($this->get_field_name('speed')); ?>" value="<?php echo esc_attr($speed); ?>"
                min="5" max="500" style="width:80px" />
            <span class="description">
                <?php esc_html_e('Higher = faster.', 'wordpress-sponsor'); ?>
            </span>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('spacing')); ?>">
                <?php esc_html_e('Spacing between logos (px):', 'wordpress-sponsor'); ?>
            </label>
            <input type="number" id="<?php echo esc_attr($this->get_field_id('spacing')); ?>"
                name="<?php echo esc_attr($this->get_field_name('spacing')); ?>" value="<?php echo esc_attr($spacing); ?>"
                min="0" max="200" style="width:80px" />
        </p>
        <p>
            <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('random')); ?>"
                name="<?php echo esc_attr($this->get_field_name('random')); ?>" value="1" <?php checked($random); ?>
            />
            <label for="<?php echo esc_attr($this->get_field_id('random')); ?>">
                <?php esc_html_e('Random initial order', 'wordpress-sponsor'); ?>
            </label>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance): array
    {
        return [
            'title'   => sanitize_text_field($new_instance['title']),
            'height'  => absint($new_instance['height'])  ?: 300,
            'speed'   => absint($new_instance['speed'])   ?: 50,
            'spacing' => isset($new_instance['spacing']) ? absint($new_instance['spacing']) : 8,
            'random'  => !empty($new_instance['random']) ? 1 : 0,
        ];
    }
}
