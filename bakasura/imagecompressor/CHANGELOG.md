# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

[1.1.0]: https://github.com/BakasuraRCE/phpbb-ext-image-compressor/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/BakasuraRCE/phpbb-ext-image-compressor/releases/tag/v1.0.0
