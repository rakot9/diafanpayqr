<?php

require_once __DIR__ . "/PayqrConfig.php"; // подключаем основной класс

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

Custom::inc('includes/session.php');
$diafan->_session = new Session($diafan);
$diafan->_session->init();


switch($_GET['action']) {

    case 'clear_cart':
        //производим очситку корзины
        //PayqrLog::log("payqr_ajax.php Очистка корзины!");
        echo "payqr_ajax.php Очистка корзины! " . PHP_EOL ;

        //print_r($_SESSION);
        //print_r($diafan);

        DB::query("DELETE FROM {shop_cart} WHERE user_id=%d AND trash='0'", $diafan->_users->id);
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
    case 'get_cart_button':

        //товары в корзине
        $products = array();

        //формируем кнопку
        $this->diafan->_site->module = 'cart';
        $this->diafan->current_module = 'cart';
        Custom::inc('modules/cart/cart.php');
        $cart = new Cart($this->diafan);
        $cart_products = $cart->model->form_table();

        $discount = isset($cart_products['discount_total']['discount'])? $cart_products['discount_total']['discount'] : 0;
        $is_percent = false;
        if(strpos($discount ,'%') !== false)
        {
            //скидка в %
            $discount = str_replace('%', '', $discount);
            $discount = trim($discount);
            $discount = (int)$discount;
            $is_percent = true;
        }

        foreach($cart_products['rows'] as $product)
        {
            $position_amount = $product['summ']? str_replace('&nbsp;', '', $product['summ']) : 0;
            $position_amount = round((float)$position_amount, 2);
            $position_amount = $is_percent? ($position_amount * ((100 - $discount)/100)) : $position_amount;

            $productId = explode("_", $product['id']);

            $products[] = array(
                "article"  => isset($productId[0])? (int)$productId[0] : $product['id'],
                "name"     => $product['name'],
                "imageUrl" => (isset($product['img'], $product['img']['src']) && !empty($product['img']['src']))? $product['img']['src'] : "",
                "quantity" => $product['count'],
                "amount"   => round($position_amount)
            );
        }

        $button = new PayqrButtonGenerator($products);
        echo $button->getCartButton();
        //
        break;
}