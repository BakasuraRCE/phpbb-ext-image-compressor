<?php

namespace bakasura\imagecompressor\acp;

/**
 * ACP module information for Image Compressor.
 *
 * @package bakasura\imagecompressor\acp
 */
class image_compressor_info
{
    /**
     * Returns the ACP module definition array.
     *
     * @return array Module configuration with title, filename, and modes.
     */
    public function module()
    {
        return [
            'filename' => '\\bakasura\\imagecompressor\\acp\\image_compressor_module',
            'title' => 'IC_ACP_TITLE',
            'modes' => [
                'settings' => [
                    'title' => 'IC_ACP',
                    'auth' => 'ext_bakasura/imagecompressor && acl_a_board',
                    'cat' => ['IC_ACP_TITLE'],
                ],
            ],
        ];
    }
}
