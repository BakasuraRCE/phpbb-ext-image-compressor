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
        global $language, $template, $request, $phpbb_container;

        /* @var $config \phpbb\config\config */
        $config = $phpbb_container->get('config');

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
                        trigger_error($language->lang('IC_ACP_QUALITY_INVALID') . adm_back_link($this->u_action));
                    }

                    $config->set('ic_pngquant_path', $request->variable('ic_pngquant_path', ''));
                    $config->set('ic_min_quality', $ic_min_quality);
                    $config->set('ic_max_quality', $ic_max_quality);
                    trigger_error($language->lang('IC_ACP_SETTING_SAVED') . adm_back_link($this->u_action));
                }

                // --- pngquant availability check ---
                $ic_pngquant_version  = '';
                $pngquant_path        = $config['ic_pngquant_path'];
                $shell_exec_disabled  = !function_exists('shell_exec')
                    || in_array('shell_exec', array_map('trim', explode(',', ini_get('disable_functions'))));

                if ($shell_exec_disabled)
                {
                    $ic_status_class   = 'errorbox';
                    $ic_status_message = $language->lang('IC_STATUS_SHELL_EXEC_DISABLED');
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
                    // Quick functional test: run pngquant with no args and check the first line of output.
                    // pngquant has no --version flag; calling it without arguments prints the version
                    // on the first line followed by usage text (exits with non-zero, hence 2>&1).
                    $raw_output     = (string) shell_exec(escapeshellarg($pngquant_path) . ' 2>&1');
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
