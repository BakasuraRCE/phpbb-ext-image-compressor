<?php

namespace bakasura\imagecompressor\migrations;

class image_compressor_acp extends \phpbb\db\migration\migration
{
    /**
     * If our config variable already exists in the db
     * skip this migration.
     */
    public function effectively_installed()
    {
        return isset($this->config['ic_pngquant_path']);
    }

    /**
     * This migration depends on phpBB's v314 migration
     * already being installed.
     */
    static public function depends_on()
    {
        return ['\phpbb\db\migration\data\v31x\v314'];
    }

    public function update_data()
    {
        return [

            // Add the config variable we want to be able to set
            ['config.add', [
                'ic_pngquant_path',
                '/usr/bin/pngquant'
            ]],

            // Add a parent module (IC_ACP_TITLE) to the Extensions tab (ACP_CAT_DOT_MODS)
            ['module.add', [
                'acp',
                'ACP_CAT_DOT_MODS',
                'IC_ACP_TITLE'
            ]],

            // Add our main_module to the parent module (IC_ACP_TITLE)
            ['module.add', [
                'acp',
                'IC_ACP_TITLE',
                [
                    'module_basename' => '\bakasura\imagecompressor\acp\image_compressor_module',
                    'modes' => ['settings'],
                ],
            ]],
        ];
    }
}