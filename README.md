# WordPress Sponsor

A WordPress plugin for managing sponsors and displaying their logos in the sidebar via two flexible widgets.

## Features

- Custom post type **Sponsor** with logo (featured image), link URL, priority and status
- Custom post status **Inactive** (in addition to WordPress core statuses `publish` / `draft`)
- Admin list table with logo preview, priority (sortable) and link columns
- **Widget 1 – Sponsors Grid**: displays logos in a 3-column grid, ordered by priority; optional random order
- **Widget 2 – Sponsors Scroll**: infinite upward-scrolling ticker with configurable height and speed; optional random initial order; pauses on mouse hover

## Requirements

- WordPress 6.0 or higher
- PHP 8.0 or higher

## Installation

1. Clone or download this repository into your `wp-content/plugins/` directory:

   ```bash
   git clone https://github.com/your-org/wordpress_sponsor.git \
       wp-content/plugins/wordpress_sponsor
   ```

2. Activate the plugin in **Plugins → Installed Plugins**.

3. Add sponsors under the new **Sponsors** menu entry in the WordPress admin.

4. Add one or both widgets via **Appearance → Widgets** (or the block editor's Legacy Widget block).

## File Structure

```
wordpress_sponsor/
├── wordpress_sponsor.php                  # Main plugin file
├── includes/
│   ├── class-sponsor-post-type.php        # Custom post type, meta boxes, admin columns
│   ├── class-sponsor-widget-grid.php      # Widget 1: 3-column grid
│   └── class-sponsor-widget-scroll.php    # Widget 2: upward scroll ticker
└── assets/
    ├── css/sponsors.css                   # Frontend styles
    └── js/sponsors-scroll.js              # Scroll animation logic
```

## Managing Sponsors

| Field | Description |
|---|---|
| **Title** | Sponsor name (displayed as `alt` attribute on the logo) |
| **Logo** | Featured image – assign any image from the media library |
| **Link URL** | Optional website URL; wraps the logo in an `<a>` tag |
| **Priority** | Integer 1–999, lower = higher priority (default: 10) |
| **Status** | `Published` (active), `Draft`, or `Inactive` |

Only **published** sponsors are shown in the widgets.

## Widgets

### Widget 1 – Sponsors: Grid

Displays all active sponsor logos in a responsive 3-column grid.

| Option | Default | Description |
|---|---|---|
| Title | *(empty)* | Optional widget heading |
| Random order | off | Shuffle logos on every page load instead of ordering by priority |

### Widget 2 – Sponsors: Scroll

Scrolls all active sponsor logos continuously from bottom to top.

| Option | Default | Description |
|---|---|---|
| Title | *(empty)* | Optional widget heading |
| Visible height (px) | 300 | Height of the visible scroll area |
| Speed (px / second) | 50 | Higher value = faster scroll |
| Random initial order | off | Shuffle logos on page load |

The animation pauses automatically when a visitor hovers over the widget.

## License

[GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html)
