<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of InvoiceHandler
 *
 * @author 1
 */
class InvoiceHandler 
{
    private $invoice;
    private $diafan;
    
    public function __construct(PayqrInvoice $invoice, Diafan $diafan) 
    {
        $this->invoice = $invoice;
        $this->diafan = $diafan;
    }    
    
    /*
    * Код выполнен, когда интернет-сайт получит уведомление от PayQR о необходимости создания заказа в учетной системе интернет-сайта.
    * Это означает, что покупатель приблизился к этапу оплаты, а, значит, интернет-сайту нужно создать заказ в своей учетной системе, если такой заказ еще не создан, и вернуть в ответе PayQR значение orderId в объекте "Счет на оплату", если orderId там еще отсутствует.
    *
    * $this->invoice содержит объект "Счет на оплату" (подробнее об объекте "Счет на оплату" на https://payqr.ru/api/ecommerce#invoice_object)
    *
    * Ниже можно вызвать функции своей учетной системы, чтобы особым образом отреагировать на уведомление от PayQR о событии invoice.order.creating.
    *
    * Важно: после уведомления от PayQR об invoice.order.creating в содержании конкретного объекта "Счет на оплату" должен быть обязательно заполнен параметр orderId (если он не заполнялся на уровне кнопки PayQR). По правилам PayQR оплата заказа не может быть начата до тех пор, пока в счете не появится номер заказа (orderId). Если интернет-сайт не ведет учет заказов по orderId, то на данном этапе можно заполнить параметр orderId любым случайным значением, например, текущими датой и временем. Также важно, что invoice.order.creating является первым и последним этапом, когда интернет-сайт может внести коррективы в параметры заказа (например, откорректировать названия позиций заказа).
    *
    * Часто используемые методы на этапе invoice.order.creating:
    *
    * * Получаем объект адреса доставки из "Счета на оплату"
    * $this->invoice->getDelivery();
    * * вернет:
    * "delivery": { "country": "Россия", "region": "Москва", "city": "Москва", "zip": "115093", "street": "Дубининская ул.", "house": "80", "comment": "У входа в автосалон Хонда", }
    *
    * * Получаем объект содержания корзины из "Счета на оплату"
    * $this->invoice->getCart();
    * * вернет:
    * [{ "article": "5675657", "name": "Товар 1", "imageUrl": "http://goods.ru/item1.jpg", "quantity": 5, "amount": 19752.25 }, { "article": "0", "name": "PROMO акция", "imageUrl": "http://goods.ru/promo.jpg", }]
    *
    * * Обновляем содержимое корзины в объекте "Счет на оплату" в PayQR
    * $this->invoice->setCart($cartObject);
    *
    * * Получаем объект информации о покупателе из "Счета на оплату"
    * $this->invoice->getCustomer();
    * * вернет:
    * { "firstName": "Иван", "lastName": "Иванов", "phone": "+79111111111", "email": "test@user.com" }
    *
    * * Устанавливаем orderId из учетной системы интернет-сайта в объекте "Счет на оплату" в PayQR
    * $this->invoice->setOrderId($orderId);
    *
    * * Получаем сумму заказа из "Счета на оплату"
    * $this->invoice->getAmount();
    *
    * * Изменяем сумму заказа в объекте "Счет на оплату" в PayQR (например, уменьшаем сумму, чтобы применить скидку)
    * $this->invoice->setAmount($amount);
    *
    * * Если по каким-то причинам нам нужно отменить этот заказ сейчас (работает только при обработке события invoice.order.creating)
    * $this->invoice->cancelOrder(); вызов этого метода отменит заказ
    */
    public function createOrder()
    {
        $order = new PayqrOrder($this->invoice, $this->diafan);

        $orderId = $order->createOrder();
        PayqrLog::log("Создали заказ:" . $orderId);
        
        $this->invoice->setOrderId($orderId);
        
        PayqrLog::log("Получаем invoice_id");
        $invoice_id = $this->invoice->getInvoiceId();
        
        PayqrLog::log("Получили invoice_id: ". $invoice_id);

        if($order->getOrderPaidStatus($invoice_id))
        {
            
        }
        
        /**
         * Устанавливаем общую стоимость заказа
         */
        PayqrLog::log("Устанавливаем сумму заказа");
        $total = $order->getTotalAmount();
        $this->invoice->setAmount($total);
        PayqrLog::log("Установили сумму заказа: ". $total);

        //отправка сообщений
        // $module = new PayqrModule();
        // if($module->getOption("message-invoice-order-creating"))
        // {
        //     $message = $this->invoice->getMessage();
        //     if($message)
        //     {
        //         $message->article = $module->getOption("message-invoice-order-creating-article");
        //         $message->text = $module->getOption("message-invoice-order-creating-text");
        //         $message->imageUrl = $module->getOption("message-invoice-order-creating-imageUrl");
        //         $message->url = $module->getOption("message-invoice-order-creating-url");
        //         $this->invoice->setMessage($message);
        //     }
        // }
        
        //сохраняем заказ
        $db = PayqrModuleDb::getInstance();
        $id = $db->insert(PayqrModuleDb::getInvoiceTable(), 
            array(
                "invoice_id" => $this->invoice->getInvoiceId(), 
                "order_id" => $orderId, 
                "invoice_type" => "invoice.order.creating",
            ),
            array("%s", "%s", "%s")
        );
        
        //сохраняем логи
        $id = $db->insert(PayqrModuleDb::getLogTable(), 
            array(
                "event_id" => $this->invoice->getInvoiceId(), 
                "order_id" => $orderId, 
                "event_type" => "invoice.order.creating", 
                "payqr_number" => $this->invoice->getPayqrNumber(),
                "datetime" => date("Y-m-d H:i:s"),
                "data" => file_get_contents("php://input")
            ),
            array("%s", "%s", "%s", "%s", "%s", "%s")
        );
        return true;
    }
    
