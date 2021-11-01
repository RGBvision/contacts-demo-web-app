<?php

// Проверка версии PHP (7.4+)
if (PHP_VERSION_ID < 70400) {
    exit ('Эта программа использует PHP 7.4 или выше.');
}

// Проверка наличия файла настроек для подключения к БД
if (!file_exists(BASE_DIR . '/configs/db.config.php') || !@filesize(BASE_DIR . '/configs/db.config.php')) {
    // Запуск инсталлятора, если файла настроек подключения к БД нет.
    header('Location:install/index.php');
    exit;
}

/**
 * Установка хоста
 */
function setHost(): void
{
    if (isset($_SERVER['HTTP_HOST'])) {
        // Все символы в $_SERVER ['HTTP_HOST'] переводятся в нижний регистр и проверяются на наличие недопустимых в соответствии со спецификацией RFC 952 и RFC 2181
        $_SERVER['HTTP_HOST'] = strtolower($_SERVER['HTTP_HOST']);

        if (!preg_match('/^\[?(?:[a-z0-9-:\]_]+\.?)+$/', $_SERVER['HTTP_HOST'])) {
            // Если $_SERVER['HTTP_HOST'] не соответствует спецификации, то это скорее всего попытка взлома. Значит нужно завершить выполнение со статусом 400.
            Response::setStatus(400);
            Response::shutDown();
        }
    } else {
        $_SERVER['HTTP_HOST'] = '';
    }

    $ssl = isSSL();

    $schema = ($ssl) ? 'https://' : 'http://';

    $host = str_replace(':' . $_SERVER['SERVER_PORT'], '', $_SERVER['HTTP_HOST']);

    $port = ((int)$_SERVER['SERVER_PORT'] === 80 || (int)$_SERVER['SERVER_PORT'] === 443 || $ssl) ? '' : ':' . $_SERVER['SERVER_PORT'];

    define('HOST', $schema . $host . $port);
}


/**
 * Установка константы ABS_PATH, которая содержит абсолютный путь с файлам системы
 */
function absPath(): void
{
    $abs_path = dirname(
        ((strpos($_SERVER['PHP_SELF'], $_SERVER['SCRIPT_NAME']) === false) && (PHP_SAPI === 'cgi'))
            ? $_SERVER['PHP_SELF']
            : $_SERVER['SCRIPT_NAME']);

    if (defined('BASE_DIR')) {
        $abs_path = dirname($abs_path);
    }

    define('ABS_PATH', rtrim(str_replace("\\", "/", $abs_path), '/') . '/');
}


/**
 * Проверка защищенного (SSL) соединения
 *
 * @return bool
 */
function isSSL(): bool
{
    return (isset($_SERVER['HTTPS']) && ((strtolower($_SERVER['HTTPS']) === 'on') || ((int)$_SERVER['HTTPS'] === 1)))
        || (isset($_SERVER['SERVER_PORT']) && ((int)$_SERVER['SERVER_PORT'] === 443));
}


/**
 * Загрузка конфигурации
 */
function loadConfig(): void
{
    if (file_exists(BASE_DIR . '/system/config.php')) {
        include_once(BASE_DIR . '/system/config.php');
    } else {
        throw new RuntimeException('The config file does not exist.');
    }
}

// Загрузка конфигурации
loadConfig();

// Установка константы ABS_PATH
absPath();

// Подключение автозагрузчика
require_once BASE_DIR . '/system/Loader.php';

// Инициализация автозагрузчика
Loader::init();

// Загрузка EasyDB
Loader::addDirectory(BASE_DIR . '/system/lib/db/');

// Загрузка классов ядра системы
Loader::addDirectory(BASE_DIR . '/system/core/');

// Загрузка вспомогательных классов
Loader::addDirectory(BASE_DIR . '/system/helpers/');

// Установка HOST
setHost();

// Подключение i18n
$language = 'ru';
if ($_http_accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE']) {

    $prefLocales = array_reduce(
        explode(',', $_http_accept_language),
        static function ($res, $el) {
            [$l, $q] = array_merge(explode(';q=', $el), [1]);
            $res[$l] = (float)$q;
            return $res;
        }, []);

    arsort($prefLocales);

    $locales = array_keys($prefLocales);

    $language = substr($locales[0], 0, 2);

}
i18n::init(BASE_DIR . '/system/i18n/', $language);

// Загрузка конфигурации подключения к БД
$config = [];
include_once(BASE_DIR . '/configs/db.config.php');

// Инициализация подключения к БД
DB::init($config);

// Установка TimeZone.
try {
    $tz = (new DateTime('now', new DateTimeZone(TIMEZONE)))->format('P');
} catch (Exception $e) {
    die();
}

switch (DB::$db_engine) {
    case 'mysql':
        DB::query("SET time_zone = ?", $tz);
        break;
    case 'postgresql':
        DB::query("SET TIME ZONE '$tz'");
        break;
}

// Подготовка работы с запросом
Request::init();

// Заголовки ответа.
$headers = [
    'Content-Type: text/html; charset=UTF-8',
    'X-Engine: ' . APP_NAME,
    'X-Engine-Build: ' . APP_BUILD,
    'X-Engine-Copyright: (c) RGBvision ' . date('Y'),
    'X-Engine-Site: https://rgbvision.net'
];

// Установка HEADERS ответа.
Response::setHeaders($headers);