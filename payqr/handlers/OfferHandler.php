<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OfferHandler
 *
 * @author 1
 */
class OfferHandler 
{
    private $offer;
    
    public function __construct(PayqrOffer $offer) 
    {
        $this->offer = $offer;
    }
    
    /*
     * Пример как послать запрос
     *  $amount = 500;
        $invoices = array(
            "invoices" => array(
                array(
                    "scenario" => "pay",
                    "orderId" => rand(100, 1000),
                    "amount" => $amount,
                    "cart" => array(
                        array(                          
                          "article" => $this->offer->getOffertId(),
                          "name" => "Жёлтое такси (500 р.)",
                          "quantity" => "1",
                          "amount" => $amount
                        ),
                    ),
                )
            )
        );
        $this->offer->setInvoices($invoices);
     */
    public function create()
    {
        $amount = 500;
        $invoices = array(
            "invoices" => array(
                array(
                    "scenario" => "pay",
                    "orderId" => rand(100, 1000),
                    "amount" => $amount,
                    "cart" => array(
                        array(                          
                          "article" => $this->offer->getOffertId(),
                          "name" => "Жёлтое такси (500 р.)",
                          "quantity" => "1",
                          "amount" => $amount
                        ),
                    ),
                )
            )
        );
        $this->offer->setInvoices($invoices);
    }
}