<?php

use ParagonIE\EasyDB\EasyDB;

class DB
{
    //--- Instance
    private static ?DB $instance = null;
    //--- DB engine
    static public string $db_engine = 'mysql'; // default
    //--- DB host
    static protected $db_host;
    //--- DB user
    static protected $db_user;
    //--- DB password
    static protected $db_pass;
    //--- DB port
    static protected $db_port;
    //--- DB name
    static protected $db_name;
    //--- PDO connection
    static public ?PDO $connection = null;
    //--- driver (EasyDB)
    static public ?EasyDB $driver = null;

    //--- Constructor
    private function __construct(array $config)
    {
        self::$db_engine = $config['dbengine'];
        self::$db_host = $config['dbhost'];
        self::$db_user = $config['dbuser'];
        self::$db_pass = $config['dbpass'];
        self::$db_name = $config['dbname'];

        self::$db_engine = $config['dbengine'] ?? 'mysql';
        self::$db_port = $config['dbport'] ?? null;

        if (!is_object(self::$connection) || !self::$connection instanceof PDO) {

            $connection_string = sprintf("%s:host=%s;port=%d;dbname=%s;user=%s;password=%s;charset=utf8;",
                (self::$db_engine === 'postgresql') ? 'pgsql' : 'mysql',
                self::$db_host,
                self::$db_port,
                self::$db_name,
                self::$db_user,
                self::$db_pass);

            try {
                self::$connection = @new PDO($connection_string);
                self::$driver = new EasyDB(self::$connection, self::$db_engine);
            } catch (PDOException $e) {
                self::shutDown(__METHOD__ . ': ' . $e->getMessage());
            }
        }
    }


    /**
     * Получить инстанс класса
     *
     * @param array $config параметры подключения к БД
     * @return DB|null
     */
    public static function getInstance(array $config): ?DB
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }


    /**
     * Инициализация
     *
     * @param array $config параметры подключения к БД
     */
    public static function init(array $config): void
    {
        self::getInstance($config);
    }


    /**
     * Завершение работы и вывод сообщения при ошибке подключения к БД
     *
     * @param string $error
     */
    public static function shutDown(string $error = ''): void
    {
        ob_start();
        header('HTTP/1.1 503 Service Temporarily Unavailable');
        header('Status: 503 Service Temporarily Unavailable');
        header('Retry-After: 120');

        die ($error);
    }

    /**
     * Выполнение запроса к БД (переназначение метода EasyDB->run)
     *
     * @param string $statement
     * @param mixed ...$params
     * @return array|bool|int|mixed|object
     */
    public static function query(string $statement, ...$params)
    {
        return self::$driver->run($statement, ...$params);
    }

    /**
     * Обертка методов класса EasyDB
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([self::$driver, $name], $arguments);
    }

}
