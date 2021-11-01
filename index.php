<?php

// Установка глобальной константы с корневой папкой системы
define('BASE_DIR', str_replace("\\", '/', __DIR__));

// Инициализация системы
require_once BASE_DIR . '/system/init.php';

// Инициализация роутера
Router::init();

// Запуск роутера
Router::execute();