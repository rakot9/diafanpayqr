<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of payqr_offer
 *
 * @author 1
 */
class PayqrOffer extends PayqrEvent
{    
    /**
    * Возвращает идентификатор PayQR конкретного объекта "Привязанный"
    * @return mixed
    */
    public function getOffertId()
    {
        return $this->getDataId();
    }
    
    
    public function setInvoices($invoices)
    {
        $this->object->data = $invoices;
    }
}
