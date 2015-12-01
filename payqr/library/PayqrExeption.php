<?php
/**
 * Исключение во время работы с API PayQR
 */

class PayqrExeption extends Exception
{
    const INVOICE_ACTION_EXEPTION = 1;
    const REVERT_ACTION_EXEPTION = 2;
    const INVOICE_RECEIVER_EXEPTION = 3;
    const REVERT_RECEIVER_EXEPTION = 4;

    public $response; // объект ответа PayQR

    /**
     * Default Constructor
     *
     * @param string|null $message
     * @param int  $code
     */
    public function __construct($message = null, $code = 0, $response = false)
    {
        parent::__construct($message, $code);
        $this->response = $response;
        PayqrLog::log('Вызвано исключение : '.$this->errorMessage());
    }
    
    /**
     * prints error message
     *
     * @return string
     */
    public function errorMessage()
    {
        $errorMsg = 'Error on line ' . $this->getLine() . ' in ' . $this->getFile()
          . ': <b>' . $this->getMessage() . '</b>';
        return $errorMsg;
    }
} 