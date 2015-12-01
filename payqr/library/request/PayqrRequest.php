<?php
/**
 * Обработка результатов запросов cURL
 */
 
class PayqrRequest
{
    const GET = "GET";
    const POST = "POST";
    const PUT = "PUT";
    const DELETE = "DELETE";

    /**
     * The body of the response without the headers block
     *
     * @var string
     **/
    public $body = '';

    /**
     * An associative array containing the response's headers
     *
     * @var array
     **/
    public $headers = array();
    
    
    /**
     * Makes an HTTP GET request to the specified $url with an optional array or string of $vars
     *
     * Returns a PayqrCurl object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars
     * @return PayqrCurl
     **/
    public function get($url, $vars = array())
    {
        if (!empty($vars)) {
          $url .= (stripos($url, '?') !== false) ? '&' : '?';
          $url .= (is_string($vars)) ? $vars : http_build_query($vars, '', '&');
        }
        return $this->request(self::GET, $url);
    }

    /**
     * Makes an HTTP POST request to the specified $url with an optional array or string of $vars
     *
     * @param string $url
     * @param array|string $vars
     * @return PayqrCurl|boolean
     **/
    public function post($url, $vars = array())
    {
      return $this->request(self::POST, $url, $vars);
    }

    /**
     * Makes an HTTP PUT request to the specified $url with an optional array or string of $vars
     *
     * Returns a PayqrCurl object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars
     * @return PayqrCurl|boolean
     **/
    public function put($url, $vars = array())
    {
        return $this->request(self::PUT, $url, $vars);
    }

    /**
     * Accepts the result of a curl request as a string
     * @param string $response
     **/
    private function trim_headers($response)
    {
        # Headers regex
        $pattern = '#HTTP/\d\.\d.*?$.*?\r\n\r\n#ims';

        # Extract headers from response
        preg_match_all($pattern, $response, $matches);
        $headers_string = array_pop($matches[0]);
        $headers = explode("\r\n", str_replace("\r\n\r\n", '', $headers_string));

        # Remove headers from the response body
        $this->body = str_replace($headers_string, '', $response);

        # Extract the version and status from the first header
        $version_and_status = array_shift($headers);
        preg_match('#HTTP/(\d\.\d)\s(\d\d\d)\s(.*)#', $version_and_status, $matches);
        $this->headers['Http-Version'] = $matches[1];
        $this->headers['Status-Code'] = $matches[2];
        $this->headers['Status'] = $matches[2] . ' ' . $matches[3];

        # Convert headers into an associative array
        foreach ($headers as $header) {
            preg_match('#(.*?)\:\s(.*)#', $header, $matches);
            $this->headers[$matches[1]] = $matches[2];
        }
    }
    
    /**
    * Проверяем ответ от PayQR на отправленный запрос в PayQR
    */
    public function check_response($rawResponse, $method, $url, $vars)
    {
        $message = "Method: {$method}\nUrl: {$url}\nParams: ".var_export($vars, true)."\nResponse: {$rawResponse}";
        PayqrLog::log($message);
        if ($rawResponse) 
        {
            $this->trim_headers($rawResponse);
        } 
        else 
        {
            $error = curl_errno($this->request) . ' - ' . curl_error($this->request);
            throw new PayqrExeption("Ошибка при запросе $method, $url, ".var_export($vars, true).", $error", 0, $rawResponse);
        }
    
        // Проверяем что ответ не пустой
        if (empty($this))
        {
            throw new PayqrExeption("Получен пустой ответ", 0);
        }
        // Проверяем код ответа
        if (!isset($this->headers['Status-Code']))
        {
            throw new PayqrExeption("Отсутствует заголовок с кодом ответа ".var_export($this, true), 0, $this);
        }
        // Проверяем код ответа
        if ($this->headers['Status-Code'] != '200')
        {
            throw new PayqrExeption("Получен ответ с кодом ошибки ".$this->headers['Status-Code']." ".  var_export($this, true), 0, $this);
        }
        // Проверяем заголовок ответа
        if (!PayqrAuth::checkHeader(PayqrConfig::$secretKeyIn, $this->headers))
        {
            throw new PayqrExeption("Неверный параметр ['PQRSecretKey'] в headers ответа".var_export($this, true), 0, $this);
        }
        return $this;
    }
}