<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PayqrModule
 *
 * @author 1
 */
class PayqrModule 
{
    public static function getBaseUrl()
    {
        $url = "http://{$_SERVER["SERVER_NAME"]}/" . PayqrConfig::$baseUrl;
        return $url;
    }
    public static function redirect($url)
    {        
        $location = PayqrModule::getBaseUrl() . $url;
        header("Location: $location");
    }

        private $options;

    private function setOptions()
    {
        $db = PayqrModuleDb::getInstance();        
        $auth = new PayqrModuleAuth();
        $user = $auth->getUser();
        if($user)
        {
            $query = "select settings from ".PayqrModuleDb::getUserTable()." where user_id={$user->user_id}";
        }
        else 
        {
            $query = "select settings from ".PayqrModuleDb::getUserTable()." limit 1";
        }
        $result = $db->query($query);
        if($settings = json_decode($result->settings))
        {
            foreach($settings as $item)
            {
                $this->options[$item->key] = $item->value;
            }
        }
    }
    
    public function getOption($key)
    {
        if(!$this->options)
        {
            $this->setOptions();
        }
        if(isset($this->options[$key]))
        {
            return $this->options[$key];
        }
    }
}
