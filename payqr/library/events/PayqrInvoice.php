<?php
/**
 * Работа с объектами "Счет на оплату"
 *
 * Интернет-сайт получает объект "Счет на оплату" целиком в каждом уведомлении от PayQR о событиях, связанных с этим объектом. Интернет-сайт может сам реализовывать любые логики работы с объектом "Счет на оплату", но для упрощения написания собственного кода некоторые методы заранее подготовлены и представлены в рамках этого файла. Пользуйтесь ими при желании.
 */
 
class PayqrInvoice extends PayqrEvent
{
    /**
    * Возвращает идентификатор PayQR конкретного объекта "Счет на оплату"
    * @return string
    */
    public function getInvoiceId()
    {
        return $this->getDataId();
    }

    /**
     * Возвращает номер заказа интернет-сайта (orderId) из объекта PayQR "Счет на оплату"
     * @return mixed
     */
    public function getOrderId()
    {
        return isset($this->data->orderId) ? $this->data->orderId : 0;
    }

    /**
     * Возвращает номер заказа интернет-сайта (orderId) из объекта PayQR "Счет на оплату"
     * @return mixed
     */
    public function getOrderGroupId()
    {
        return isset($this->data->orderGroup) ? $this->data->orderGroup : 0;
    }

    /**
     * Передает номера заказа интернет-сайта (orderId) в объект PayQR "Счет на оплату"
     * @param $id
     * @return bool
     */
    public function setOrderId($orderId)
    {
        if (isset($this->data->orderId)) {
            $this->data->orderId = $orderId;
            return true;
        }
        return false;
    }

    /**
     * Тестовый метод удаляет orderId из json Ответа
     * @return bool
     */
    public function removeOrderId()
    {
        if (isset($this->data->orderId)) {
            unset($this->data->orderId);
            return true;
        }
        return false;
    }
    /**
     * Тестовый метод удаляет orderId из json Ответа
     * @return bool
     */
    public function removeAmount()
    {
        if (isset($this->data)) {
            unset($this->data->amount);
            return true;
        }
        return false;
    }

    /**
     * Возвращает сумму заказа из объекта PayQR "Счет на оплату"
     * @return float
     */
    public function getAmount()
    {
        return isset($this->data->amount) ? $this->data->amount : 0;
    }

    /**
     * Изменяет сумму заказа в объекте PayQR "Счет на оплату" (для случаев крайней необходимости)
     * @param $amount
     * @return bool
     */
    public function setAmount($amount)
    {
        if (isset($this->data->amount)) {
            $this->data->amount = round($amount, 2);
            return true;
        }
      return false;
    }

    /**
     * Возвращает содержимое корзины из объекта PayQR "Счет на оплату
     * @return mixed
     */
    public function getCart()
    {
        return isset($this->data->cart) ? $this->data->cart : false;
    }

    /**
     * Изменяет содержимое корзины в объекте PayQR "Счет на оплату"
     * @param $cart_obj
     * @return bool
     */
    public function setCart($cart_obj)
    {
        if (isset($this->data->cart)) {
            $this->data->cart = $cart_obj;
            return true;
        }
        return false;
    }
    
    /**
     * Получение объекта сообщения
     * @return mixed
     */
    public function getMessage()
    {
        return isset($this->data->message) ? $this->data->message : "";
    }
    
    /**
     * Задаёт сообщение
     * @param $message
     */
    public function setMessage($message)
    {
        $this->data->message = $message;
    }
    
 
    /**
     * Возвращает данные покупателя из объекта PayQR "Счет на оплату"
     * @return object
     */
    public function getCustomer()
    {
        return isset($this->data->customer) ? $this->data->customer : false;
    }

    /**
     * Возвращает адрес доставки из объекта PayQR "Счет на оплату"
     * @return mixed
     */
    public function getDelivery()
    {
        return isset($this->data->delivery) ? $this->data->delivery : false;
    }

