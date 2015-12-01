<?php
/**
 * Реализация запросов в PayQR
 */
 
class PayqrAction
{
    protected $request;

    public function __construct()
    {
        if(function_exists('curl_init'))
        {
            $this->request = new PayqrCurl();
        }
        else
        {
            $this->request = new PayqrSocket();
        }
        $this->request->headers['Content-Type'] = 'application/json';
        $this->request->headers['PQRSecretKey'] = PayqrConfig::$secretKeyOut;
        // Чтобы убрать из заголовков HTTP/1.1 100 Continue
        $this->request->headers['Expect:'] = "";
    }

    /**
     * Отправка POST-запроса
     * @param $url
     * @param array $vars
     * @return mixed|string
     */
    public function post($url, $vars = array())
    {
        $this->request->post($url, $vars);
        return $this->getObject();
    }

    /**
     * Отправка GET-запроса
     * @param $url
     * @param array $vars
     * @return mixed|string
     */
    public function get($url, $vars = array())
    {
        $this->request->get($url, $vars);
        return $this->getObject();
    }

    /**
     * Отправка PUT-запроса
     * @param $url
     * @param array $vars
     * @return mixed|string
     */
    public function put($url, $vars = array())
    {
        $this->request->put($url, $vars);
        return $this->getObject();
    }
    
    /*
     * Проверяем строку на валидный json и возвращаем декодируемый объект
     */
    private function getObject()
    {
        PayqrJsonValidator::validate($this->request->body);
        return json_decode($this->request->body);
    }
} 