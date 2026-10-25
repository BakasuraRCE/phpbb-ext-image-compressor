# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.1] - 2026-10-23

### Security
- Fixed stored XSS in the ACP settings page: `{{ IC_STATUS_MESSAGE }}` was rendered without escaping. Two status messages embed the admin-supplied pngquant path via `sprintf`; phpBB sets Twig's `autoescape` to `false` globally, so the raw path appeared as HTML. Added `| e` filter to `IC_STATUS_MESSAGE` (and `IC_STATUS_CLASS` for consistency).

### Changed
- Replaced `shell_exec()` with `proc_open()` array form (exec form) throughout. `shell_exec` and all related PHP shell functions invoke `/bin/sh -c` internally; in hardened containers such as DHI distroless images where `/bin/sh` is absent they always fail. `proc_open()` with an array calls `execve()` directly and works without a shell.
- Renamed language keys `IC_LOG_SHELL_EXEC_DISABLED` → `IC_LOG_PROC_OPEN_DISABLED` and `IC_STATUS_SHELL_EXEC_DISABLED` → `IC_STATUS_PROC_OPEN_DISABLED` to reflect the switch to `proc_open`.

### Fixed
- ACP validation error now correctly displayed as a warning (red) instead of success (green) by passing `E_USER_WARNING` to `trigger_error()`
- `effectively_installed()` in `image_compressor_quality` migration now checks both `ic_min_quality` and `ic_max_quality` config keys
- Replaced `@unlink()` calls with explicit `file_exists()` checks before unlinking temporary files
- `clearstatcache()` now targets only the processed file path instead of clearing the full stat cache
- Compressed file is no longer used when it is not smaller than the original; original file is kept unchanged
- Log events for configuration errors now use level `admin` instead of `critical`
- Fixed `@return null` PHPDoc annotation in `core_modify_uploaded_file()` to `@return void`
- Removed inline `<style>` block from ACP template; styles moved to `adm/style/acp_image_compressor.css`
- Added missing `| e` escape filter to `IC_STATUS_CLASS` in ACP template (see Security above)
- Corrected misleading description in `IC_ACP_QUALITY_HINT` language key
- Corrected misleading error message in `IC_LOG_COMPRESS_FAILED` language key
- Removed trailing exclamation mark from `IC_ACP_SETTING_SAVED` language key for style consistency
- Switched from `$phpbb_container->get('config')` to `global $config` in ACP module for consistency with other service declarations
- Updated `version_check.json` to reflect current version

### Added
- `ext.php` base class for explicit extension entry point
- `IC_ACP_PATH_NOT_ABSOLUTE` language key for path validation warning
- Path validation: `ic_pngquant_path` now requires an absolute path
- PHPDoc class docblocks added to `listener`, `image_compressor_acp` and `image_compressor_quality` classes
- Documented version notification feature in README

## [1.1.0] - 2026-10-23

### Security
- Fixed command injection vulnerability: `ic_pngquant_path` config value is now passed through `escapeshellarg()` before being used in `shell_exec`. Quality values are cast to `(int)` in the shell command.

### Added
- `version_check.json` to enable in-phpBB version-update notifications.
- `version-check` entry in `composer.json` pointing to the version check file on GitHub.
- `support` section (`issues`, `source`) to `composer.json`.
- Root-level `README.md` symlink pointing to `bakasura/imagecompressor/README.md` for better repository visibility on GitHub.
- ACP settings page now displays a color-coded status indicator (green/yellow/red) showing whether `shell_exec` is available and pngquant is reachable and functional.
- ACP settings page shows the detected pngquant version when the binary is found and working.
- Compression quality (minimum and maximum) is now configurable from the ACP settings page; previously hardcoded to 60–90.
- New migration `image_compressor_quality` adds `ic_min_quality` (default: 60) and `ic_max_quality` (default: 90) configuration keys.
- Guard added for missing quality config keys: logs `IC_LOG_QUALITY_CONFIG_MISSING` if the quality migration has not run yet.
- New language keys: `IC_LOG_SHELL_EXEC_DISABLED`, `IC_LOG_WRITE_FAILED`, `IC_LOG_QUALITY_CONFIG_MISSING`, ACP status indicator messages, and quality field labels.

### Changed
- Version bumped to 1.1.0 in `composer.json`.
- Extension description updated to clarify phpBB 3.3 compatibility.
- PHP minimum version constraint corrected from `>=5.4` to `>=7.1.3` in `composer.json`.
- phpBB soft-require version range updated from `>=3.1.10,<3.3.0@dev` to `>=3.3.0,<4.0.0@dev` in `composer.json`.
- `license.txt` renamed to `LICENSE` to follow standard repository conventions.
- Removed `email` field from author entry in `composer.json`.
- `phpbb_root_path` is now injected via the service container (`%core.root_path%`) instead of using `global $phpbb_root_path`.
- `compress_png()` now returns `bool` (`true` on success, `false` on failure); filesize is only updated when compression succeeds.
- PNG file replacement now uses an atomic write pattern: content is written to a temporary file first, then atomically renamed over the original to prevent corruption on partial write failures.
- `rename()` return value is now verified; failure is logged and the temporary file is cleaned up.
- `file_put_contents()` return value is now verified; failure is logged and the method returns `false`.
- `@unlink()` replaced with `if (file_exists()) { unlink(); }`.
- ACP module `main()` now uses `switch ($mode)` with a `default: trigger_error('NO_MODE')` fallback, following phpBB convention.
- Migration `depends_on()` updated from phpBB 3.1.x (`v314`) to phpBB 3.3.0 (`v330`).
- `%core.root_path%` added as fifth service argument in `services.yml`.
- ACP settings form split into two fieldsets: *pngquant binary* and *Compression quality*.

### Fixed
- Fixed fatal error on PHP 8.0+: `compress_png()` was called statically (`listener::compress_png()`) but is an instance method; corrected to `$this->compress_png()`.
- Fixed double semicolon (`;;`) at the end of `$image_path` construction.
- Fixed erroneous inline comment `// insert attachment into db` inside `compress_png()`.
- Fixed migration `depends_on()` incorrectly referencing a phpBB 3.1.x migration class.

### Documentation
- README completely rewritten: added description, system requirements, pngquant installation instructions, configuration guide (binary path, status indicator, quality fields), compatibility table, and changelog link.
- PHPDoc blocks added to all classes and methods across ACP, migration, and event listener files.
- `@access public` annotations removed (redundant with PHP visibility keywords).
- Copyright and license header added to language file.

## [1.0.0] - 2023-08-16

### Added
- Initial release of the Image Compressor phpBB extension.
- Automatic PNG image compression on upload.

[1.1.1]: https://github.com/BakasuraRCE/phpbb-ext-image-compressor/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/BakasuraRCE/phpbb-ext-image-compressor/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/BakasuraRCE/phpbb-ext-image-compressor/releases/tag/v1.0.0
