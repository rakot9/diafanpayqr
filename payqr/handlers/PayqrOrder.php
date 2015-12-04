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
    private $deliveryData;

    /**
     * @param PayqrInvoice $invoice
     * @param $diafan
     */
    public function __construct(PayqrInvoice &$invoice, $diafan)
    {
        $this->invoice = $invoice;
        $this->customerData = $invoice->getCustomer();
        $this->diafan = $diafan;
        $this->deliveryData = $invoice->getDelivery();
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
        //$this->_dfnActualizeCart();
        
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
            $totalAmount += $product->{'amount'};
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
        $this->diafan->_site->module = 'cart';
        $this->diafan->current_module = 'cart';
        Custom::inc('modules/cart/cart.php');
        $cart = new Cart($this->diafan);

        $params = $cart->model->get_params(array("module" => "shop", "table" => "shop_order", "where" => "show_in_form='1'", "fields" => "info"));

        $status_id = DB::query_result("SELECT id FROM {shop_order_status} WHERE status='0' LIMIT 1");

        $order_id = DB::query("INSERT INTO {shop_order} (user_id, created, status, status_id, lang_id) VALUES (%d, %d, '0', %d, %d)",
            $userId,
            time(),
            $status_id,
            _LANG
        );

        PayqrLog::log("Создали заказ: " . $order_id);

        // товары
        $goods_summ = 0;
        $summ = $this->getTotalAmount();

        foreach($this->invoice->getCart() as $product)
        {
            $shop_good_id = DB::query("INSERT INTO {shop_order_goods} (order_id, good_id, count_goods) VALUES (%d, %d, %f)", $order_id, (int)$product->article, (int)$product->quantity);

            PayqrLog::log("Вставили товар и получили идентификатор товара в {shop_order_goods}: " . $shop_good_id);

            $row = $this->diafan->_shop->price_get((int)$product->article, array());

            DB::query("UPDATE {shop_order_goods} SET price=%f, discount_id=%d WHERE id=%d", $row["price"], $row["discount_id"], $shop_good_id);

            $goods_summ += round((float)$row["price"] * (int)$product->quantity , 2);
        }
        
        if($discount = $this->get_discount_total($goods_summ, $userId))
        {
            $summ -= $discount["discount_summ"];
        }
        else
        {
            $discount["discount_summ"] = 0;
            $discount["discount_id"] = 0;
        }

        PayqrLog::log("Получили скидки: " . print_r($discount, true));

        DB::query("UPDATE {shop_order} SET summ=%f, discount_id=%d, discount_summ=%f WHERE id=%d", $goods_summ, $discount["discount_id"], $discount["discount_summ"], $order_id);

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
     * Получает скидку от общей суммы товаров
     *
     * @return float
     */
    private function get_discount_total($cart_summ, $userId = null)
    {
        PayqrLog::log("get_discount_total");

        $discount = false;
        $order_summ = 0;
        if($userId)
        {
            $order_summ = DB::query_result("SELECT SUM(summ) FROM {shop_order} WHERE user_id=%d AND (status='1' OR status='3')", $userId);
        }

        PayqrLog::log("Order sum : " . $order_summ);


        //скидка на общую сумму заказа
        $person_discount_ids = $this->diafan->_shop->price_get_person_discounts();
        PayqrLog::log("Получили скидки клиенту: ", print_r($person_discount_ids, true));


        $userRoleId = dfnUserAuth::getInstance($this->diafan)->_dfnGetUserRoleId($userId);

        PayqrLog::log("Получили роль пользователя: " . $userRoleId);

        $rows = DB::query_fetch_all("SELECT id, discount, amount, deduction, threshold, threshold_cumulative FROM"
            ." {shop_discount} WHERE act='1' AND trash='0' AND (threshold_cumulative>0 OR threshold>0)"
            ." AND role_id".($userRoleId ? ' IN (0, '.$userRoleId.')' : '=0')
            ." AND (person='0'".($person_discount_ids ? " OR id IN(".implode(",", $person_discount_ids).")" : "").")"
            ." AND date_start<=%d AND (date_finish=0 OR date_finish>=%d)"
            ." AND (threshold_cumulative>0 AND threshold_cumulative<=%f"
            ." OR threshold>0 AND threshold<=%f)",
            time(), time(), $order_summ, $cart_summ
        );

        PayqrLog::log("После получения скидки: ", print_r($rows, true));

        foreach ($rows as $row)
        {
            $row["discount_id"] = $row["id"];
            if($row['deduction'])
            {
                if($row['deduction'] < $cart_summ)
                {
                    $row["discount_summ"] = $row["deduction"];
                }
                else
                {
                    $row["discount_summ"] = 0;
                }
            }
            else
            {
                $row["discount_summ"] = $cart_summ * $row["discount"] / 100;
            }
            if(empty($discount) || $discount["discount_summ"] < $row["discount_summ"])
            {
                $discount = $row;
            }
        }
        return $discount;
    }

    /**
     * Получает стоимость товара с учетом скидки
     * @param int $id
     * @return int
     */
    private function _dfnGetProductPrice($id)
    {
        Custom::inc('modules/shop/inc/shop.inc.price.php');
        $shopIncPrice = new Shop_inc_price($this->diafan);
        $price = $shopIncPrice->get((int)$id, array());
        unset($shopIncPrice);
        return (float) (isset($price['price'])? $price['price'] : 0);
    }

    /**
     * Получает наименования товара с учетом скидки
     * @param string $id
     * @return string
     */
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

    /**
     * @param int $order_id
     * 
     */
    public function updateDeliverySumm($order_id, $delivery_id, $delivery_summ)
    {
        DB::query("UPDATE {shop_order} set delivery_summ=%f, delivery_id=%d, summ=summ+%f WHERE id=%d", $delivery_summ, $delivery_id, $delivery_summ, $order_id);
    }

    /**
     * Метод устанавливает данные пользователя
     * 
     */
    public function setUserOrderData($order_id)
    {
        $diafanOrderParams = array();

        $orderParams = DB::query_fetch_all("SELECT * FROM {shop_order_param} WHERE trash='0'");

        foreach ($orderParams as $param)
        {
            $diafanOrderParams[$param["info"]] = $param["id"];
        }

        /*
        * Заполняем email пользователя
        */
        if(isset($this->customerData->email, $diafanOrderParams["email"]) && !empty($this->customerData->email))
        {
            DB::query("INSERT INTO {shop_order_param_element} (value, param_id, element_id, trash) VALUES ('%s', %d, %d, '%s')", $this->customerData->email, $diafanOrderParams["email"], $order_id, '0');
        }

        /*
        * 
        */
        if(isset($diafanOrderParams["name"]) && !empty($diafanOrderParams["name"]))
        {
            $userName = "";

            if(isset($this->customerData->lastName) && !empty($this->customerData->lastName))
            {
                $userName .= $this->customerData->lastName;
            }

            if(isset($this->customerData->firstName) && !empty($this->customerData->firstName))
            {
                $userName .= " ". $this->customerData->firstName;
            }

            if(isset($this->customerData->middleName) && !empty($this->customerData->middleName))
            {
                $userName .= " ". $this->customerData->middleName;
            }

            if(!empty($userName))
            {
                DB::query("INSERT INTO {shop_order_param_element} (value, param_id, element_id, trash) VALUES ('%s', %d, %d, '%s')", $userName, $diafanOrderParams["name"], $order_id, '0');
            }
        }

        /*
        * 
        */
        if(isset($diafanOrderParams["phone"], $this->customerData->phone) && !empty($this->customerData->phone))
        {
            DB::query("INSERT INTO {shop_order_param_element} (value, param_id, element_id, trash) VALUES ('%s', %d, %d, '%s')", $this->customerData->phone, $diafanOrderParams["phone"], $order_id, '0');
        }

        /*
        * 
        */
        if(isset($diafanOrderParams["zip"], $this->deliveryData->zip) && !empty($this->deliveryData->zip))
        {
            DB::query("INSERT INTO {shop_order_param_element} (value, param_id, element_id, trash) VALUES ('%s', %d, %d, '%s')", $this->deliveryData->zip, $diafanOrderParams["zip"], $order_id, '0');
        }

        /*
        * 
        */
        if(isset($this->deliveryData->city, $diafanOrderParams["city"]) && !empty($this->deliveryData->city))
        {
            DB::query("INSERT INTO {shop_order_param_element} (value, param_id, element_id, trash) VALUES ('%s', %d, %d, '%s')", $this->deliveryData->city, $diafanOrderParams["city"], $order_id, '0');
        }

        /*
        * 
        */
        if(isset($this->deliveryData->street, $diafanOrderParams["street"]) && !empty($this->deliveryData->street))
        {
            DB::query("INSERT INTO {shop_order_param_element} (value, param_id, element_id, trash) VALUES ('%s', %d, %d, '%s')", $this->deliveryData->street, $diafanOrderParams["street"], $order_id, '0');
        }

        /*
        * 
        */
        if(isset($this->deliveryData->house, $diafanOrderParams["building"]) && !empty($this->deliveryData->house))
        {
            DB::query("INSERT INTO {shop_order_param_element} (value, param_id, element_id, trash) VALUES ('%s', %d, %d, %d)", $this->deliveryData->house, $diafanOrderParams["building"], $order_id, 0);
        }

        /*
        * 
        */
        if(isset($this->deliveryData->unit, $diafanOrderParams["suite"]) && !empty($this->deliveryData->unit))
        {
            DB::query("INSERT INTO {shop_order_param_element} (value, param_id, element_id, trash) VALUES ('%s', %d, %d, %d)", $this->deliveryData->unit, $diafanOrderParams["suite"], $order_id, 0);
        }

        /*
        * 
        */
        if(isset($this->deliveryData->flat, $diafanOrderParams["flat"]) && !empty($this->deliveryData->flat))
        {
            DB::query("INSERT INTO {shop_order_param_element} (value, param_id, element_id, trash) VALUES ('%s', %d, %d, %d)", $this->deliveryData->flat, $diafanOrderParams["flat"], $order_id, 0);
        }
    }
}
