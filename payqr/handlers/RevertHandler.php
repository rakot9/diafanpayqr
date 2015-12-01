<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RevertHandler
 *
 * @author 1
 */
class RevertHandler 
{
    private $revert;
    
    public function __construct(PayqrRevert $revert) 
    {
        $this->revert = $revert;
    }
    
    /*
    * Код будет выполнен, когда интернет-сайт получит уведомление от PayQR об отказе в выполнении запроса в PayQR на отмену счета и возврат денежных средств.
    * Это означает, что PayQR не будет осуществлять отмену конкретного счета (заказа) и возврат денежных средств покупателю, поэтому интернет-сайту необходимо самостоятельно осуществить возврат денежных средств покупателю без участия PayQR. Подобный отказ может быть связан с ограничениями действующего законодательства Российской Федерации в области переводов денежных средств.
    *
    * $this->revert содержит объект "Возвраты" (подробнее об объекте "Возвраты" на https://payqr.ru/api/ecommerce#revert_object)
    *
    * Ниже можно вызвать функции своей учетной системы, чтобы особым образом отреагировать на уведомление от PayQR о событии revert.failed.
    */
    public function fail()
    {
        
    }
    
    /*
    * Код будет выполнен, когда интернет-сайт получит уведомление от PayQR о выполнении запроса в PayQR на отмену счета и возврат денежных средств.
    * Это означает, что PayQR успешно отменил счет на указанную в запросе в PayQR сумму и вернул эту сумму покупателю (на источник списания денежных средств или на остаток в PayQR).
    *
    * $this->revert содержит объект "Возвраты" (подробнее об объекте "Возвраты" на https://payqr.ru/api/ecommerce#revert_object)
    *
    * Ниже можно вызвать функции своей учетной системы, чтобы особым образом отреагировать на уведомление от PayQR о событии revert.succeeded.
    */
    public function succeed()
    {
        
    }
}
