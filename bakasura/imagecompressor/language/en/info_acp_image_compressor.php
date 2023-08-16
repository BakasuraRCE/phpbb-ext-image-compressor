<?php

if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = [];
}

$lang = array_merge($lang, [
    'IC_ACP_TITLE' => 'Image Compressor',
    'IC_ACP' => 'Settings',
    'IC_ACP_PNGQUANT_PATH' => 'Path to pngquant binary',
    'IC_ACP_SETTING_SAVED' => 'Settings have been saved successfully!',
    'IC_LOG_INVALID_PNGQUANT_PATH' => 'Path to pngquant is invalid: %s',
    'IC_LOG_COMPRESS_FAILED' => "Conversion to compressed PNG failed. Is pngquant 1.8+ installed on the server?\nFile: %s",
]);