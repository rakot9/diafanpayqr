<?php
/**
 * Класс конфигурации
 * Подключите этот файл, чтобы обеспечить автозагрузку всех необходимых классов для работы с API PayQR
 */

if (!defined('PAYQR_ROOT')) {
    define('PAYQR_ROOT', dirname(__FILE__) . '/');
}
require(PAYQR_ROOT . 'library/PayqrAutoload.php');

class PayqrConfig
{
    // по умолчанию ниже продемонстрированы примеры значений, укажите актуальные значения для своего "Магазина"
    public static $merchantID = ""; // номер "Магазина" из личного кабинета PayQR
    
    public static $secretKeyIn = ""; // входящий ключ из личного кабинета PayQR (SecretKeyIn), используется в уведомлениях от PayQR
    
    public static $secretKeyOut = ""; // исходящий ключ из личного кабинета PayQR (SecretKeyOut), используется в запросах в PayQR
    
    public static $logKey = ""; // Ключ доступа к логам

    public static $logFile =  "logs/payqr.log"; // имя файла логов библиотеки PayQR

    public static $logFilePath =  ""; // путь к логам, если пустой, используется по умолчанию

    public static $enabledLog = true; // разрешить библиотеке PayQR вести лог

    public static $maxTimeOut = 10; // максимальное время ожидания ответа PayQR на запрос интернет-сайта в PayQR

    public static $checkHeader = true; // проверять секретный ключ SecretKeyIn в уведомлениях и ответах от PayQR

    public static $version_api = '2.0.0'; // версия библиотеки PayQR
    
    public static $baseUrl = "payqr";
    
    public static function getSiteBasePath()
    {
        $basepath = __DIR__ ;
        $deep = count(explode("/", self::$baseUrl));
        for($i=0; $i<$deep; $i++)
        {
            $basepath .= "/../";
        }
        $basepath = realpath($basepath) . "/";
        return $basepath;
    }
}
