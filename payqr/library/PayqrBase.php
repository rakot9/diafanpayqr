<?php

/**
 * Проверка конфигурации библиотеки PayQR
 */
class PayqrBase
{
    public static $apiUrl = "https://payqr.ru/shop/api/1.0"; // Не меняйте этот адрес без получения официальных извещений PayQR
    
    
    public static function checkConfig()
    {
        if (PayqrConfig::$secretKeyIn == "") {
            throw new PayqrExeption("Поле PayqrConfig::secretKeyIn не может быть пустым, проверьте конфигурацию библиотеки PayQR");
        }
        if (PayqrConfig::$secretKeyOut == "") {
            throw new PayqrExeption("Поле PayqrConfig::secretKeyOut не может быть пустым, проверьте конфигурацию библиотеки PayQR");
        }
        if (PayqrConfig::$enabledLog && PayqrConfig::$logFile == "") {
            throw new PayqrExeption("Поле PayqrConfig::logFile не может быть пустым, проверьте конфигурацию библиотеки PayQR");
        }
        if (PayqrConfig::$merchantID == "") {
            throw new PayqrExeption("Поле PayqrConfig::merchantID не может быть пустым, проверьте конфигурацию библиотеки PayQR");
        }
    }

    /**
     * Эквивалент apache_request_headers()
     * @return mixed
     */
    public static function getallheaders()
    {
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $name = str_replace(' ', '-', str_replace('_', ' ', substr($name, 5)));
                $headers[$name] = $value;
            } 
            else {
                if ($name == "CONTENT_TYPE") {
                    $headers["Content-Type"] = $value;
                } 
                else {
                    if ($name == "CONTENT_LENGTH") {
                        $headers["Content-Length"] = $value;
                    }
                }
            }
        }
        return $headers;
    }
} 