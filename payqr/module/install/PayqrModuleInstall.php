<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PayqrModuleInstall
 *
 * @author 1
 */
class PayqrModuleInstall 
{
    public function checkDbConn($host, $user, $password, $database)
    {
        if(!empty($host) && !empty($user) && !empty($password) && !empty($database))
        {            
            $link = mysqli_connect($host, $user, $password, $database);
            if($link && $link->connect_errno == 0)
            {
                return true;
            }
        }
    }
    public function checkDbConfigConn()
    {
        return $this->checkDbConn(PayqrModuleDbConfig::$host, PayqrModuleDbConfig::$username, PayqrModuleDbConfig::$password, PayqrModuleDbConfig::$database);
    }

    public function saveDbConfig($db)
    {
        if($this->checkDbConn($db["host"], $db["username"], $db["password"], $db["database"]))
        {
            $data = '<?php

class PayqrModuleDbConfig
{
    public static $username = "'.$db["username"].'";
    public static $password = "'.$db["password"].'";
    public static $database = "'.$db["database"].'";
    public static $host = "'.$db["host"].'";
    public static $prefix = "'.$db["prefix"].'";
}';
            $file = PAYQR_ROOT . "module/orm/PayqrModuleDbConfig.php";
            file_put_contents($file, $data);
            PayqrModuleDbConfig::setConfig($db);
            $this->createTables();
            return true;
        }
        else{
            return false;
        }
    }
    
    public function register($post)
    {
        if($post["password"] != $post["password_repeat"])
        {
            return "Пароли не совпадают";
        }
        $db = PayqrModuleDb::getInstance();
        $user = $db->select("select * from ".PayqrModuleDb::getUserTable()." where username = ?", array($post["username"]), array("s"));
        if($user)
        {
            return "Пользователь с таким именем уже существует";
        }
        $auth = new PayqrModuleAuth();
        $password = $auth->encodePassword($post["password"]);
        $id = $db->insert(PayqrModuleDb::getUserTable(), array("username"=>$post["username"], "password"=>$password), array("%s", "%s"));
        $auth = new PayqrModuleAuth($id);
        PayqrModule::redirect("/module/auth");
    }
    
    public function createTables()
    {
        $prefix = PayqrModuleDbConfig::$prefix;
        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}payqr_invoice` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `invoice_id` varchar(100) NOT NULL,
                    `invoice_type` varchar(100) NOT NULL,
                    `order_id` varchar(100) DEFAULT NULL,
                    INDEX `order_id_index` (`order_id`), 
                    INDEX `invoice_index` (`invoice_id`, `invoice_type`),
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                
                CREATE TABLE IF NOT EXISTS `{$prefix}payqr_log` (
                `log_id` int(11) NOT NULL AUTO_INCREMENT,
                  `data` text NOT NULL,
                  `event_id` varchar(100) NOT NULL,
                  `event_type` varchar(100) NOT NULL,
                  `payqr_number` varchar(100) NOT NULL,
                  `datetime` datetime NOT NULL, 
                  `order_id` int(11) DEFAULT NULL,
                   INDEX `event_id_index` (`event_id`), 
                   PRIMARY KEY (`log_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

                CREATE TABLE IF NOT EXISTS `{$prefix}payqr_user` (
                    `user_id` int(11) NOT NULL AUTO_INCREMENT,
                    `username` varchar(100) NOT NULL,
                    `password` varchar(100) NOT NULL,
                    `merch_id` varchar(100) DEFAULT NULL,
                    `settings` text DEFAULT NULL,
                    PRIMARY KEY (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        
        $db = PayqrModuleDb::getInstance();
        $table = $db->query("show tables like '{$prefix}payqr_user'");
        if(!$table)
        {
            $db->multiQuery($sql);
        }
    }
}