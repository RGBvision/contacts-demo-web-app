<?php

class Html
{

    /**
     * Вывод HTML результата с минификацией и GZIP сжатием
     *
     * @param string $data данные для сжатия и вывода
     * @param int $status HTTP статус
     * @param bool $shutdown завершить ли выполнение после вывода
     */
    public static function output(string $data, int $status = 200, bool $shutdown = true): void
    {

        $headers = [];

        // Включение GZIP компрессии, если поддерживается
        if (GZIP_COMPRESSION && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
            ob_start('ob_gzhandler');
            $headers[] = 'Content-Encoding: gzip';
        } else {
            ob_start();
        }

        $headers[] = 'Content-Type: text/html; charset=utf-8';
        $headers[] = 'Cache-Control: must-revalidate';
        if (OUTPUT_EXPIRE) {
            $headers[] = 'Expires: ' . gmdate("D, d M Y H:i:s", time() + OUTPUT_EXPIRE_OFFSET) . ' GMT';
        }
        $headers[] = 'Content-Length: ' . strlen($data);
        $headers[] = 'Vary: Accept-Encoding';

        Response::setHeaders($headers);
        Response::setStatus($status);

        echo $data;

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