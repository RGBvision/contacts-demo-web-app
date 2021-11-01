<?php

class Json
{

	protected function __construct()
	{
		//---
	}

    /**
     * Преобразовать массив в JSON
     *
     * @param array $array
     * @return string
     */
    public static function encode(array $array): string
	{
		$json = json_encode($array, JSON_UNESCAPED_UNICODE);

		if ($json === false) {
            $json = json_encode(['jsonError', json_last_error_msg()]);
        }

		if ($json === false) {
            $json = '{"jsonError": "unknown"}';
        }

		return $json;
	}

    /**
     * Преобразовать JSON в массив или объект
     *
     * @param string $string JSON строка
     * @param bool $object Преобразовать в объект
     * @return mixed
     */
    public static function decode(string $string, bool $object = false)
	{
	    $result = json_decode($string, $object);

	    if (json_last_error() === JSON_ERROR_NONE) {
	        return $result;
        }

		return null;
	}

    /**
     * Вывести JSON в браузер
     *
     * @param array $array данные для вывода
     * @param int $status HTTP статус
     * @param bool $shutdown завершить ли выполнение после вывода
     */
    public static function output(array $array, int $status = 200, bool $shutdown = true): void
    {

		$headers = [
			'Pragma: no-cache',
			'Cache-Control: private, no-cache',
			'Content-Disposition: inline; filename="data.json"',
			'Vary: Accept',
			'Content-type: application/json; charset=utf-8'
		];

        // Включение GZIP компрессии, если поддерживается
        if (GZIP_COMPRESSION && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
            ob_start('ob_gzhandler');
            $headers[] = 'Content-Encoding: gzip';
        } else {
            ob_start();
        }

		Response::setHeaders($headers);
        Response::setStatus($status);

		$json = self::encode($array);

		echo $json;

        // Получение результата вывода
        $render = ob_get_clean();

        // Вывод результата
        if (GZIP_COMPRESSION && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
            $render = gzencode($render, 9);
        }

        echo $render;

        if ($shutdown) {
            Response::shutDown();
        }
	}
}