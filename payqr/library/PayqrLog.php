<?php
/**
 * Ведение отладочных логов
 */
 
class PayqrLog
{

    /**
     * Добавление записи в лог файл
     *
     * @param $file
     * @param $message
     */
    public static function log($message)
    {
        if(!PayqrConfig::$enabledLog)
          return;
        $file = self::getLogFilePath();
        $message = str_repeat("-", 300) . "\n" . date("Y-m-d H:i:s") . "\n" . $message . "\n\n";
        file_put_contents($file, $message, FILE_APPEND);
    }
    
    public static function showLog()
    {
        $file = self::getLogFilePath();
        if(file_exists($file))
        {
            $log = file_get_contents($file);
            $text = nl2br($log);
        }
        else
        {
            $text = "Файл логов не найден";
        }
        return $text;
    }
    
    public static function getLogFilePath()
    {
        if(!empty(PayqrConfig::$logFilePath))
        {
            $path = PayqrConfig::getSiteBasePath() . PayqrConfig::$logFilePath;
        }
        else
        {
            $settings = new PayqrButtonDefaultSettings();
            $path = PayqrConfig::getSiteBasePath() . $settings->getLogPath();
        }
        return $path;
    }
} 