    /**
    * Код будет выполнен, когда интернет-сайт получит уведомление от PayQR об успешной оплате конкретного заказа.
    * Это означает, что PayQR успешно списал запрошенную интернет-сайтом сумму денежных средств с покупателя и перечислит ее интернет-сайту в ближайшее время, интернет-сайту нужно исполнять свои обязанности перед покупателем, т.е. доставлять товар или оказывать услугу. 
    *
    * $this->invoice содержит объект "Счет на оплату" (подробнее об объекте "Счет на оплату" на https://payqr.ru/api/ecommerce#invoice_object)
    *
    * Ниже можно вызвать функции своей учетной системы, чтобы особым образом отреагировать на уведомление от PayQR о событии invoice.paid.
    *
    * Получить orderId из объекта "Счет на оплату", по которому произошло событие, можно через $this->invoice->getOrderId();
    *
    * Важно: несмотря на то, что заказ создается на этапе получения уведомления о событии invoice.order.creating, крайне рекомендуется валидировать все содержание заказа и после получения уведомления о событии invoice.paid. А в случае, когда запрос адреса доставки у покупателя на уровне кнопки PayQR, настроен на рекомендательный режим (спрашивать после оплаты/спрашивать необязательно), то не просто рекомендуется, а обязательно, так как объект "Счет на оплату" на этапе invoice.paid будет содержать в себе расширенные окончательные данные, которых не было на invoice.order.creating. Если по результатам проверки данных из invoice.paid обнаружатся какие-то критичные расхождения (например, сумма заказа из объекта "Счет на оплату" не сходится с суммой из соответствующего заказа), можно сразу послать запрос в PayQR на отмену счету после его оплаты (возврат денег).
    */
    public function payOrder()
    {
        //отправка сообщений
        $module = new PayqrModule();
        if($module->getOption("message-invoice-paid"))
        {
            $message = $this->invoice->getMessage();
            if($message)
            {
                $message->article = $module->getOption("message-invoice-paid-article");
                $message->text = $module->getOption("message-invoice-paid-text");
                $message->imageUrl = $module->getOption("message-invoice-paid-imageUrl");
                $message->url = $module->getOption("message-invoice-paid-url");
                $this->invoice->setMessage($message);
            }
        }
    }
    
