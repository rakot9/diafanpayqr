<?php
/**
 * Скрипт принимает и обрабатывает уведомления от PayQR
 */
require_once __DIR__ . "/PayqrConfig.php"; // подключаем основной класс
try
{
	//подключаем diafan
    define('DIAFAN', 1);
    define('TITLE', "Регистрация на сайте");
    define('_LANG', 1);
    define('IS_ADMIN', 0);
    define('REVATIVE_PATH', '');
    define('BASE_PATH', "http".(! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? "s" : '')."://".getenv("HTTP_HOST")."/".(REVATIVE_PATH ? REVATIVE_PATH.'/' : ''));
    define('ABSOLUTE_PATH', dirname(__FILE__).'/../');
    define('BASE_PATH_HREF', BASE_PATH . "registration/");
    define('BASE_URL', ($domain ? $domain : getenv("HTTP_HOST")).(REVATIVE_PATH ? '/'.REVATIVE_PATH : ''));

    include_once ABSOLUTE_PATH.'includes/custom.php';
    include_once(ABSOLUTE_PATH.'includes/developer.php');
    include_once(ABSOLUTE_PATH.'includes/diafan.php');
    include_once(ABSOLUTE_PATH.'includes/file.php');

    Dev::init();

    include_once(ABSOLUTE_PATH . 'config.php');
    include_once(ABSOLUTE_PATH . 'includes/core.php');
    include_once(ABSOLUTE_PATH . 'includes/init.php');
    $diafan = new Init();

    Custom::inc('includes/controller.php');
    Custom::inc('includes/model.php');
    Custom::inc('includes/action.php');

    Custom::inc('includes/controller.php');
    Custom::inc('includes/model.php');
    Custom::inc('includes/action.php');


    $receiver = new PayqrReceiver();
    $receiver->handle($diafan);
}
catch (PayqrExeption $e)
{
    PayqrLog::log($e->response);
}

function encrypt($text)
{
    //return md5($text);
    return $text;
}