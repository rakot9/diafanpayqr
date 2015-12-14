<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PayqrOrderAction
 *
 * @author 1
 */
class PayqrOrderAction 
{
    private $id;
    
    public function __construct($id)
    {
        $this->id = $id;
    }
    
    public function getForm()
    {
        $html = "";
        PayqrConfig::setConfig();
        $db = PayqrModuleDb::getInstance();
        $invoiceObj = $db->select("select * from ".PayqrModuleDb::getInvoiceTable()." where order_id=?", array($this->id), array("s"));
        if($invoiceObj)
        {
            $payqrInvoice = new PayqrInvoiceAction();
            $invoice = $payqrInvoice->invoice_get($invoiceObj->invoice_id);
            if($invoice)
            {
                $html .= "<form method='post'>";
                $html .= "<div style='margin-bottom:20px'>";
                $html .= "<input type='hidden' name='invoice_id' value='{$invoice->id}'/>";
                $html .= "<input type='hidden' name='order_id' value='{$this->id}'/>";
                $html .= "<div class='row'><strong>Информация о заказе</strong>";
                $payqrFields = array(
                    "id" => "ID",
                    "status" => "Статус",
                    "confirmStatus" => "Cтатус подтверждения заказа",
                    "payqrNumber" => "Номер инвойса",
                    "orderId" => "ID заказа",
                    "amount" => "Сумма",
                    "revertAmount" => "Сумма возврата",
                );
                $invoice->revertAmount=0;
                foreach ($invoice->reverts as $item)
                {
                    if($item->status == "succeedeed")
                    {
                        $invoice->revertAmount += $item->revertedAmount;
                    }
                }
                $html .= "<table class='payqr'>";
                $k=0;
                foreach($payqrFields as $key=>$field)
                {
                    $html .= "<tr class='".($k%2 == 0 ? "odd" : "even")."'><td>{$field}</td><td>{$invoice->$key}</td></tr>";
                    $k++;
                }
                $html .= "</table></div>";
                $html .= "<div class='row'><strong>Товары в заказе</strong>";
                $html .= "<table class='payqr'><tr><td>ID</td><td>кол-во</td><td>сумма</td></tr>";
                foreach($invoice->cart as $k=>$item)
                {
                    $html .= "<tr class='".($k%2 == 0 ? "odd" : "even")."'><td>{$item->article}</td><td>{$item->quantity}</td><td>{$item->amount}</td></tr>";
                }
                $html .= "</table></div>";
                if(count($invoice->reverts)>0)
                {
                    $html .= "<div class='row'><strong>История возвратов</strong>";
                    $html .= "<table class='payqr'><tr><td>revertId</td><td>сумма</td><td>Статус</td></tr>";
                    foreach($invoice->reverts as $k=>$item)
                    {
                        $html .= "<tr class='".($k%2 == 0 ? "odd" : "even")."'><td>{$item->id}</td><td>{$item->revertedAmount}</td><td>{$item->status}</td></tr>";
                    }
                    $html .= "</table></div>";

                }
                $html .= "<div class='row'><strong>Действия</strong></div>";
                //7 cases for payqr orders
                $html .= "<div class='row'><label>Ничего не выполнять: <input type='radio' name='invoice_action' value='invoice_no_action' checked/></label></div>";
                if($invoice->status == "new")
                {
                    $html .= "<div class='row'><label>Аннулировать счет на заказ: <input type='radio' name='invoice_action' value='invoice_cancel'/></label></div>";
                }
                elseif($invoice->status != "cancelled" && $invoice->status != "failed")
                {
                    if($invoice->status == "paid" || $invoice->status == "revertedPartially")
                    {
                        $html .= "<div class='row'><label>Отменить заказ после оплаты: <input class='invoice_check' text='PayQR.invoice_revert' type='radio' name='invoice_action' value='invoice_revert'/></label>";
                        $revert_amount_value = $invoice->amount - $invoice->revertAmount;
                        $html .= "<input type='hidden' name='invoice_amount' value='{$invoice->amount}'/>";
                        $html .= "<input type='hidden' name='invoice_revertAmount' value='{$invoice->revertAmount}'/>";
                        $html .= "<div><label>Сумма возврата: <input type='text' name='invoice_revert_amount' value='$revert_amount_value' class='form-text'/></label><div>";
                        $html .= "</div>";
                    }
                    if(($invoice->status == "paid" || $invoice->status == "revertedPartially" || $invoice->status == "reverted") && $invoice->confirmStatus == "waiting")
                    {
                        $html .= "<div class='row'><label>Досрочно запустить расчеты: <input class='invoice_check' text='PayQR.invoice_confirm' type='radio' name='invoice_action' value='invoice_confirm'/></label></div>";
                    }
                    $time_since_created = round((time()-strtotime($invoice->created))/60);
                    if($time_since_created < 259200 && ($invoice->status == "paid" || $invoice->status == "revertedPartially" || $invoice->status == "reverted"));
                    {
                        $html .= "<div class='row'><label>Дослать/изменить сообщение: <input class='invoice_check' text='PayQR.invoice_message' text='PayQR.invoice_message' type='radio' name='invoice_action' value='invoice_message'/></label>";
                        $html .= "<div><label>Текст сообщения к покупке: <input type='text' name='invoice_message_text' value='' class='form-text'/></label></div>";
                        $html .= "<div><label>URL изображения для сообщения к покупке: <input type='text' name='invoice_message_image_url' value='' class='form-text'/></label></div>";
                        $html .= "<div><label>URL сайта для сообщения к покупке: <input type='text' name='invoice_message_click_url' value='' class='form-text'/></label></div></div>";
                    }
                    $html .= "<div class='row'><label>Синхронизировать статус с PayQR: <input class='invoice_check' text='PayQR.invoice_sync_data' type='radio' name='invoice_action' value='invoice_sync_data'/></label></div>";
                }
                $html .= "</div>";
                $html .= "<input type='submit' value='Выполнить' name='order_form'>";
                $html .= "</form>";
            }
            else
            {
                  $html = "<strong>Нет данных в системе PayQR</strong>";
            }
        }
        
        return $html;
    }
    
