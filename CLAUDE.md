# TBP Core Plugin

## Structure

- `tbp-core.php` - Main plugin file (singleton bootstrap)
- `admin/` - Admin interface
  - `admin-page.php` - Modules management (tabbed)
  - `settings-page.php` - Settings page (tabbed)
  - `components/class-modules-list-table.php` - Reusable WP_List_Table component
- `inc/` - Module categories
  - `functions/` - Core functionality modules
  - `queries/` - Elementor query filters
  - `elementor-widgets/` - Elementor widgets
  - `dynamic-tags/` - Elementor dynamic tags
  - `acf-fields/` - ACF field implementations

## Module Convention

Each module has:
- `init.php` with `@module-title`, `@module-version`, `@module-description`, `@module-usage` PHPDoc tags
- Self-contained assets in `assets/` subfolder

## Commit Rules

- No "Built with Claude" or "Co-Authored-By: Claude" in commits
- Keep commit messages concise and descriptive
