<?php

namespace bakasura\imagecompressor\migrations;

/**
 * Migration: adds pngquant quality range configuration keys (ic_min_quality, ic_max_quality).
 *
 * @package bakasura\imagecompressor\migrations
 * @copyright (c) bakasura
 * @license GNU General Public License, version 2 (GPL-2.0)
 */
class image_compressor_quality extends \phpbb\db\migration\migration
{
    /**
     * Skip if quality config values already exist.
     */
    public function effectively_installed()
    {
        return isset($this->config['ic_min_quality']) && isset($this->config['ic_max_quality']);
    }

    /**
     * Depends on the base Image Compressor migration.
     *
     * @return array
     */
    static public function depends_on()
    {
        return ['\\bakasura\\imagecompressor\\migrations\\image_compressor_acp'];
    }

    /**
     * Adds compression quality configuration values.
     *
     * @return array List of migration operations.
     */
    public function update_data()
    {
        return [
            ['config.add', ['ic_min_quality', 60]],
            ['config.add', ['ic_max_quality', 90]],
        ];
    }
}
