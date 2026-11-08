# Legacy WordPress theme (reference only)

This folder holds the WordPress theme code (GeneratePress child theme
includes and must-use plugins) from the site this platform was migrated
from. **None of it is loaded by the Laravel application**: the files call
WordPress APIs such as `add_action()`, `add_filter()` and `get_field()`
that don't exist here.

It is kept because it documents behaviour the Laravel code reproduces:

| Legacy file                                   | Laravel replacement                                    |
| --------------------------------------------- | ------------------------------------------------------ |
| `inc/custom-header-footer.php`                | `resources/views/layouts/app.blade.php`, `MenuService` |
| `inc/custom-*-template.php`                   | `resources/views/site/*.blade.php`                     |
| `inc/customizer.php`, `inc/typography.php`    | `public/css/*`, theme options in `wp_options`          |
| `inc/polylang-*.php`, `mu-plugins/cal-*`      | `PolylangService`, `UiTranslationService`              |
| `mu-plugins/culina-slug-redirects.php`        | redirect routes in `routes/web.php`                    |
| ACF field groups referenced by the templates  | `AcfFieldService`, `LegacyAcfSyncService`              |

Rules:

- Don't `require` or autoload anything from here. `tests/Unit/ArchitectureTest.php`
  fails if application code references this folder or calls WordPress hooks.
- When porting behaviour, write it in `app/` with tests, then note it in the
  table above.
- Once nothing here is needed as reference, delete the folder.
