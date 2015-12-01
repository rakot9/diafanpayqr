<?php
/**
 * Автозагрузка классов библиотеки PayQR
 */
if (version_compare(PHP_VERSION, '5.0.0') < 0) {
  die("Для вашей версии PHP (".phpversion().") необходимо использовать другую версию библиотеки PayQR");
}
PayqrAutoload::Register();

class PayqrAutoload
{
    /**
     * Регистрация автозагрузчика со стандартной библиотекой PHP (SPL)
     */
    public static function Register()
    {
        if (function_exists('__autoload')) {
            // Register any existing autoloader function with SPL, so we don't get any clashes
            spl_autoload_register('__autoload');
        }
        // Register ourselves with SPL
        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            return spl_autoload_register(array('PayqrAutoload', 'Load'), true, true);
        } else {
            return spl_autoload_register(array('PayqrAutoload', 'Load'));
        }
    }

    /**
     * Autoload a class identified by name
     *
     * @param string $pClassName Name of the object to load
     */
    public static function Load($pClassName)
    {
        $files = self::getFiles();
        foreach ($files as $file)
        {
            if(strpos($file, $pClassName) !== false && is_readable($file))
            {
                require_once $file;
            }
        }
    }
    /*
     * Получить список всех файлов в директории payqr
     */
    private static function getFiles($pattern = "") 
    {
        if(empty($pattern))
        {
            $pattern = PAYQR_ROOT . "*";
        }
        $files = glob($pattern);
        foreach (glob($pattern, GLOB_ONLYDIR|GLOB_NOSORT) as $dir)
        {
            $files = array_merge($files, self::getFiles($dir.'/'.basename($pattern)));
        }
        return $files;
    }
} 