/**
 * WordPress Sponsor – Scroll widget behaviour
 *
 * For each .wp-sponsor-scroll container:
 *  1. Reads the desired scroll speed from data-speed (px/s).
 *  2. Calculates the CSS animation-duration from the track's actual pixel
 *     height (which is 2× the content because PHP duplicated the items).
 *  3. Pauses the animation while the user hovers over the widget.
 */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.wp-sponsor-scroll').forEach(function (container) {
        var track = container.querySelector('.wp-sponsor-scroll-track');
        if (!track) return;

        // Speed in px/s supplied by the PHP widget; fall back to 50.
        var speed = parseFloat(container.dataset.speed) || 50;

        // scrollHeight is the total height of both duplicated sets of items.
        // Dividing by 2 gives the height of one "page" we need to scroll through.
        var halfHeight = track.scrollHeight / 2;

        if (halfHeight > 0 && speed > 0) {
            track.style.animationDuration = (halfHeight / speed).toFixed(2) + 's';
        }

        // Pause on hover.
        container.addEventListener('mouseenter', function () {
            track.style.animationPlayState = 'paused';
        });

        container.addEventListener('mouseleave', function () {
            track.style.animationPlayState = 'running';
        });
    });
});
