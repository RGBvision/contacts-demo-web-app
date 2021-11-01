<?php

class Dir
{
	protected function __construct()
	{
		//--
	}


    /**
     * Создать директорию
     *
     * @param string $dir путь
     * @param int $chmod права
     * @return bool
     */
    public static function create(string $dir, int $chmod = 0775): bool
    {
		return (!self::exists($dir))
			? @mkdir($dir, $chmod, true)
			: true;
	}


    /**
     * Проверка существования директории
     *
     * @param string $dir путь
     * @return bool
     */
    public static function exists(string $dir): bool
    {
		return (file_exists($dir) && is_dir($dir));
	}


	/**
     * Получить значение прав на директорию
     *
     * @param string $dir путь
     * @return int
     */
    public static function checkPerm(string $dir): int
	{
		clearstatcache();
		return (int)substr(sprintf('%o', fileperms($dir)), -4);
	}

    /**
     * Удалить директорию и всё её содержимое
     *
     * @param string $dir путь
     */
    public static function delete(string $dir): void
    {

		if (is_dir($dir)) {
			$elements = scandir($dir);

			foreach ($elements as $element) {
				if ($element !== '.' && $element !== '..') {
					if (filetype($dir . '/' . $element) === 'dir') {
                        self::delete($dir . '/' . $element);
                    } else {
                        unlink($dir . '/' . $element);
                    }
				}
			}
		}

		reset($elements);
		rmdir($dir);
	}

    /**
     * Получить содержимое директории
     *
     * @param string $dir путь
     * @return array
     */
    public static function scan(string $dir): array
    {

		if (is_dir($dir) && $handle = opendir($dir)) {
			$files = [];

			while ($element = readdir($handle)) {
				if ($element !== '.' && $element !== '..' && is_dir($dir . DS . $element)) {
                    $files[] = $element;
                }
			}

			return $files;
		}

		return [];
	}

    /**
     * Проверить директорию на возможность записи
     *
     * @param string $path путь
     * @return bool
     */
    public static function writable(string $path): bool
    {

		$file = tempnam($path, 'writable');

		if ($file !== false) {
			File::delete($file);

			return true;
		}

		return false;
	}

    /**
     * Получить размер содержимого директории
     *
     * @param string $path путь
     * @return int
     */
    public static function size(string $path): int
	{

		$total_size = 0;
		$files = scandir($path);
		$clean_path = rtrim($path, '/') . '/';

		foreach ($files as $t) {
			if ($t !== '.' && $t !== '..') {
				$current_file = $clean_path . $t;

				if (is_dir($current_file)) {
					$total_size += self::size($current_file);
				} else {
					$total_size += filesize($current_file);
				}
			}
		}

		return $total_size;
	}

    /**
     * Копировать директорию
     *
     * @param string $src путь источника
     * @param string $dst путь назначения
     */
    public static function copy(string $src, string $dst): void
    {
		$dir = opendir($src);

        if (!mkdir($dst) && !is_dir($dst)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $dst));
        }

		while (false !== ($file = readdir($dir))) {
			if (($file !== '.') && ($file !== '..')) {
				if (is_dir($src . '/' . $file)) {
					self::copy($src . '/' . $file, $dst . '/' . $file);
				} else {
					copy($src . '/' . $file, $dst . '/' . $file);
				}
			}
		}

		closedir($dir);
	}
}