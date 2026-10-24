# Image Compressor

Automatically compresses PNG images on upload for phpBB 3.3.x, using the external `pngquant` binary to reduce file sizes without significant quality loss.

## Description

Image Compressor hooks into phpBB's file upload event and transparently passes PNG attachments through `pngquant` before they are stored. The compressed file replaces the original and the stored file size metadata is updated accordingly. No user interaction is required — compression happens silently in the background.

Currently only PNG images are supported. JPEG and other formats are passed through unchanged.

## System Requirements

- PHP >= 7.1.3
- phpBB >= 3.3.x
- [pngquant](https://pngquant.org/) 1.8 or later installed on the server

## Installing pngquant

### Debian / Ubuntu

```bash
sudo apt-get install pngquant
```

### RPM-based distributions (Fedora, CentOS, RHEL)

```bash
sudo yum install pngquant
# or with dnf:
sudo dnf install pngquant
```

After installation, verify the binary path:

```bash
which pngquant
```

## Installation

Copy the extension to `phpBB/ext/bakasura/imagecompressor`.

Go to **ACP** > **Customise** > **Extensions** and enable the **Image Compressor** extension.

## Configuration

After enabling the extension, go to **ACP** > **Extensions** > **Image Compressor** > **Settings**.

**Path to pngquant binary** — enter the full path of the `pngquant` executable on your server
(e.g. `/usr/bin/pngquant` or `/usr/local/bin/pngquant`). After saving, the settings page shows
a status indicator confirming whether pngquant is reachable and functional, along with the
detected version.

> **Important:** The default path is `/usr/bin/pngquant`. This may not be correct on all systems.
> macOS with Homebrew, systems with a manual install, or containers may place the binary in a
> different location (e.g. `/usr/local/bin/pngquant`, `/opt/homebrew/bin/pngquant`).
> Always verify the correct path with `which pngquant` after installation and update the ACP
> setting accordingly, otherwise compression will fail silently.

**Minimum quality** and **Maximum quality** (0–100) — control the compression aggressiveness.
The maximum quality sets the target; lower values produce smaller files at the cost of image
fidelity. If pngquant cannot maintain quality above the minimum threshold for a given image,
compression is skipped and the original file is kept unchanged.

## PHP / phpBB Compatibility

| Component | Minimum version |
|-----------|-----------------|
| PHP       | 7.1.3           |
| phpBB     | 3.3.x           |
| pngquant  | 1.8             |

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a full list of changes.

## License

[GPLv2](LICENSE)
