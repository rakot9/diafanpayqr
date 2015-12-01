<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PayqrButtonPage
 *
 * @author 1
 */
class PayqrButtonPage 
{
    private $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function getSettings()
    {
        $settings = array();
        if($this->user)
        {
            $db = PayqrModuleDb::getInstance();
            $user = $db->select("select * from ".PayqrModuleDb::getUserTable()." where user_id=?", array($this->user->user_id), array("s"));
            if($user)
            {
                $settings = json_decode($user->settings);
                if($settings)
                {
                    $buttonSettings = new PayqrButtonDefaultSettings();
                    foreach ($settings as $item)
                    {
                        switch ($item->key)
                        {
                            case "order-status-invoice-order-creating":
                                $item->possible_values = $buttonSettings->getIOCStatusList();
                                break;
                            case "order-status-invoice-paid":
                                $item->possible_values = $buttonSettings->getIPStatusList();
                                break;
                            case "order-status-invoice.cancelled":
                                $item->possible_values = $buttonSettings->getICStatusList();
                                break;
                            case "order-status-invoice.failed":
                                $item->possible_values = $buttonSettings->getIFStatusList();
                                break;
                            case "order-status-invoice-reverted":
                                $item->possible_values = $buttonSettings->getIRStatusList();
                                break;
                        }
                    }
                }
            }
        }
        if(empty($settings))
        {
            require_once __DIR__ . "/button.settings.php";
            $settings = json_decode(json_encode($settings));
        }
        return $settings;
    }

    public function getHtml()
    {
        $html = "<H1>Настройки PayQR</H1>";
        $html .= "<div class='form'><form method='post'>";
        $settings = $this->getSettings();
        $html .= $this->getHtmlRec($settings);
        $html .= "<div class='row'><input type='submit' value='Сохранить'/></form></div>";
        $html .= "<div><span style='color:red'>*</span>Высота и Ширина кнопки указываются в px или %, например 10px или 20%</div>";
        
        $button = new PayqrButtonGenerator();
        $html .= $button->getJs();
        $html .= "<div class='button_example'>Кнопка в корзине<br/>";
        $html .= $button->getCartButton();
        $html .= "</div>";
        
        $html .= "<div class='button_example'>Кнопка в карточке товара<br/>";
        $html .= $button->getProductButton();
        $html .= "</div>";
        
        $html .= "<div class='button_example'>Кнопка на страничке категории товаров<br/>";
        $html .= $button->getCategoryButton();
        $html .= "</div>";
        
        $html .= "<div class='button_example'>";
        $html .= "<form method='post'><input type='hidden' name='exit'/><input type='submit' value='Выйти'/></form>";
        $html .= "</div>";
        return $html;
    }
    private function getHtmlRec($settings, $parent="")
    {
        $html = "";
        foreach($settings as $item)
        {
            if($item->parent == $parent)
            {
                $html .= "<li id='{$item->key}' class='row'>";
                $html .= $this->getRow($item);
                $html .= "<ul id='child-{$item->key}' class='children'>";
                $html .= $this->getHtmlRec($settings, $item->key);
                $html .= "</ul>";
                $html .= "</li>";
            }
        }
        return $html;
    }
    private function getRow($item)
    {
        $html = "";
        if($item->parent == "")
        {
          $html .= "<a href='javascript:void(0)'>$item->name</a>";
        }
        else
        {
            $html .= "<label for='{$item->key}'>{$item->name}</label>";

            $text_attr = $this->get_attr_str($item, "text");
            $select_attr = $this->get_attr_str($item, "select");

            if(!empty($item->possible_values))
            {
                $html .= "<select $select_attr>";
                foreach($item->possible_values as $key=>$val)
                {
                    $s = "";
                    if($key == $item->value){
                        $s = "selected='selected'";
                    }
                    $html .= "<option value='$key' $s>$val</option>";
                }
                $html .= "</select>";
            }
            elseif(substr($item->key, 0, 12) == "order_status")
            {

            }
            else{
                $html .= "<input type='text' $text_attr/>";
            }
        }
        return $html;
    }

    private function get_attr_str($item, $type)
    {
        $text = "text";
        $select = "select";

        $attr = array();
        if($item->changable == 0){
            $attr["readonly"] = "readonly";
            $attr["style"] = "background-color: #eee;";
        }
        $attr["id"] = $item->key;
        $attr["name"] = "PayqrSettings[{$item->key}]";
        $attr["value"] = $item->value;
        if($type == $text)
        {
            $attr["size"] = strlen($item->value);
        }
        $attr_str = "";
        foreach($attr as $key=>$val)
        {
            $attr_str .= "$key='$val' ";
        }
        $attr_str = trim($attr_str);
        return $attr_str;
    }
    
    
    
    public function save($post)
    {
        $db = PayqrModuleDb::getInstance();
        $settings = $this->getSettings();
        foreach ($settings as $item)
        {
            if(isset($post[$item->key]))
            {
                $item->value = $post[$item->key];
                if($item->key == "logUrl")
                {
                    $key = "key=";
                    $url = explode($key, $post[$item->key]);
                    $item->value = $url[0] . $key . $post["logKey"];
                }
                if($item->key == "merchantID")
                {
                    $db->update(PayqrModuleDb::getUserTable(), array("merch_id" => $post[$item->key]), array("%s"), array("user_id" => $this->user->user_id), array("%s"));
                }
            }
        }
        $settings = json_encode($settings);
        $db->update(PayqrModuleDb::getUserTable(), array("settings" => $settings), array("%s"), array("user_id" => $this->user->user_id), array("%s"));
    }
}