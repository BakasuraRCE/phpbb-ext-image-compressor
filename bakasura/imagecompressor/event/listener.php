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

    /** @var \phpbb\config\config $config */
    protected $config;

    /** @var \phpbb\log\log_interface $log */
    protected $log;


    /** @var \phpbb\user $user */
    protected $user;

    /**
     * Constructor
     *
     * @param \phpbb\request\request $request Request object
     * @param \phpbb\config\config $config
     * @param \phpbb\log\log_interface $log
     * @param \phpbb\user $user
     * @access public
     */
    public function __construct($request, $config, $log, $user)
    {
        $this->request = $request;
        $this->config = $config;
        $this->log = $log;
        $this->user = $user;
    }

    /**
     * Assign functions defined in this class to event listeners in the core
     *
     * @return array
     * @static
     * @access public
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
     * There's package for Debian/Ubuntu and RPM for other distributions on http://pngquant.org
     *
     * CREDITS: https://pngquant.org/php.html
     *
     * @param $path_to_png_file string - path to any PNG file, e.g. $_FILE['file']['tmp_name']
     * @param $max_quality int - conversion quality, useful values from 60 to 100 (smaller number = smaller file)
     * @return string - content of PNG file after conversion
     */
    private function compress_png($path_to_png_file, $max_quality = 90)
    {
        // insert attachment into db
        if (!@file_exists($this->config['ic_pngquant_path'])) {
            $this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'IC_LOG_INVALID_PNGQUANT_PATH', false, [$this->config['ic_pngquant_path']]);
            return;
        }

        // guarantee that quality won't be worse than that.
        $min_quality = 60;

        // '-' makes it use stdout, required to save to $compressed_png_content variable
        // '<' makes it read from the given file path
        // escapeshellarg() makes this safe to use with any path
        $compressed_png_content = shell_exec($this->config['ic_pngquant_path'] . " --quality=$min_quality-$max_quality - < " . escapeshellarg($path_to_png_file));

        if (!$compressed_png_content) {
            $this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'IC_LOG_COMPRESS_FAILED', false, [$path_to_png_file]);
            return;
        }

        file_put_contents($path_to_png_file, $compressed_png_content);
    }

    /**
     * Compress uploaded images
     *
     * @param object $event The event object
     * @return null
     * @access public
     */
    public function core_modify_uploaded_file($event)
    {
        global $phpbb_root_path;

        $filedata = $event['filedata'];
        $is_image = $event['is_image'];

        // only compress valid png images
        if (!$filedata['post_attach'] || !$is_image || $filedata['mimetype'] !== 'image/png')
            return;

        $image_path = $phpbb_root_path . $this->config['upload_path'] . '/' . utf8_basename($filedata['physical_filename']);;

        // file must exist
        if (!file_exists($image_path))
            return;

        // compress image
        listener::compress_png($image_path);

        // update filesize
        clearstatcache();
        $filedata['filesize'] = (int)filesize($image_path);
        $event['filedata'] = $filedata;
    }
}