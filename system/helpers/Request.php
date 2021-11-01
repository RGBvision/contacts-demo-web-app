<?php

class Request
{
    protected function __construct()
    {
        // ---
    }

    public static function init(): void
    {
        if (
            in_array(strtoupper($_SERVER['REQUEST_METHOD']), ['PUT', 'DELETE'])
            && ($input_data = file_get_contents("php://input"))
        ) {
            parse_str($input_data, $input_params);
            $_REQUEST = array_merge_recursive($_REQUEST, $input_params);
        }
    }

    /**
     * Перенаправление запроса
     *
     * @param string $url целевой URL перенаправления
     * @param int $status HTTP статус перенаправления
     * @param int|null $delay задержка в секундах перед перенаправлением
     */
    public static function redirect(string $url, int $status = 302, ?int $delay = null): void
    {

        if (headers_sent()) {

            if ($delay !== null) {
                echo "<script>setTimeout(() => { document.location.href='" . $url . "'; }, " . $delay * 1000 . ");</script>\n";
            } else {
                echo "<script>document.location.href='" . $url . "';</script>\n";
            }

        } else {

            Response::setStatus($status);

            if ($delay !== null) {
                sleep($delay);
            }

            Response::setHeader('Location:' . $url, true, $status);
            Response::shutDown();

        }
    }

    /**
     * Получить GET параметр запроса
     *
     * @param string $key имя (ключ) параметра
     * @return array|mixed|null
     */
    public static function get(string $key)
    {
        return Arrays::get($_GET, $key);
    }

    /**
     * Получить POST параметр запроса
     *
     * @param string $key имя (ключ) параметра
     * @return array|mixed|null
     */
    public static function post(string $key)
    {
        return Arrays::get($_POST, $key);
    }

    /**
     * Получить GET или POST параметр запроса
     *
     * @param string $key имя (ключ) параметра
     * @return array|mixed|null
     */
    public static function request(string $key)
    {
        return Arrays::get($_REQUEST, $key);
    }

    /**
     * Получить путь запроса
     *
     * @return string
     */
    public static function getPath(): string
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    /**
     * Проверить пришел ли AJAX запрос
     *
     * @return bool
     */
    public static function isAjax(): bool
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'));
    }
}