<?php
/**
 * Прием уведомлений от PayQR
 */

class PayqrReceiver
{
    const INVOICE = 'invoice';
    const REVERT = 'revert';
    const OFFER = 'offer';
    const RECEIPT = 'receipt';

    private $type;
    private $event;
    private $objectEvent;
    
    /**
     * объект события
     */
    private $objectHandler;
    
    public function __construct()
    {
        $this->setModuleConfig();
        PayqrBase::checkConfig();
    }

        /**
     * Задаём настройки
     */
    private function setModuleConfig()
    {
        $user_id = isset($_GET["user_id"]) ? $_GET["user_id"] : 1;
        $auth = new PayqrModuleAuth($user_id);
        
        $module = new PayqrModule();
        PayqrConfig::$merchantID = $module->getOption("merchantID");
        PayqrConfig::$secretKeyIn = $module->getOption("secretKeyIn");
        PayqrConfig::$secretKeyOut = $module->getOption("secretKeyOut");
    }

    /**
     * Получаем уведомление
     */
    public function receive()
    {
        // Получаем данные из тела запроса
        $json = file_get_contents('php://input');
        // логируем тело запроса
        PayqrLog::log("REQUEST\n" . $json);
        
        // Проверяем валидность JSON данных
        PayqrJsonValidator::validate($json);
        // Получаем объект события из уведомления
        $this->objectEvent = json_decode($json);
        
        // Проверяем что уведомление действительно от PayQR (по секретному ключу SecretKeyIn)
        if (!PayqrAuth::checkHeader(PayqrConfig::$secretKeyIn)) {
            // если уведомление пришло не от PayQR, вызываем исключение
            throw new PayqrExeption("Неверный параметр ['PQRSecretKey'] в header уведомления", 1);
        }

        // Проверяем наличие свойства типа объекта
        if (!isset($this->objectEvent->object) || !isset($this->objectEvent->id) || !isset($this->objectEvent->type)) {
            throw new PayqrExeption("В уведомлении отстутствуют обязательные параметры object, id, type", 1, $json);
        }
        
        $this->event = $this->objectEvent->type;
        $this->type = $this->objectEvent->data->object;
        
        PayqrLog::log("Тип события: " . $this->event . "\nИдентификатор события: " . $this->objectEvent->data->id);
        
        // В зависимости от того какого типа уведомление, создаем объект
        switch ($this->type) 
        {
            case self::INVOICE:
                $this->objectHandler = new PayqrInvoice($this->objectEvent);
                break;
            case self::REVERT:
                $this->objectHandler = new PayqrRevert($this->objectEvent);
                break;
            case self::OFFER:
                $this->objectHandler = new PayqrOffer($this->objectEvent);
                break;
            case self::RECEIPT:
                $this->objectHandler = new PayqrReceipt($this->objectEvent);
                break;
            default:
                throw new PayqrExeption("В уведомлении отстутствуют обязательные параметры object id type", 1, $json);
                return;
        }
        // если все прошло успешно, возвращаем объект
        return $this->objectHandler;
    }

    /**
     * Ответ на уведомление
     */
    public function response()
    {
        header("PQRSecretKey:" . PayqrConfig::$secretKeyOut);
        // если счет отмечен как отмененный
        if($this->objectHandler->cancel)
        { 
            header("HTTP/1.1 409 Conflict");
            PayqrLog::log('payqr_receiver::response() - Send HTTP/1.1 409 Conflict');
            return;
        }
        header("HTTP/1.1 200 OK");
        $json = json_encode($this->objectEvent);
        PayqrLog::log("RESPONSE\n" . $json);
        echo $json;
    }

    /**
     * Обработка входящего запроса
     */
    public function handle($diafan)
    {
        $object = $this->receive();
        switch ($this->type) 
        {
            case self::INVOICE:  
            $invoice = new InvoiceHandler($object, $diafan);
                switch ($this->event)
                {            
                    case 'invoice.order.creating': 
                        // нужно создать заказ в своей учетной системе, если заказ еще не был создан, и вернуть в PayQR полученный номер заказа (orderId), если его еще не было
                        $invoice->createOrder();
                        break;
                    case 'invoice.paid':
                        // нужно зафиксировать успешную оплату конкретного заказа
                        $invoice->payOrder();
                        break;
                    case 'invoice.reverted':
                        // PayQR зафиксировал полную отмену конкретного счета (заказа) и возврат всей суммы денежных средств по нему
                        $invoice->revertOrder();
                        break;
                    case 'invoice.cancelled':
                        // PayQR зафиксировал отмену конкретного заказа до его оплаты
                        $invoice->cancelOrder();
                        break;
                    case 'invoice.failed':
                        // ошибка совершения покупки, операция дальше продолжаться не будет
                        $invoice->failOrder();
                        break;
                    case 'invoice.deliverycases.updating':
                        // нужно вернуть в PayQR список способов доставки для покупателя
                        $invoice->setDeliveryCases();
                        break;
                    case 'invoice.pickpoints.updating':
                        // нужно вернуть в PayQR список пунктов самовывоза для покупателя
                        $invoice->setPickPoints();
                        break;
                }
                break;
            case self::REVERT:
                $revert = new RevertHandler($object);
                switch ($this->event)            
                {
                    case 'revert.failed':
                        // PayQR отказал интернет-сайту в отмене счета и возврате денежных средств покупателю
                        $revert->fail();
                        break;
                    case 'revert.succeeded':
                        // PayQR зафиксировал отмену счета интернет-сайтом и вернул денежные средства покупателю
                        $revert->succeed();
                        break;
                }
                break;
            case self::OFFER:
                $offer = new OfferHandler($object);
                switch ($this->event)            
                {
                    case 'offer.invoices.creating':
                        // PayQR отказал интернет-сайту в отмене счета и возврате денежных средств покупателю
                        $offer->create();
                        break;
                }
                break;
            case self::RECEIPT:
                $receit = new ReceiptHandler($object);
                switch ($this->event)            
                {
                    case 'receipt.paid':
                        // PayQR отказал интернет-сайту в отмене счета и возврате денежных средств покупателю
                        $receit->pay();
                        break;
                }
                break;
            default:
        }
        $this->response();
    }
} 