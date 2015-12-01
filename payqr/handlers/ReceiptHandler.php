<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ReceiptHandler
 *
 * @author 1
 */
class ReceiptHandler 
{
    private $receipt;
    
    public function __construct(PayqrReceipt $receipt) 
    {
        $this->receipt = $receipt;
    }
    
    public function pay()
    {
        
    }
}
