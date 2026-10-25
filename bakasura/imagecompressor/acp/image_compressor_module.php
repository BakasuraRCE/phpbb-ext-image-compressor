<?php

namespace bakasura\imagecompressor\acp;

/**
 * ACP module for Image Compressor settings.
 *
 * @package bakasura\imagecompressor\acp
 */
class image_compressor_module
{
    /** @var string */
    public $u_action;

    /** @var string */
    public $tpl_name;

    /** @var string */
    public $page_title;

    /**
     * Main entry point for the ACP module.
     *
     * @param int    $id   Module ID
     * @param string $mode Module mode (e.g. 'settings')
     * @return void
     */
    public function main($id, $mode)
    {
        global $config, $language, $template, $request;

        $this->tpl_name = 'acp_image_compressor';
        $this->page_title = $language->lang('IC_ACP_TITLE');

        switch ($mode)
        {
            case 'settings':
                add_form_key('ic_settings');

                if ($request->is_set_post('submit')) {
                    if (!check_form_key('ic_settings')) {
                        trigger_error('FORM_INVALID');
                    }

                    $ic_min_quality = max(0, min(100, $request->variable('ic_min_quality', 60)));
                    $ic_max_quality = max(0, min(100, $request->variable('ic_max_quality', 90)));

                    if ($ic_min_quality >= $ic_max_quality) {
                        trigger_error($language->lang('IC_ACP_QUALITY_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
                    }

                    $ic_pngquant_path = $request->variable('ic_pngquant_path', '');
                    if ($ic_pngquant_path !== '' && $ic_pngquant_path[0] !== '/') {
                        trigger_error($language->lang('IC_ACP_PATH_NOT_ABSOLUTE') . adm_back_link($this->u_action), E_USER_WARNING);
                    }
                    $config->set('ic_pngquant_path', $ic_pngquant_path);
                    $config->set('ic_min_quality', $ic_min_quality);
                    $config->set('ic_max_quality', $ic_max_quality);
                    trigger_error($language->lang('IC_ACP_SETTING_SAVED') . adm_back_link($this->u_action));
                }

                // --- pngquant availability check ---
                $ic_pngquant_version  = '';
                $pngquant_path        = $config['ic_pngquant_path'];

                // proc_open is used instead of shell_exec because hardened containers
                // (e.g. DHI distroless images) have no /bin/sh; shell_exec/exec/system
                // all invoke /bin/sh -c internally and will always fail in those environments.
                // proc_open() with an array executes the binary via execve() directly.
                $proc_open_disabled = !function_exists('proc_open')
                    || in_array('proc_open', array_map('trim', explode(',', ini_get('disable_functions'))));

                $ic_status_class = '';
                $ic_status_message = '';

                if ($proc_open_disabled)
                {
                    $ic_status_class   = 'errorbox';
                    $ic_status_message = $language->lang('IC_STATUS_PROC_OPEN_DISABLED');
                }
                else if (empty($pngquant_path))
                {
                    $ic_status_class   = 'attentionbox';
                    $ic_status_message = $language->lang('IC_STATUS_PATH_EMPTY');
                }
                else if (!is_executable($pngquant_path))
                {
                    $ic_status_class   = 'errorbox';
                    $ic_status_message = $language->lang('IC_STATUS_BINARY_NOT_FOUND', $pngquant_path);
                }
                else
                {
                    // Quick functional test: invoke pngquant with no arguments.
                    // pngquant has no --version flag; with no args it prints the version
                    // line on the first line of stderr and exits non-zero.
                    // Array form = exec form: no shell needed, works in distroless containers.
                    // stdin is closed immediately (EOF) so pngquant never reads from it.
                    $descriptorspec = [
                        0 => ['pipe', 'r'],  // stdin: closed immediately
                        1 => ['pipe', 'w'],  // stdout: capture
                        2 => ['pipe', 'w'],  // stderr: capture (pngquant writes version here)
                    ];
                    $process = @proc_open([$pngquant_path], $descriptorspec, $pipes);
                    if (!is_resource($process))
                    {
                        $ic_status_class    = 'attentionbox';
                        $ic_status_message  = $language->lang('IC_STATUS_BINARY_TEST_FAILED', $pngquant_path);
                        $ic_pngquant_version = '';
                    }
                    else
                    {
                        fclose($pipes[0]);  // send EOF to pngquant's stdin immediately
                        $stdout = stream_get_contents($pipes[1]);
                        $stderr = stream_get_contents($pipes[2]);
                        fclose($pipes[1]);
                        fclose($pipes[2]);
                        proc_close($process);

                        // pngquant writes version info to stderr; combine both to be safe
                        $raw_output     = $stdout . $stderr;
                        $version_output = trim(explode("\n", $raw_output)[0]);
                        if ($version_output !== '' && stripos($version_output, 'pngquant') !== false)
                        {
                            $ic_status_class    = 'successbox';
                            $ic_status_message  = $language->lang('IC_STATUS_OK');
                            $ic_pngquant_version = $version_output;
                        }
                        else
                        {
                            $ic_status_class    = 'attentionbox';
                            $ic_status_message  = $language->lang('IC_STATUS_BINARY_TEST_FAILED', $pngquant_path);
                            $ic_pngquant_version = '';
                        }
                    }
                }
                // --- end check ---

                $template->assign_vars([
                    'IC_PNGQUANT_PATH'    => $pngquant_path,
                    'IC_PNGQUANT_VERSION' => $ic_pngquant_version,
                    'IC_MIN_QUALITY'      => (int) $config['ic_min_quality'],
                    'IC_MAX_QUALITY'      => (int) $config['ic_max_quality'],
                    'IC_STATUS_CLASS'     => $ic_status_class,
                    'IC_STATUS_MESSAGE'   => $ic_status_message,
                    'U_ACTION'            => $this->u_action,
                ]);
            break;

            default:
                trigger_error('NO_MODE', E_USER_ERROR);
            break;
        }
    }
}
