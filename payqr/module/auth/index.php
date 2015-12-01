<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__ . '/../../PayqrConfig.php';
$auth = new PayqrModuleAuth();
$auth->authenticate();

?>

<div id="form">    
    <form method="post">
        <div>Вход в систему</div>
        <div><label>Логин: <input type="text" name="username"/></label></div>
        <div><label>Пароль: <input type="password" name="password"/></label></div>
        <div><input type="submit" value="Отправить"/></div>
    </form>
</div>

<style>
    #form
    {
        margin: 20% 50%;
        width: 200px;
        height: 200px;
    }
    #form div
    {
        margin: 10px 20px;
        width: 100%;
    }
</style>