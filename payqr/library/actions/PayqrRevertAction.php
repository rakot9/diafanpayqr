<?php
/**
 * Методы по объектам "Возвраты"
 * Подробнее https://payqr.ru/api/ecommerce#reverts
 */
 
class PayqrRevertAction extends PayqrAction
{
    /**
     * Получить информацию о возврате по его идентификатору в PayQR (актуализировать)
     * https://payqr.ru/api/ecommerce#revert_get
     *
     * @param $revert_id
     * @return mixed|string
     *
     * GET https://payqr.ru/shop/api/1.0/reverts/{id}
     *
     * curl "https://payqr.ru/shop/api/1.0/reverts/{id}"
     */
    public function get_revert($revert_id)
    {
        try{
            return $this->get(PayqrBase::$apiUrl.'/reverts/'.$revert_id);
        }
        catch(PayqrExeption $e){
            $this->require_handler($e, __FUNCTION__);
        }
    }

    /**
     * Проверяет наличие файла обработчика и подключает его
     * @param PayqrExeption $e
     * @param $functionName
     */
    private function require_handler(PayqrExeption $e, $functionName)
    {
        if(file_exists(PAYQR_ERROR_HANDLER.'invoice_action_error.php'))
        {
            $response = $e->response;
            require PAYQR_ERROR_HANDLER.'revert_action_error.php';
        }
    }
} 