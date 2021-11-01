<?php

define('APP_NAME', 'DEMO');
define('APP_VERSION', '1.0');
define('APP_BUILD', '1.0.001');
define('APP_INFO', APP_NAME. ' ' . APP_VERSION . ' &copy; ' . date('Y'));

define('DS', DIRECTORY_SEPARATOR);

$config_defaults = array();

$config_defaults['GZIP_COMPRESSION'] = array(
	'DEFAULT' => false,
	'TYPE' => 'bool',
	'VARIANT' => ''
);

$config_defaults['OUTPUT_EXPIRE'] = array(
	'DEFAULT' => false,
	'TYPE' => 'bool',
	'VARIANT' => ''
);

$config_defaults['OUTPUT_EXPIRE_OFFSET'] = array(
	'DEFAULT' => 60 * 60,
	'TYPE' => 'integer',
	'VARIANT' => ''
);

define('CP_CONFIG_DEFAULTS', $config_defaults);

if (file_exists(BASE_DIR . '/configs/environment.php')) {
    include(BASE_DIR . '/configs/environment.php');
}

foreach ($config_defaults as $key => $value) {
	if (!defined($key)) {
        define($key, $value['DEFAULT']);
    }
}

unset($config_defaults);

if (!defined('TIMEZONE')) {
    define('TIMEZONE', 'Europe/Moscow');
}
@date_default_timezone_set(TIMEZONE);

ini_set('arg_separator.output', '&amp;');
ini_set('url_rewriter.tags', '');

// Установка кодировки UTF-8.
function_exists('mb_language') and mb_language('uni');
function_exists('mb_regex_encoding') and mb_regex_encoding('UTF-8');
function_exists('mb_internal_encoding') and mb_internal_encoding('UTF-8');