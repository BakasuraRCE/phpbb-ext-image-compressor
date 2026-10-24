<?php


namespace bakasura\imagecompressor\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener
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
     * CREDITS: https://pngquant.org/php.html
     *
     * @param string $path_to_png_file Path to the PNG file to compress (e.g. the uploaded tmp path).
     * @return bool True on successful compression, false on failure.
     */
    private function compress_png($path_to_png_file)
    {
        // Verify the pngquant binary exists at the configured path
        if (!file_exists($this->config['ic_pngquant_path'])) {
            $this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'IC_LOG_INVALID_PNGQUANT_PATH', false, [$this->config['ic_pngquant_path']]);
            return false;
        }

        // Check that shell_exec is not disabled in this PHP environment
        if (!function_exists('shell_exec') || in_array('shell_exec', array_map('trim', explode(',', ini_get('disable_functions'))))) {
            $this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'IC_LOG_SHELL_EXEC_DISABLED', false, []);
            return false;
        }

        // Verify quality config keys exist (requires image_compressor_quality migration to have run)
        if (!isset($this->config['ic_min_quality']) || !isset($this->config['ic_max_quality'])) {
            $this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'IC_LOG_QUALITY_CONFIG_MISSING', false, []);
            return false;
        }

        // Quality range read from ACP config (0-100; lower = smaller file)
        $min_quality = (int) $this->config['ic_min_quality'];
        $max_quality = (int) $this->config['ic_max_quality'];

        // '-' makes it use stdout, required to save to $compressed_png_content variable
        // '<' makes it read from the given file path
        // escapeshellarg() makes this safe to use with any path
        $compressed_png_content = shell_exec(
            escapeshellarg($this->config['ic_pngquant_path']) .
            ' --quality=' . (int)$min_quality . '-' . (int)$max_quality .
            ' - < ' . escapeshellarg($path_to_png_file)
        );

        if (!$compressed_png_content) {
            $this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'IC_LOG_COMPRESS_FAILED', false, [$path_to_png_file]);
            return false;
        }

        // Write to a temporary file first, then atomically rename to avoid partial writes corrupting the original
        $tmp_file = $path_to_png_file . '.tmp_' . getmypid();
        $result = file_put_contents($tmp_file, $compressed_png_content);
        if ($result === false) {
            $this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'IC_LOG_WRITE_FAILED', false, [$path_to_png_file]);
            if (file_exists($tmp_file)) {
                unlink($tmp_file);
            }
            return false;
        }

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
     * @return null
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
            clearstatcache();
            $filedata['filesize'] = (int)filesize($image_path);
            $event['filedata'] = $filedata;
        }
    }
}
