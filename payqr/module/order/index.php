<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__ . '/../../PayqrConfig.php';

$id = isset($_GET["id"]) ? $_GET["id"] : 0;
$order = new PayqrOrderAction($id);
if(isset($_POST["order_form"]))
{
    $order->handle($_POST);
}
echo $order->getForm();

?>

<style>
    .error
    {
        color: red;
    }
    .row
    {
        margin: 12px 0;
    }
    th, td
    {
        border: 1px solid black;
    }
</style>