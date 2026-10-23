# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-10-23

### Added
- `version_check.json` to enable in-phpBB version-update notifications.
- `version-check` entry in `composer.json` pointing to the version check file on GitHub.
- `support` section (`issues`, `source`) to `composer.json`.
- Root-level `README.md` symlink pointing to `bakasura/imagecompressor/README.md` for better repository visibility on GitHub.

### Changed
- Version bumped to 1.1.0 in `composer.json`.
- Extension description updated to clarify phpBB 3.3 compatibility.
- PHP minimum version constraint corrected from `>=5.4` to `>=7.1.3` in `composer.json`.
- phpBB soft-require version range updated from `>=3.1.10,<3.3.0@dev` to `>=3.3.0,<4.0.0@dev` in `composer.json`.
- `license.txt` renamed to `LICENSE` to follow standard repository conventions.
- Removed `email` field from author entry in `composer.json`.

## [1.0.0] - 2023-08-16

### Added
- Initial release of the Image Compressor phpBB extension.
- Automatic PNG image compression on upload.

[1.1.0]: https://github.com/BakasuraRCE/phpbb-ext-image-compressor/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/BakasuraRCE/phpbb-ext-image-compressor/releases/tag/v1.0.0