    public function handle($data)
    {
        PayqrConfig::setConfig();
        if(isset($data["invoice_action"]))
        {
            $order_id = $data["order_id"];
            $action = $data["invoice_action"];
            $invoice_id = $data["invoice_id"];
            if($this->validate($data))
            {
                $invAction = new PayqrInvoiceAction();
                switch($action)
                {
                    case "invoice_cancel":
                        $invAction->invoice_cancel($invoice_id);
                        $order = new PayqrOrder();
                        $order->cancelOrder();
                        break;
                    case "invoice_revert":
                        $revert_amount = $data["invoice_revert_amount"];
                        $invAction->invoice_revert($invoice_id, $revert_amount);
                        break;
                    case "invoice_confirm":
                        $invAction->invoice_confirm($invoice_id);
                        break;
                    case "invoice_execution_confirm":
                        $invAction->invoice_execution_confirm($invoice_id);
                        break;
                    case "invoice_message":
                        $text = $data["invoice_message_text"];
                        $image_url = $data["invoice_message_image_url"];
                        $click_url = $data["invoice_message_click_url"];
                        $invAction->invoice_message($invoice_id, $text, $image_url, $click_url);
                        break;
                    case "invoice_sync_data":
                        $order = new PayqrOrder();
                        $order->syncOrder();
                        break;
                }
            }
        }
    }

    private function validate($data)
    {
        $action = $data["invoice_action"];
        switch($action)
        {
            case "invoice_revert":
                $revert_amount = $data["invoice_revert_amount"];
                $invoice_amount = $data["invoice_amount"];
                $invoice_revertAmount = $data["invoice_revertAmount"];
                if($revert_amount > $invoice_amount-$invoice_revertAmount)
                {
                  $message = "PayQR.revert_should_be_less_then";
                  echo "<strong class='error'>$message</strong>";
                  return false;
                }
                break;
            case "invoice_message":
                $text = $data["invoice_message_text"];
                $image_url = $data["invoice_message_image_url"];
                $click_url = $data["invoice_message_click_url"];
                if(empty($text) || empty($image_url) || empty($click_url))
                {
                    $message = "Все поля должны быть заполнены";
                    echo "<strong class='error'>$message</strong>";
                    return false;
                }
                break;
        }
        return true;
    }
}