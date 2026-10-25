<?php


namespace bakasura\imagecompressor\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener for bakasura/imagecompressor.
 *
 * Hooks into the core.modify_uploaded_file event to automatically
 * compress PNG attachments using pngquant after they are uploaded.
 *
 * @package bakasura\imagecompressor\event
 * @copyright (c) bakasura
 * @license GNU General Public License, version 2 (GPL-2.0)
 */
class listener implements EventSubscriberInterface
{
    /** @var \phpbb\request\request */
    protected $request;

    /** @var \phpbb\config\config */
    protected $config;

    /** @var \phpbb\log\log_interface */
    protected $log;

    /** @var \phpbb\user */
    protected $user;

    /** @var string */
    protected $phpbb_root_path;

    /**
     * Constructor
     *
     * @param \phpbb\request\request   $request         Request object
     * @param \phpbb\config\config     $config          Config object
     * @param \phpbb\log\log_interface $log             Log object
     * @param \phpbb\user              $user            User object
     * @param string                   $phpbb_root_path phpBB root path
     */
    public function __construct($request, $config, $log, $user, $phpbb_root_path)
    {
        $this->request         = $request;
        $this->config          = $config;
        $this->log             = $log;
        $this->user            = $user;
        $this->phpbb_root_path = $phpbb_root_path;
    }

    /**
     * Assign functions defined in this class to event listeners in the core
     *
     * @return array
     * @static
     */
    public static function getSubscribedEvents()
    {
        return array(
            'core.modify_uploaded_file' => 'core_modify_uploaded_file',
        );
    }


    /**
     * Optimizes PNG file with pngquant 1.8 or later (reduces file size of 24-bit/32-bit PNG images).
     *
     * You need to install pngquant 1.8 on the server (ancient version 1.0 won't work).
     * There's a package for Debian/Ubuntu and RPM for other distributions on http://pngquant.org
     *
     * Uses proc_open() array form (exec form) to invoke the binary directly via execve()
     * without a shell. This is required for hardened containers (e.g. DHI distroless images)
     * where /bin/sh is absent and shell_exec/exec/system/passthru always fail.
     *
     * @param string $path_to_png_file Path to the PNG file to compress (e.g. the uploaded tmp path).
     * @return bool True on successful compression (or when compression is intentionally skipped),
     *              false on failure.
     */
    private function compress_png($path_to_png_file)
    {
        // Verify the pngquant binary exists and is executable at the configured path
        if (!is_executable($this->config['ic_pngquant_path'])) {
            $this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'IC_LOG_INVALID_PNGQUANT_PATH', false, [$this->config['ic_pngquant_path']]);
            return false;
        }

        // Check that proc_open is available (may be disabled via disable_functions)
        if (!function_exists('proc_open') || in_array('proc_open', array_map('trim', explode(',', ini_get('disable_functions'))))) {
            $this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'IC_LOG_PROC_OPEN_DISABLED', false, []);
            return false;
        }

        // Verify quality config keys exist (requires image_compressor_quality migration to have run)
        if (!isset($this->config['ic_min_quality']) || !isset($this->config['ic_max_quality'])) {
            $this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'IC_LOG_QUALITY_CONFIG_MISSING', false, []);
            return false;
        }

        $min_quality = (int) $this->config['ic_min_quality'];
        $max_quality = (int) $this->config['ic_max_quality'];

        // Write compressed output directly to a temp file rather than buffering the
        // entire PNG binary in a PHP string — more memory-efficient for large images,
        // and safe for concurrent requests (tempnam() guarantees a unique path).
        $tmp_file = tempnam(dirname($path_to_png_file), 'ic_');
        if ($tmp_file === false) {
            $this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'IC_LOG_WRITE_FAILED', false, [$path_to_png_file]);
            return false;
        }

        // Array form = exec form: invokes the binary directly via execve(), no shell needed.
        // '-' tells pngquant to read stdin and write to stdout.
        $descriptorspec = [
            0 => ['file', $path_to_png_file, 'r'],  // stdin:  read PNG from upload path
            1 => ['file', $tmp_file,          'w'],  // stdout: write compressed PNG to temp file
            2 => ['pipe',                     'w'],  // stderr: drain to avoid buffer deadlock
        ];
        $process = @proc_open(
            [
                $this->config['ic_pngquant_path'],
                '--quality=' . $min_quality . '-' . $max_quality,
                '-',
            ],
            $descriptorspec,
            $pipes
        );

        if (!is_resource($process)) {
            $this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'IC_LOG_COMPRESS_FAILED', false, [$path_to_png_file]);
            if (file_exists($tmp_file)) {
                unlink($tmp_file);
            }
            return false;
        }

        // Drain stderr before proc_close() to prevent the child from blocking on a full pipe buffer
        stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exit_code = proc_close($process);

        // Exit code 24: pngquant cannot stay above the minimum quality floor; it intentionally
        // skips compression. This is not an error — keep the original file unchanged.
        if ($exit_code === 24) {
            if (file_exists($tmp_file)) {
                unlink($tmp_file);
            }
            return true;
        }

        if ($exit_code !== 0 || filesize($tmp_file) === 0) {
            $this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'IC_LOG_COMPRESS_FAILED', false, [$path_to_png_file]);
            if (file_exists($tmp_file)) {
                unlink($tmp_file);
            }
            return false;
        }

        // Compressed result is not smaller; keep original
        if (filesize($tmp_file) >= filesize($path_to_png_file)) {
            if (file_exists($tmp_file)) { unlink($tmp_file); }
            return true;
        }

        // Atomic rename: replaces the original only after the compressed file is fully written
        if (!rename($tmp_file, $path_to_png_file)) {
            $this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'IC_LOG_WRITE_FAILED', false, [$path_to_png_file]);
            if (file_exists($tmp_file)) {
                unlink($tmp_file);
            }
            return false;
        }

        return true;
    }

    /**
     * Compress uploaded images
     *
     * @param object $event The event object
     * @return void
     */
    public function core_modify_uploaded_file($event)
    {
        $filedata = $event['filedata'];
        $is_image = $event['is_image'];

        // only compress valid png images
        if (!$filedata['post_attach'] || !$is_image || $filedata['mimetype'] !== 'image/png')
            return;

        $image_path = $this->phpbb_root_path . $this->config['upload_path'] . '/' . utf8_basename($filedata['physical_filename']);

        // file must exist
        if (!file_exists($image_path))
            return;

        // compress image and update filesize only if compression succeeded
        if ($this->compress_png($image_path)) {
            // update filesize
            clearstatcache(true, $image_path);
            $filedata['filesize'] = (int)filesize($image_path);
            $event['filedata'] = $filedata;
        }
    }
}
