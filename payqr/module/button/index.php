<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__ . '/../../PayqrConfig.php';
$auth = new PayqrModuleAuth();
if(isset($_POST["exit"]))
{
    $auth->logOut();
}
$user = $auth->getUser();
if($user)
{
    $button = new PayqrButtonPage($user);
    if(isset($_POST["PayqrSettings"]))
    {
        $button->save($_POST["PayqrSettings"]);
    }
    $html = $button->getHtml();
    echo $html;
}

?>

<style>
    .row
    {
        margin: 5px 0;
    }
    label
    {
        font-weight: bold;
        font-size: 0.9em;
        display: block;
    }
    .children
    {
        display: none;
    }
    #child-base-options
    {
        display: block;
    }
    .button_example
    {
        margin: 20px 0;
    }
</style>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script>
    $("li.row a").click(function(){
        var id = "#child-" + $(this).parent().attr("id");
        $(id).toggle();
    });
    $("li.row select").change(function(){
        var id = "#child-" + $(this).parent().attr("id");
        var val = $(this).val();
        if(val == 1 || val == "nonrequired"){
            $(id).show();
        }
        else {
            $(id).hide();
        }
    });
    $("li.row select").each(function(){
        var id = "#child-" + $(this).parent().attr("id");
        var val = $(this).val();
        if(val == 1 || val == "nonrequired"){
            $(id).show();
        }
    });
</script>