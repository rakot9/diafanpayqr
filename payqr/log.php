<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__ . "/PayqrConfig.php";

$key = PayqrConfig::$logKey;
if(empty($key))
{
    $module = new PayqrModule();
    $key = $module->getOption("logKey");
}
if(isset($_GET["key"]) && $_GET["key"] == $key)
{
    $text = PayqrLog::showLog();
}
else
{
    $text = "Введён неверный ключ доступа к логам";
}
echo $text;