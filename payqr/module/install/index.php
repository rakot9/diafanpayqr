<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$step = isset($_GET["step"]) ? $_GET["step"] : 1;
require_once __DIR__ . '/../../PayqrConfig.php';
$install = new PayqrModuleInstall();
$html = "";
switch ($step)
{
    case 1:
        if($install->checkDbConfigConn())
        {
            PayqrModule::redirect("/module/install/index.php?step=2");
        }
        else
        {
            if(isset($_POST["username"]) && !empty($_POST["username"]))
            {
                if($install->saveDbConfig($_POST))
                {
                    PayqrModule::redirect("/module/install/index.php?step=2");
                }
                else{
                    $html .= "<div class='error'>Неверные доступы в бд</div>";
                }
            }
            $html .= "<form method='post'>";
            $html .= "<div>Введите данные для доступа к бд</div>";
            $html .= "<input type='hidden' name='step1'/>";
            $html .= "<div><label>Имя пользователя: <input type='text' name='username'/></label></div>";
            $html .= "<div><label>Пароль: <input type='password' name='password'/></label></div>";
            $html .= "<div><label>Имя базы данных: <input type='text' name='database'/></label></div>";
            $html .= "<div><label>Префикс таблиц: <input type='text' name='prefix'/></label></div>";
            $html .= "<div><label>Хост: <input type='text' value='localhost' name='host'/></label></div>";
            $html .= "<div><input type='submit' value='Отправить'/></div>";
            $html .= "</form>";
        }
        break;
    case 2:
        $install->createTables();
        if(isset($_POST["step2"]))
        {
            $msg = $install->register($_POST);
            $html .= "<div class='error'>$msg</div>";
        }
        $html .= "<form method='post'>";
        $html .= "<input type='hidden' name='step2'/>";
        $html .= "<div>Введите данные для входа в кабинет</div>";
        $html .= "<div><label>Имя пользователя: <input type='text' name='username'/></label></div>";
        $html .= "<div><label>Пароль: <input type='password' name='password'/></label></div>";
        $html .= "<div><label>Повторите пароль: <input type='password' name='password_repeat'/></label></div>";
        $html .= "<div><input type='submit' value='Отправить'/></div>";
        $html .= "</form>";
        break;
    default:
        $html = "<h1>Установка завершена</h1>";
        break;
}

echo $html;



?>

<style>
    .error
    {
        font-weight: bold;
        color: red;
    }
    form
    {
        margin: 20% 50%;
        width: 200px;
        height: 200px;
    }
    form div
    {
        margin: 10px 20px;
        width: 100%;
    }
</style>