    /*
    * Код будет выполнен, когда интернет-сайт получит уведомление от PayQR о полной отмене счета (заказа) после его оплаты.
    * Это означает, что посредством запросов в PayQR интернет-сайт либо одной полной отменой, либо несколькими частичными отменами вернул всю сумму денежных средств по конкретному счету (заказу).
    *
    * $this->invoice содержит объект "Счет на оплату" (подробнее об объекте "Счет на оплату" на https://payqr.ru/api/ecommerce#invoice_object)
    *
    * Ниже можно вызвать функции своей учетной системы, чтобы особым образом отреагировать на уведомление от PayQR о событии invoice.reverted.
    *
    * Получить orderId из объекта "Счет на оплату", по которому произошло событие, можно через $this->invoice->getOrderId();
    */ 
    public function revertOrder()
    {        
        //отправка сообщений
        $module = new PayqrModule();
        if($module->getOption("message-invoice-reverted"))
        {
            $message = $this->invoice->getMessage();
            if($message)
            {
                $message->article = $module->getOption("message-invoice-reverted-article");
                $message->text = $module->getOption("message-invoice-reverted-text");
                $message->imageUrl = $module->getOption("message-invoice-reverted-imageUrl");
                $message->url = $module->getOption("message-invoice-reverted-url");
                $this->invoice->setMessage($message);
            }
        }
    }
    
    /*
    * Код будет выполнен, когда интернет-сайт получит уведомление от PayQR об отмене счета (заказа) до его оплаты.
    * Это означает, что либо вышел срок оплаты счета (заказа), либо покупатель отказался от оплаты счета (заказа), либо PayQR успешно обработал запрос в PayQR от интернет-сайта об отказе от счета (заказа) до его оплаты покупателем.
    *
    * $this->invoice содержит объект "Счет на оплату" (подробнее об объекте "Счет на оплату" на https://payqr.ru/api/ecommerce#invoice_object)
    *
    * Ниже можно вызвать функции своей учетной системы, чтобы особым образом отреагировать на уведомление от PayQR о событии invoice.cancelled.
    *
    * Получить orderId из объекта "Счет на оплату", по которому произошло событие, можно через $this->invoice->getOrderId();
    */
    public function cancelOrder()
    {
        
    }
    
    /*
    * Код будет выполнен, когда интернет-сайт получит уведомление от PayQR о сбое в совершении покупки (завершении операции).
    * Это означает, что что-то пошло не так в процессе совершения покупки (например, интернет-сайт не ответил во время на уведомление от PayQR), поэтому операция прекращена.
    *
    * $this->invoice содержит объект "Счет на оплату" (подробнее об объекте "Счет на оплату" на https://payqr.ru/api/ecommerce#invoice_object)
    *
    * Ниже можно вызвать функции своей учетной системы, чтобы особым образом отреагировать на уведомление от PayQR о событии invoice.failed.
    *
    * Получить orderId из объекта "Счет на оплату", по которому произошло событие, можно через $this->invoice->getOrderId();
    */
    public function failOrder()
    {
        
    }
    
