<?php

namespace bakasura\imagecompressor\acp;

class image_compressor_info
{
    public function module()
    {
        return [
            'filename' => '\bakasura\imagecompressor\acp\image_compressor_module',
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

