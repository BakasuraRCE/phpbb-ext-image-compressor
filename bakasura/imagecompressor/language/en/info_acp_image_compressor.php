<?php
/**
 * Image Compressor ACP language file (English)
 *
 * @copyright (c) bakasura
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = [];
}

$lang = array_merge($lang, [
    'IC_ACP_TITLE'                 => 'Image Compressor',
    'IC_ACP'                       => 'Settings',
    'IC_ACP_PNGQUANT_PATH'         => 'Path to pngquant binary',
    'IC_ACP_SETTING_SAVED'         => 'Settings have been saved successfully!',
    'IC_LOG_INVALID_PNGQUANT_PATH' => 'Path to pngquant is invalid: %s',
    'IC_LOG_COMPRESS_FAILED'       => "Conversion to compressed PNG failed. Is pngquant 1.8+ installed on the server?\nFile: %s",
    'IC_LOG_SHELL_EXEC_DISABLED'    => 'Image Compressor: shell_exec is disabled in this PHP environment; PNG compression skipped.',
    'IC_LOG_WRITE_FAILED'           => 'Image Compressor: failed to write compressed PNG to file: %s',
    'IC_LOG_QUALITY_CONFIG_MISSING' => 'Image Compressor: quality configuration is missing; run the extension migrations and check the ACP settings.',

    // ACP status indicator messages
    'IC_STATUS_SHELL_EXEC_DISABLED' => 'Error: shell_exec() is disabled in this PHP environment. PNG compression is unavailable. Contact your hosting provider.',
    'IC_STATUS_PATH_EMPTY'          => 'No pngquant path configured yet. Enter the path to the pngquant binary below and save.',
    'IC_STATUS_BINARY_NOT_FOUND'    => 'Error: pngquant binary not found or not executable at: %s',
    'IC_STATUS_BINARY_TEST_FAILED'  => 'Warning: pngquant binary found at %s but failed to run. Check file permissions and binary integrity.',
    'IC_STATUS_OK'                  => 'pngquant is installed and working correctly.',
    'IC_ACP_PNGQUANT_VERSION'       => 'Installed version',

    // Compression quality settings
    'IC_ACP_LEGEND_BINARY'          => 'pngquant binary',
    'IC_ACP_LEGEND_QUALITY'         => 'Compression quality',
    'IC_ACP_MIN_QUALITY'            => 'Minimum quality',
    'IC_ACP_MAX_QUALITY'            => 'Maximum quality',
    'IC_ACP_QUALITY_HINT'           => '(0-100) The maximum quality controls file size — lower values produce smaller files. pngquant skips compression entirely if it cannot stay above the minimum quality.',
    'IC_ACP_QUALITY_INVALID'        => 'Error: Minimum quality must be strictly less than maximum quality.',
]);
