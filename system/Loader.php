<?php


class Loader
{
    protected static $classes = array();
    protected static $directories = array();
    protected static $namespaces = array();
    protected static $aliases = array();

    protected function __construct()
    {
        //
    }

    /**
     * Добавить класс в автозагрузку
     *
     * @param string $className имя класса
     * @param string $classPath путь к файлу класса
     */
    public static function addClass(string $className, string $classPath): void
    {
        self::$classes[$className] = $classPath;
    }

    /**
     * Добавить классы из массива в автозагрузку
     *
     * @param array $classes
     */
    public static function addClasses(array $classes): void
    {
        foreach ($classes as $name => $path) {
            self::$classes[$name] = $path;
        }
    }

    /**
     * Включить и выполнить PHP файлы по указанному пути
     *
     * @param string $path путь к директории с файлами
     */
    public static function addFiles(string $path): void
    {
        $files = glob($path . '/*.php');

        foreach ($files as $file) {
            require($file);
        }
    }

    /**
     * Загрузить модули системы
     *
     * @param string $path путь к папке с модулями
     * @return array
     */
    public static function addModules(string $path = ''): array
    {
        $dir = dir($path . '/modules');

        $modules = [];

        while (false !== ($entry = $dir->read())) {
            if (strpos($entry, '.') === 0) {
                continue;
            }

            $module_dir = $dir->path . '/' . $entry;

            if (!is_dir($module_dir)) {
                continue;
            }

            if (!(is_file($module_dir . '/Module.php') && include_once($module_dir . '/Module.php'))) {
                $modules['errors'][] = $entry;
                continue;
            }

            $module_name = 'Module' . $entry;
            new $module_name();
        }

        $dir->Close();

        return $modules;
    }

    /**
     * Добавить все классы из папки в автозагрузку
     *
     * @param string $path путь к папке с классами
     */
    public static function addDirectory(string $path): void
    {
        self::$directories[] = rtrim($path, '/');
    }

    /**
     * Зарегистрировать пространство имен
     *
     * @param string $namespace имя
     * @param string $path путь
     */
    public static function regNamespace(string $namespace, string $path): void
    {
        self::$namespaces[trim($namespace, '\\') . '\\'] = rtrim($path, '/');
    }

    /**
     * Добавить псевдоним для класса
     *
     * @param string $alias псевдоним
     * @param string $className имя класса
     */
    public static function addAlias(string $alias, string $className): void
    {
        self::$aliases[$alias] = $className;
    }

    /**
     * Проверить соответствие PSR0
     *
     * @param string $className имя класса
     * @param string|null $directory папка
     * @return bool
     */
    protected static function PSR0(string $className, ?string $directory = null): bool
    {
        $classPath = '';

        if (($pos = strripos($className, '\\')) !== false) {
            $namespace = substr($className, 0, $pos);
            $className = substr($className, $pos + 1);
            $classPath = str_replace('\\', '/', $namespace) . '/';
        }

        $classPath .= str_replace('_', '/', $className) . '.php';

        $directories = ($directory === null)
            ? self::$directories
            : [$directory];

        foreach ($directories as $_directory) {
            if (file_exists($_directory . '/' . $classPath)) {
                include($_directory . '/' . $classPath);

                return true;
            }
        }

        return false;
    }

    /**
     * Загрузить класс
     *
     * @param string $className имя класса
     * @return bool
     */
    public static function loadClass(string $className): bool
    {

        $className = ltrim($className, '\\');

        if (isset(self::$aliases[$className])) {
            return class_alias(self::$aliases[$className], $className);
        }

        if (isset(self::$classes[$className]) && file_exists(self::$classes[$className])) {
            include self::$classes[$className];

            return true;
        }

        foreach (self::$namespaces as $namespace => $path) {
            if ((strpos($className, $namespace) === 0) && self::PSR0(substr($className, strlen($namespace)), $path)) {
                return true;
            }
        }

        return self::PSR0($className) || self::PSR0(strtolower($className));
    }

    /**
     * Инициализация загрузчика
     */
    public static function init(): void
    {
        spl_autoload_register('self::loadClass', true);
    }

}