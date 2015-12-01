<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PayqrHandler
 *
 * @author 1
 */
class PayqrEvent
{
    protected $data;
    protected $object;
    public $cancel = false;

    public function __construct($object)
    {
        $this->object = $object;
        $this->data = $this->object->data;
    }
    
    /**
    * Проверяет режим работы полученного уведомления от PayQR (true - "боевой", false - "тестовый")
    * @return bool
    */
    public function isLivemode()
    {
        return isset($this->object->livemode) ? $this->object->livemode : 0;
    }
    
    /**
    * Возвращает идентификатор PayQR конкретного объекта "Счет на оплату"
    * @return string
    */
    protected function getDataId()
    {
        return isset($this->data->id) ? $this->data->id : 0;
    }
    
    /**
    * Возвращает номер покупателя в PayQR (уникальный идентификатор покупателя, можно ориентироваться для начисления бонусов за повторные покупки)
    * @return mixed
    */
    public function getPayqrUserId()
    {
        return isset($this->data->payqrUserId) ? $this->data->payqrUserId : 0;
    }
    
    /**
    * Возвращает номер счета в PayQR, по которому осуществлялся возврат (так номер счета видит покупатель в приложении PayQR)
    * @return mixed
    */
    public function getPayqrNumber()
    {
        return isset($this->data->payqrNumber) ? $this->data->payqrNumber : 0;
    }

    /**
    * Возвращает сумму возврата по счету из объекта PayQR "Возвраты"
    * @return float
    */
    public function getAmount()
    {
        return isset($this->data->amount) ? $this->data->amount : 0;
    }

    /**
     * Получение объекта userdata
     * @return mixed
     */
    public function getUserdata()
    {
        return isset($this->data->userdata) ? $this->data->userdata : "";
    }
    
    /**
     * Задаёт userdata
     * @param $userdata
     */
    public function setUserdata($userdata)
    {
        $this->data->userdata = $userdata;
    }
}