    /**
    * Код в этом файле будет выполнен, когда интернет-сайт получит уведомление от PayQR о необходимости предоставить покупателю способы доставки конкретного заказа.
    * Это означает, что интернет-сайт на уровне кнопки PayQR активировал этап выбора способа доставки покупателем, и сейчас покупатель дошел до этого этапа.
    *
    * $this->invoice содержит объект "Счет на оплату" (подробнее об объекте "Счет на оплату" на https://payqr.ru/api/ecommerce#invoice_object)
    *
    * Ниже можно вызвать функции своей учетной системы, чтобы особым образом отреагировать на уведомление от PayQR о событии invoice.deliverycases.updating.
    *
    * Важно: на уведомление от PayQR о событии invoice.deliverycases.updating нельзя реагировать как на уведомление о создании заказа, так как иногда оно будет поступать не от покупателей, а от PayQR для тестирования доступности функционала у конкретного интернет-сайта, т.е. оно никак не связано с реальным формированием заказов. Также важно, что в ответ на invoice.deliverycases.updating интернет-сайт может передать в PayQR только содержимое параметра deliveryCases объекта "Счет на оплату". Передаваемый в PayQR от интернет-сайта список способов доставки может быть многоуровневым.
    *
    * Пример массива способов доставки:
    * $delivery_cases = array(
    *          array(
    *              'article' => '2001',
    *               'number' => '1.1',
    *               'name' => 'DHL',
    *               'description' => '1-2 дня',
    *               'amountFrom' => '0',
    *               'amountTo' => '70',
    *              ),
    *          .....
    *  );
    * $this->invoice->setDeliveryCases($delivery_cases);
    */
    public function setDeliveryCases()
    {
        $total = $this->invoice->getAmount();
        
        $rows = DB::query_fetch_all("SELECT sd.id, sd.name1, sd.text1, sdt.price  
                                     FROM {shop_delivery} sd 
                                     LEFT JOIN {shop_delivery_thresholds} sdt ON sd.id=sdt.delivery_id 
                                     WHERE sd.act1='1' AND sd.trash='0' AND sdt.trash='0' AND sdt.amount < %f", $total);

        $delivery_cases = array();

        PayqrLog::log(print_r($rows, true));

        $i = 1;
        foreach($rows as $row)
        {
            $delivery_cases[] = array(
                'article' => $row['id'],
                'number' => $i++,
                'name' => $row['name1'],
                'description' => $row['text1'],
                'amountFrom' => $row['price'],
                'amountTo' => $row['price']
            );
        }

        PayqrLog::log(print_r($delivery_cases, true));

        $this->invoice->setDeliveryCases($delivery_cases);
    }
    
    /*
    * Код в этом файле будет выполнен, когда интернет-сайт получит уведомление от PayQR о необходимости предоставить покупателю пункты самовывоза конкретного заказа.
    * Это означает, что интернет-сайт на уровне кнопки PayQR активировал этап выбора пункта самовывоза покупателем, и сейчас покупатель дошел до этого этапа.
    *
    * $this->invoice содержит объект "Счет на оплату" (подробнее об объекте "Счет на оплату" на https://payqr.ru/api/ecommerce#invoice_object)
    *
    * Ниже можно вызвать функции своей учетной системы, чтобы особым образом отреагировать на уведомление от PayQR о событии invoice.pickpoints.updating.
    *
    * Важно: на уведомление от PayQR о событии invoice.pickpoints.updating нельзя реагировать как на уведомление о создании заказа, так как иногда оно будет поступать не от покупателей, а от PayQR для тестирования доступности функционала у конкретного интернет-сайта, т.е. оно никак не связано с реальным формированием заказов. Также важно, что в ответ на invoice.pickpoints.updating интернет-сайт может передать в PayQR только содержимое параметра pickPoints объекта "Счет на оплату". Передаваемый в PayQR от интернет-сайта список пунктов самовывоза может быть многоуровневым.
    *
    * Пример массива способов доставки:
    * $pick_points_cases = array(
    *          array(
    *              'article' => '1001',
    *               'number' => '1.1',
    *               'name' => 'Наш пункт самовывоза 1',
    *               'description' => 'с 10:00 до 22:00',
    *               'amountFrom' => '90',
    *               'amountTo' => '140',
    *              ),
    *          .....
    *  );
    * $this->invoice->setPickPointsCases($pick_points_cases);
    */
    public function setPickPoints()
    {
        
    }
}
