<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Класс используется для генерации кнопки в цмс
 *
 * @author 1
 */
class PayqrButtonGenerator 
{
    private $scenario = "buy";
    private $type_cart = "cart";
    private $type_product = "product";
    private $type_category = "category";

    private $products;
    private $amount;

    public function __construct($products=array(), $amount=0)
    {
        $this->products = $products;
        $this->amount = $amount;
    }

    /**
    * Возвращает код скрипта PayQR для размещения в head интернет-сайта
    */
    public function getJs()
    {
      return '<script src="https://payqr.ru/popup.js?merchId=' . $this->getOption("merchantID") . '"></script>' . PHP_EOL .
             '<script src="http://'.$_SERVER['SERVER_NAME'].'/payqr/diafanpayqr.js">';
    }
    
    public function getCartButton()
    {
        if($this->getOption("button-show-on-cart"))
        {
            $products = $this->products;
            return $this->get_button_html($this->scenario, $products, $this->type_cart);
        }
    }

    public function getProductButton()
    {
        if($this->getOption("button-show-on-product"))
        {
            $products = $this->products;
            return $this->get_button_html($this->scenario, $products, $this->type_product);
        }
    }

    public function getCategoryButton()
    {
        if($this->getOption("button-show-on-category"))
        {
            $products = $this->products;
            return $this->get_button_html($this->scenario, $products, $this->type_category);
        }
    }
    
    private function get_button_html($scenario, $products, $type)
    {
        $data = $this->get_data($scenario, $products, $type);
        $button_name = "";
        if($this->getOption("custom-button-name"))
        {
            $button_name = $this->getOption("custom-button-name");
        }
        $html = "<button";
        foreach($data as $attr=>$value)
        {
            if(is_array($value))
            {
                $value = implode(" ", $value);
            }
            if(!empty($value))
            {
                $html .= " $attr='$value'";
            }
        }
        $html .= ">$button_name</button>";
        return $html;
    }
  
  
    /**
     * @param $scenario
     * @param array $data
     * @return array|bool
     */
    private function get_data($scenario, $products, $type) 
    {
        $data = array();
        $data['data-scenario'] = $scenario;


        $cart_data = $products;
        $data_amount = 0;
        foreach ($cart_data as $item) {
            $data_amount += $item['amount'];
        }
        if($this->amount != 0)
        {
            $data_amount = $this->amount;
        }
        $data['data-amount'] = $data_amount;
        $data['data-cart'] = json_encode($cart_data);
        $data['data-firstname-required'] = $this->getOption('data-firstname-required');
        $data['data-lastname-required'] = $this->getOption('data-lastname-required');
        $data['data-middlename-required'] = $this->getOption('data-middlename-required');
        $data['data-phone-required'] = $this->getOption('data-phone-required');
        $data['data-email-required'] = $this->getOption('data-email-required');
        $data['data-delivery-required'] = $this->getOption('data-delivery-required');
        $data['data-deliverycases-required'] = $this->getOption('data-deliverycases-required');
        $data['data-pickpoints-required'] = $this->getOption('data-pickpoints-required');
        //$data['data-promocode-required'] = $this->getOption('data-promocode-required');
        //$data['data-promocard-required'] = $this->getOption('data-promocard-required');
        //$data['data-promocode-details'] = json_encode(array($this->getOption('data-promocode-details-article'), $this->getOption('data-promocode-details-description')));
        //$data['data-promocard-details'] = json_encode(array($this->getOption('data-promocard-details-article'), $this->getOption('data-promocard-details-description')));
//        if(!empty($this->getOption('data-promocode-details-article')) || !empty($this->getOption('data-promocode-details-description')))
//        {
//            $data['data-promocode-details'] = json_encode(array($this->getOption('data-promocode-details-article'), $this->getOption('data-promocode-details-description')));
//        }
//        if(!empty($this->getOption('data-promocard-details-article') || !empty($this->getOption('data-promocard-details-description'))))
//        {
//            $data['data-promocard-details'] = json_encode(array($this->getOption('data-promocard-details-article'), $this->getOption('data-promocard-details-description')));
//        }
        $userdata = array(
            "custom" => $this->getOption("data-userdata"),
        );
        $data['data-userdata'] = json_encode(array($userdata));
        $button_style = $this->get_button_style($type);
        $data['class'] = $button_style['class'];
        $data['style'] = $button_style['style'];

        return $data;
    }
  
  
    /**
     * Получить список стилей кнопки
     * 
     * @param string $type
     * @return array
     */
    private function get_button_style($type)
    {
        $style = array();
        $style['class'][] = 'payqr-button';
        $style['class'][] = $this->getOption($type . '-button-color');
        $style['class'][] = $this->getOption($type . '-button-form');
        $style['class'][] = $this->getOption($type . '-button-gradient');
        $style['class'][] = $this->getOption($type . '-button-text-case');
        $style['class'][] = $this->getOption($type . '-button-text-width');
        $style['class'][] = $this->getOption($type . '-button-text-size');
        $style['class'][] = $this->getOption($type . '-button-shadow');
        $style['style'][] = 'height:' . $this->getOption($type . '-button-height') . ';';
        $style['style'][] = 'width:' . $this->getOption($type . '-button-width') . ';';
                
        if($this->getOption("custom-button") == 1)
        {
            $style["class"][] = "payqr-button_idkfa";
            if($this->getOption("custom-button-classes"))
            {
                $style["class"][] = $this->getOption("custom-button-classes");
            }
            if($this->getOption("custom-button-styles"))
            {
                $style["style"][] = $this->getOption("custom-button-styles");
            }
        }

        return $style;
    }
    
    /**
     * Получить значение для настроек кнопки
     * 
     * @param string $key
     * @return string
     */
    private function getOption($key)
    {
        $module = new PayqrModule();
        $value = $module->getOption($key);
        return $value;
    }
}
