<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PayqrAuth
 *
 * @author 1
 */
class PayqrModuleAuth 
{
    private $key = "payqr_user";
    private $salt = "l2k3rhlkjefdd2l3kjr";
    
    public function __construct($user_id = false) 
    {
        if($user_id)
        {            
            $db = PayqrModuleDb::getInstance();
            $user = $db->select("select user_id, username, merch_id from ".PayqrModuleDb::getUserTable()." where user_id = ?", array($user_id), array("d"));
            if($user)
            {
                $this->setUser($user);
            }
        }
    }
    
    public function logOut()
    {
        $this->startSession(); 
        unset($_SESSION[$this->key]);
        unset($_COOKIE[$this->key]);
        PayqrModule::redirect("/module/auth");
    }

    public function encodePassword($password)
    {
        $password = md5(md5($password));
        return $password;
    }

    public function authenticate()
    {
        if($this->getUser())
        {
            PayqrModule::redirect("/module/button/");
        }
        $db = PayqrModuleDb::getInstance();
        $username = isset($_POST["username"]) ? $_POST["username"] : "";
        $password = isset($_POST["password"]) ? $_POST["password"] : "";
        if(!empty($username) && !empty($password))
        {
            $password = $this->encodePassword($password);
            $user = $db->select("select user_id, username, merch_id from ".PayqrModuleDb::getUserTable()." where username = ? and password = ?", array($username, $password), array("s", "s"));
            if($user)
            {
                $this->setUser($user);
                PayqrModule::redirect("/module/button/");
            }
        }
    }
    public function getUser()
    {
        $this->startSession();
        if(!isset($_SESSION[$this->key]))
        {
            $this->recoverUser();
        }
        return $_SESSION[$this->key];
    }
    private function setUser($user)
    {
        $this->startSession();
        $_SESSION[$this->key] = $user;
        $key = $this->getCookieKey($user);
        setcookie($this->key, $key, strtotime("+30 days"), '/');
    }
    private function startSession()
    {
        if(!isset($_SESSION)) 
        { 
            session_start(); 
        } 
    }
    private function getCookieKey($user)
    {
        $key = $user->user_id . $user->username . $this->salt;
        $key = md5($key);
        return $key;
    }

    private function recoverUser()
    {
        if(isset($_COOKIE[$this->key]))
        {
            $db = PayqrModuleDb::getInstance();
            $user = $db->select("select user_id, username, merch_id from ".PayqrModuleDb::getUserTable()." where md5(concat(user_id, username, '".$this->salt."')) = ?", array($_COOKIE[$this->key]), array("s"));
            if($user)
            {
                $_SESSION[$this->key] = $user;
            }
        }
        else
        {
            $location = PayqrModule::getBaseUrl() . "/module/auth/";
            $auth_location = "http://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";
            if($location != $auth_location)
            {
                header("Location: $location");
            }
            $_SESSION[$this->key] = false;
        }
    }
}