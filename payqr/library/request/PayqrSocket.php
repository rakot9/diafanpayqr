<?php

/**
 * Замена cURL в отправке запросов в PayQR, если cURL отсутствует
 */

class PayqrSocket extends PayqrRequest
{
    /**
     * Makes an HTTP request of the specified $method to a $url with an optional array or string of $vars
     *
     * Returns a PayqrCurl object if the request was successful, false otherwise
     *
     * @param string $method
     * @param string $url
     * @param array|string $vars
     * @return PayqrRequest|boolean
     **/
    public function request($method, $url, $vars = array())
    {        
        if (is_array($vars))
        {
            $vars = http_build_query($vars, '', '&');
        }
        $this->headers['Content-Length'] = strlen($vars);
        
        $url_obj = parse_url($url);
        if (!isset($url_obj['host'])) 
        {
            PayqrLog::log(__FILE__."\n\r".__METHOD__."\n\r L:".__LINE__."\n\r Неверный параметр url: " . $url ." не удалось получить host для запроса");
            return false;
        }
        $host = $url_obj['host'];

        // port 443 for ssl
        $fp = fsockopen("ssl://" . $host, 443, $errno, $errstr, intval(PayqrConfig::$maxTimeOut));
        if (!$fp) {
          PayqrLog::log("$errstr ($errno)\n");
        } 
        else 
        {
            $rawRequest = "$method $url HTTP/1.1\r\n";
            $rawRequest .= "Host: $host\r\n";
            $rawRequest .= "Accept: */*\r\n";
            if (is_array($this->headers)) 
            {
                foreach ($this->headers as $key => $value) {
                    $rawRequest .= "$key: $value\r\n";
                }
            }
            $rawRequest .= "Connection: close\r\n\r\n";
            if(!empty($vars))
            {
                $rawRequest .= "$vars\r\n\r\n";
            }
            fwrite($fp, $rawRequest);
            $rawResponse = "";
            while (!feof($fp)) {
                $rawResponse .= fgets($fp, 1024);
            }
            fclose($fp);
            $response = $this->check_response($rawResponse, $method, $url, $vars);
            return $response;
        }
    }
} 