    /**
     * Передает список способов доставки интернет-сайта в объект PayQR "Счет на оплату"
     * @param $delivery_cases_array
     */
    public function setDeliveryCases($delivery_cases_array)
    {
        if (isset($this->data->deliveryCases) && count($delivery_cases_array) > 0) {
            $delivery_cases = array();
            foreach ($delivery_cases_array as $delivery) {
                $delivery_cases[] = json_decode(json_encode($delivery), false);
            }
            $this->data->deliveryCases = $delivery_cases;
            return true;
        }
        return false;
    }

    /**
     * Передает список пунктов самовывоза интернет-сайта в объект PayQR "Счет на оплату"
     * @param $delivery_cases_array
     */
    public function setPickPointsCases($pick_points_cases_array)
    {
        if (isset($this->data->pickPoints) && count($pick_points_cases_array) > 0) {
            $pick_points_cases = array();
            foreach ($pick_points_cases_array as $point) {
                $pick_points_cases[] = json_decode(json_encode($point), false);
            }
            $this->data->pickPoints = $pick_points_cases;
            return true;
        }
        return false;
    }

    /**
     * Возвращает выбранный покупаталем пункт самовывоза из объекта PayQR "Счет на оплату"
     * Выбранный пункт самовывоза полностью соответствует одной из записей в предложенных интернет-сайтом пунктах самовывоза или принимает значение null.
     * 
     * @return bool
     */
    public function getPickPoints()
    {
        if(isset($this->data->pickPointsSelected)){
            return $this->data->pickPointsSelected;
        }
        return false;
    }

    /**
     * Возвращает выбранный покупаталем способ доставки из объекта PayQR "Счет на оплату"
     * Выбранный способ доставки полностью соответствует одной из записей в предложенных интернет-сайом способах доставки или принимает значение null.
     *
     * @return bool
     */
    public function getDeliveryCasesSelected()
    {
        if(isset($this->data->deliveryCasesSelected)){
            return $this->data->deliveryCasesSelected;
        }
        return false;
    }

    /**
     * Возвращает промо-код, если его указал покупатель, из объекта PayQR "Счет на оплату"
     * @return mixed
     */
    public function getPromoCode()
    {
        return isset($this->data->promoCode) ? $this->data->promoCode : false;
    }

    /**
     * Возвращает промо-карту, если её указал покупатель, из объекта PayQR "Счет на оплату"
     * @return mixed
     */
    public function getPromoCard()
    {
        return isset($this->data->promoCard) ? $this->data->promoCard : false;
    }

    /**
     * Возвращает номер товарной группы (orderGroup) из объекта PayQR "Счет на оплату"
     * @return mixed
     */
    public function getOrderGroup()
    {
        return isset($this->data->orderGroup) ? $this->data->orderGroup : false;
    }

    /**
     * Возвращает срок ожидания оплаты этого счета из объекта PayQR "Счет на оплату"
     * @return mixed
     */
    public function getConfirmWaitingInMinutes()
    {
        return isset($this->data->confirmWaitingInMinutes) ? $this->data->confirmWaitingInMinutes : false;
    }

    /**
     * Изменяет срок ожидания оплаты этого счета в объекте PayQR "Счет на оплату"
     * @return mixed
     */
    public function setConfirmWaitingInMinutes($min)
    {
        if (isset($this->data->confirmWaitingInMinutes)) {
            $this->data->confirmWaitingInMinutes = $min;
            return true;
        }
        return false;
    }

    /**
     * Отменяет заказ (счет) до его оплаты (не через отдельный запрос в PayQR, а ответом в PayQR на полученное уведомление как HTTP/1.1 409 Conflict, поэтому подходит для использования только на этапе получения уведомления о событии invoice.order.creating)
     */
    public function cancelOrder()
    {
        if($this->data->cancel){
            $this->cancel = true;
        }
    }
} 