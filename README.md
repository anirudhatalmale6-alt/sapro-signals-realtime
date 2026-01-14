# Sapro Signals Realtime - WordPress Plugin

A WordPress plugin that displays your Supabase `rt_signals` table with real-time updates.

## Features

- Real-time updates (INSERT, UPDATE, DELETE reflected instantly)
- Dark theme styling
- Sorted by Entry Date & Entry Time (latest first)
- Connection status indicator (green = connected, red = disconnected)
- Responsive table design
- Highlight animation for new/updated rows

## Installation

1. Upload the `sapro-signals-realtime` folder to `/wp-content/plugins/`
2. Activate the plugin in WordPress Admin > Plugins
3. Use the shortcode `[sapro_signals]` on any page or post

## Configuration

The plugin comes pre-configured with your Supabase credentials. To change them, edit these lines in `sapro-signals-realtime.php`:

```php
define('SAPRO_SUPABASE_URL', 'https://your-project.supabase.co');
define('SAPRO_SUPABASE_ANON_KEY', 'your-anon-key');
define('SAPRO_TABLE_NAME', 'rt_signals');
```

## Shortcode Options

You can override settings directly in the shortcode:

```
[sapro_signals url="https://other.supabase.co" key="other-key" table="other_table"]
```

## Supabase Requirements

For real-time updates to work, you must enable Realtime on your table:

1. Go to Supabase Dashboard > Database > Tables
2. Select `rt_signals` table
3. Click on "Realtime" in the table options
4. Enable "Insert", "Update", and "Delete" broadcasts

## Table Structure

The plugin automatically detects your table columns. It expects:
- `entry_date` - Used for sorting (date format)
- `entry_time` - Used for sorting (time format)
- `id` - Row identifier (hidden from display)

All other columns are displayed dynamically.

## Styling

The plugin uses a dark theme by default. To customize, you can add CSS in your theme targeting:

- `#sapro-signals-wrapper` - Main container
- `.sapro-header` - Header section
- `.sapro-table-container` - Table wrapper
- `.sapro-footer` - Footer section

## Support

For issues or questions, contact your developer.
