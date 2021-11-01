<?php

//--- Часовой пояс системы
define('TIMEZONE', 'Europe/Moscow');

//--- Включить gzip компрессию
define('GZIP_COMPRESSION', false);

//--- Отдавать заголовок на кеширование страницы
define('OUTPUT_EXPIRE', false);

//--- Время жизни кеширования страницы (60*60 - 1 час)
define('OUTPUT_EXPIRE_OFFSET', 3600);
