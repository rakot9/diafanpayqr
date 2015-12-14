<?php

//подключаем diafan
define('DIAFAN', 1);
define('TITLE', "Регистрация на сайте");
define('_LANG', 1);
define('IS_ADMIN', 0);
define('REVATIVE_PATH', '');
define('BASE_PATH', "http".(! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? "s" : '')."://".getenv("HTTP_HOST")."/".(REVATIVE_PATH ? REVATIVE_PATH.'/' : ''));
define('ABSOLUTE_PATH', dirname(__FILE__).'/../');
define('BASE_PATH_HREF', BASE_PATH . "registration/");
//define('BASE_URL', ($domain ? $domain : getenv("HTTP_HOST")).(REVATIVE_PATH ? '/'.REVATIVE_PATH : ''));
define('BASE_URL', getenv("HTTP_HOST") . (REVATIVE_PATH ? '/'.REVATIVE_PATH : ''));

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

switch($_GET['action']) {

    case 'clear_cart':
        //производим очситку корзины
        DB::query("DELETE FROM {shop_cart} WHERE user_id=%d AND trash='0'", $this->diafan->_users->id);
        //Очищаем сессию
        if(isset($_SESSION["cart"]))
        {
            unset($_SESSION["cart"]);
        }
        if(isset($_SESSION["cart_summ"]))
        {
            unset($_SESSION["cart_summ"]);
        }
        if(isset($_SESSION["cart_count"]))
        {
            unset($_SESSION["cart_count"]);
        }
        break;
}