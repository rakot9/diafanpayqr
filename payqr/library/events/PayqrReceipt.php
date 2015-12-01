<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of payqr_receipt
 *
 * @author 1
 */
class PayqrReceipt extends PayqrEvent
{
    /**
     * Возвращает идентификатор PayQR конкретного объекта "платёжное поручение"
     * @return mixed
     */
    public function getReceiptId()
    {
        return $this->getDataId();
    }
}