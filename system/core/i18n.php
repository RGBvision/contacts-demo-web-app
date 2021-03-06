<?php

class i18n
{

    private static $_path = null;
    private static $_language = null;
    private static $_fallbackLanguage = 'en';
    private static $_translation = array();
    private static $_missingTranslation = array(); // ToDo: Log missing translations

    //--- Init i18n static class
    public static function init($path = null, $language = 'en'): void
    {
        self::$_language = $language;
        self::$_path = $path;
        self::loadTranslation();
    }

    //--- Change default language and fallback language
    public static function setLanguage($language, $fallback = null): void
    {
        self::$_language = $language;

        if (!empty($fallback)) {
            self::$_fallbackLanguage = $fallback;
        }

        self::loadTranslation();
    }

    //--- Get list of missing translations
    public static function getMissingTranslations(): array
    {
        return self::$_missingTranslation;
    }

    //--- Check if translated string is available
    public static function exist($key)
    {
        $return = self::_getKey($key);

        if ($return) {
            $return = true;
        }

        return $return;
    }

    //--- Get translation for given key
    public static function _(string $key): string
    {
        $return = self::_getKey($key);

        if (!$return) {
            self::$_missingTranslation[] = array('language' => self::$_language, 'key' => $key);
            $return = $key;
        }

        return $return;
    }

    //--- Add new path
    public static function addPath(string $path): void
    {
        self::$_path = $path;
        self::loadTranslation();
    }

    //--- Loads translation(s)
    private static function loadTranslation(): void
    {

        $_translation_files = self::$_path . '*.' . self::$_language . '.lang.json';
        $_fallback_files = self::$_path . '*.' . self::$_fallbackLanguage . '.lang.json';

        $dir = glob($_translation_files);

        if (count($dir) === 0) {
            $dir = glob(BASE_DIR . $_fallback_files);
            if (count($dir) === 0) {
                throw new RuntimeException('Translation file not found');
            }
        }

        $translations =array();

        foreach ($dir as $file) {
            $translation = file_get_contents($file);
            $translation = json_decode($translation, true);

            if ($translation === null) {
                throw new RuntimeException('Invalid json ' . $file);
            }

            $translations[] = $translation;
        }

        self::$_translation = array_merge(self::$_translation, ...$translations);
        unset($translations);

    }

    //--- Get translation for given key
    private static function _getKey(string $key)
    {
        return Arrays::get(self::$_translation, $key);
    }
}
