<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PayqrButtonDefaultSettings
 *
 * @author 1
 */
class PayqrButtonDefaultSettings 
{    
    private $log_key;    
    
    public function getHandlerUrl()
    {
        $url = "";
        $auth = new PayqrModuleAuth();
        if($user = $auth->getUser())
        {
            $url = PayqrModule::getBaseUrl() . "/handler.php?user_id={$user->user_id}";
        }
        return $url;
    }
    
    public function getLogKey()
    {
        $key = md5(uniqid());
        $this->log_key = $key;
        return $key;
    }
    
    public function getLogPath()
    {
        $path = PayqrConfig::$baseUrl . "/" . PayqrConfig::$logFile;
        return $path;
    }
    
    public function getLogUrl()
    {
        $url = "";
        $auth = new PayqrModuleAuth();
        if($user = $auth->getUser())
        {
            $url = PayqrModule::getBaseUrl() . "/log.php?user_id={$user->user_id}&key={$this->log_key}";
        }
        return $url;
    }
    
    
    public function getOrderStatusList()
    {
        $list = array(
            "1"=>"Новый",
            "2"=>"Оплачен",
            "3"=>"Отменён",
        );
        return $list;
    }
    
    public function getIOCStatusList()
    {
        $list = $this->getOrderStatusList();
        return $list;
    }
    
    public function getIPStatusList()
    {
        $list = $this->getOrderStatusList();
        return $list;
    }
    
    public function getICStatusList()
    {
        $list = $this->getOrderStatusList();
        return $list;
    }
    
    public function getIFStatusList()
    {
        $list = $this->getOrderStatusList();
        return $list;
    }
    
    public function getIRStatusList()
    {
        $list = $this->getOrderStatusList();
        return $list;
    }
}
