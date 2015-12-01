<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Класс для создания заказа
 *
 * @author 1
 */
class PayqrOrder 
{
    private $invoice;
    private $customerData;
    private $diafan;

    /**
     * @param PayqrInvoice $invoice
     * @param Diafan $diafan
     */
    public function __construct(PayqrInvoice &$invoice, Diafan $diafan)
    {
        $this->invoice = $invoice;
        $this->customerData = $invoice->getCustomer();
        $this->diafan = $diafan;
    }

    /**
     * Создание заказа
     * @return int
     */
    public function createOrder()
    {
        /**
        *  Актуализируем корзину
        */
        $this->_dfnActualizeCart();
        
        /**
         * Создаем заказ
         */
        return $this->_dfnCreateOrder();
    }

    /**
     * создаем суммарную стоимость заказа
     * @return float
     */
    public function getTotalAmount()
    {
        $totalAmount = 0;

        $products = $this->invoice->getCart();

        foreach($products as $product)
        {
            $totalAmount += $product['amount'];
        }

        return round($totalAmount, 2);
    }
    
    /**
     * Создает заказ с проверкой пользователя на существование
     * @return int
     */
    private function _dfnCreateOrder()
    {
        /**
         * Получаем информацию о пользователе
         */
        $userId = dfnUserAuth::getInstance($this->diafan)->getUserId($this->customerData->email);

        if(!$userId)
        {
            PayqrLog::log("Не смогли получить информацию о пользвателе");

            $userId = dfnUserAuth::getInstance($this->diafan)->CreateUser($this->customerData->email);

            /**
            * создаем пользователя
            */
            PayqrLog::log("Создали нового пользоваеля с email: ".$this->customerData->email);
        }

        PayqrLog::log("Получили информацию по пользователю (" . $this->customerData->email . "): " . $userId);

        /**
         * Создаем заказ на основе корзины
         */
        // foreach($this->invoice->getCart() as $product)
        // {
        //     $product->amount;
        //     $product->article;
        //     $product->name;
        //     $product->imageURL;
        //     $product->quantity;
        // }

        //создаем заказ
        $this->diafan->_site->module = 'cart';
        $this->diafan->current_module = 'cart';
        Custom::inc('modules/cart/cart.php');
        $cart = new Cart($this->diafan);

        $params = $cart->model->get_params(array("module" => "shop", "table" => "shop_order", "where" => "show_in_form='1'", "fields" => "info"));

        PayqrLog::log("Получили параметры params: " . print_r($params, true));
        PayqrLog::log("Получили параметры unserialize(params): " . print_r(unserialize($params), true));

        $status_id = DB::query_result("SELECT id FROM {shop_order_status} WHERE status='0' LIMIT 1");

        PayqrLog::log("Получили статус заказа: " . $status_id);

        $order_id = DB::query("INSERT INTO {shop_order} (user_id, created, status, status_id, lang_id) VALUES (%d, %d, '0', %d, %d)",
            $userId,
            time(),
            $status_id,
            _LANG
        );

        PayqrLog::log("Создали заказ: " . $order_id);

        // товары
        $goods_summ = 0;

        foreach($this->invoice->getCart() as $product)
        {
            $shop_good_id = DB::query("INSERT INTO {shop_order_goods} (order_id, good_id, count_goods) VALUES (%d, %d, %f)", $order_id, $product->article, (int)$product->quantity);

            PayqrLog::log("Вставили товар и получили идентификатор товара в {shop_order_goods}: " . $shop_good_id);

            $price = $select_depend = 0;

            $sparams = unserialize($param);

            foreach ($sparams as $id => $value)
            {
                DB::query("INSERT INTO {shop_order_goods_param} (order_goods_id, value, param_id) VALUES ('%d', '%d', '%d')", $shop_good_id, $value, $id);
            }    
            $row = $this->diafan->_shop->price_get((int)$product->quantity, $sparams);

            PayqrLog::log("Получили информацию по цене товара: " . print_r($row, true));

            DB::query("UPDATE {shop_order_goods} SET price=%f, discount_id=%d WHERE id=%d", $row["price"], $row["discount_id"], $shop_good_id);

            $goods_summ += $row["price"] * $c["count"];
        }
        PayqrLog::log("Получили итоговую сумму заказа: " . $goods_summ);
        PayqrLog::log("Возвращаем идентификатор заказа");

        return $order_id;
    }

    /**
     * Производим актуализацию корзины
     * производится изменение: стоимости позиции, названия товара и URL картинки
     * @return
     * 
     */
    private function _dfnActualizeCart()
    {
        foreach($this->invoice->getCart() as $product)
        {
            if(empty($product->article) || empty(((int)$product->article)))
            {
                continue;
            }

            $price = $this->_dfnGetProductPrice((int)$product->article);

            if(empty($price) || empty($price))
            {
                continue;
            }

            $product->amount = $product->quantity * $price;
            $name = $this->_dfnGetProductName((int)$product->article);
            $product->name = !empty($name)? $name : $product->name;
        }
    }
    
    /**
     * 
     * 
     */
    private function _dfnGetProductPrice($id)
    {
        Custom::inc('modules/shop/inc/shop.inc.price.php');
        $shopIncPrice = new Shop_inc_price($this->diafan);
        $price = $shopIncPrice->get((int)$id, array());
        unset($shopIncPrice);
        return (float) (isset($price['price'])? $price['price'] : 0);
    }

    private function _dfnGetProductName($id)
    {
        $result = DB::query_fetch_array("SELECT name1 FROM {shop} WHERE id=%d ", $id);
        if($result && $result['name1'])
        {
            return $result['name1'];
        }
        return "";
    }

    /**
     * заказ оплачен уже или нет
     */
    public function getOrderPaidStatus($invoice_id)
    {        
        $db = PayqrModuleDb::getInstance();
        $invoice = $db->select("select * from ".PayqrModuleDb::getInvoiceTable()." where invoice_id=?", array($invoice_id), array("s"));
        return $invoice;
    }
}
