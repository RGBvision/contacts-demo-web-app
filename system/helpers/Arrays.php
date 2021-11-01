<?php

class Arrays
{

    protected function __construct()
    {
        //
    }


    /**
     * Получить значение по ключу или пути
     *
     * @param array $array массив, в котором осуществляется поиск
     * @param string $path ключ или путь
     * @param string $glue разделитель пути
     * @param null $default значение по умолчанию, если значение не найдено
     * @return array|mixed|null
     */
    public static function get(array &$array, string $path, string $glue = '.', $default = null)
    {
        $path_chunks = explode($glue, $path);
        $ref = &$array;

        foreach ($path_chunks as $chunk) {
            if (is_array($ref) && array_key_exists($chunk, $ref)) {
                $ref = &$ref[$chunk];
            } else {
                return $default;
            }
        }

        return $ref;
    }


    /**
     * Установить значение по ключу или пути
     *
     * @param array $array массив
     * @param string $path ключ или путь
     * @param array|mixed $value значение
     * @param string $glue разделитель пути
     */
    public static function set(array &$array, string $path, $value, string $glue = '.'): void
    {
        $path_chunks = explode($glue, $path);
        $ref = &$array;

        foreach ($path_chunks as $chunk) {
            if (isset($ref) && !is_array($ref)) {
                $ref = array();
            }
            $ref = &$ref[$chunk];
        }

        $ref = $value;
    }


    /**
     * Удалить значение по ключу или пути
     *
     * @param array $array массив
     * @param string $path ключ или путь
     * @param string $glue разделитель пути
     */
    public static function delete(array &$array, string $path, string $glue = '.'): void
    {
        $path_chunks = explode($glue, $path);
        $key = array_shift($path_chunks);

        if (empty($path_chunks)) {
            unset($array[$key]);
        } else {
            self::delete($array[$key], implode($glue, $path_chunks));
        }
    }

    /**
     * Получить объект из массива
     *
     * @param array|mixed $array массив для преобразования
     * @return StdClass
     */
    public static function toObject($array): StdClass
    {
        if (is_array($array)) {
            $obj = new StdClass();

            foreach ($array as $key => $val) {
                $obj->$key = $val;
            }
        } else if (is_object($array)) {
            $obj = $array;
        } else {
            $obj = null;
        }

        return $obj;
    }

    /**
     * Получить массив из объекта
     *
     * @param StdClass|mixed $object
     * @return array
     * @throws ReflectionException
     */
    public static function toArray($object): array
    {

        if (is_object($object)) {

            $reflectionClass = new ReflectionClass(get_class($object));
            $array = [];
            foreach ($reflectionClass->getProperties() as $property) {
                $property->setAccessible(true);
                if ($property->isPublic()) {
                    $array[$property->getName()] = $property->getValue($object);
                }
                $property->setAccessible(false);
            }
            return $array;
        }

        $object = (array)$object;

        if ($object === []) {
            return $object;
        }

        foreach ($object as $key => &$value) {
            if ((is_object($value) || is_array($value))) {
                $object[$key] = self::toArray($value);
            }
        }

        return $object;

    }


    /**
     * Мультисортировка массива по ключу
     *
     * @param array $array
     * @param $key
     * @param int $sort_way
     * @return array
     */
    public static function multiSort(array $array, $key, int $sort_way = SORT_ASC): array
    {
        $keys = array_column($array, $key);
        array_multisort($keys, $sort_way, $array);
        return $array;
    }


    /**
     * Сериализация массива для безопасного хранения в БД
     *
     * @param array $array
     * @return string
     */
    public static function safe_serialize(array $array): string
    {
        return base64_encode(serialize($array));
    }


    /**
     * Десериализация массива
     *
     * @param string $string
     * @return array
     */
    public static function safe_unserialize(string $string): array
    {
        return unserialize(base64_decode($string), ['allowed_classes' => false]) ?: [];
    }
}