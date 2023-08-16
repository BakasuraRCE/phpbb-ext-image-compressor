<?php

namespace bakasura\imagecompressor\acp;

class image_compressor_module
{
    public $u_action;
    public $tpl_name;
    public $page_title;

    public function main($id, $mode)
    {
        global $language, $template, $request, $phpbb_container;

        /* @var $config \phpbb\config\config */
        $config = $phpbb_container->get('config');

        $this->tpl_name = 'acp_image_compressor';
        $this->page_title = $language->lang('IC_ACP_TITLE');

        add_form_key('ic_settings');

        if ($request->is_set_post('submit')) {
            if (!check_form_key('ic_settings')) {
                trigger_error('FORM_INVALID');
            }

            $config->set('ic_pngquant_path', $request->variable('ic_pngquant_path', ''));
            trigger_error($language->lang('IC_ACP_SETTING_SAVED') . adm_back_link($this->u_action));
        }

        $template->assign_vars([
            'IC_PNGQUANT_PATH' => $config['ic_pngquant_path'],
            'U_ACTION' => $this->u_action,
        ]);
    }
}