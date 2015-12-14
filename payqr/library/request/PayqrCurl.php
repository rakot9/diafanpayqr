<?php
/**
 * Обертка для cURL
 */
 
class PayqrCurl extends PayqrRequest
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
        $this->error = '';
        $this->request = curl_init();
        if (is_array($vars)) {
            $vars = http_build_query($vars, '', '&');
        }

        $this->set_request_method($method);
        $this->set_request_options($url, $vars);
        $this->set_request_headers($vars);

        $rawResponse = curl_exec($this->request);
        $response = $this->check_response($rawResponse, $method, $url, $vars);

        curl_close($this->request);
        return $response;
    }

    /**
     * Formats and adds custom headers to the current request
     *
     * @return void
     * @access protected
     **/
    protected function set_request_headers($vars)
    {
        $this->headers['Content-Length'] = strlen($vars);
        $headers = array();
        foreach ($this->headers as $key => $value) {
            $headers[] = $key . ': ' . $value;
        }
        curl_setopt($this->request, CURLOPT_HTTPHEADER, $headers);
    }

    /**
     * Set the associated CURL options for a request method
     *
     * @param string $method
     * @return void
     * @access protected
     **/
    protected function set_request_method($method)
    {
        switch (strtoupper($method)) {
            case self::GET:
                curl_setopt($this->request, CURLOPT_HTTPGET, true);
                break;
            case self::POST:
                curl_setopt($this->request, CURLOPT_POST, true);
            case self::PUT:
                curl_setopt($this->request, CURLOPT_POST, true);
                break;
            default:
                curl_setopt($this->request, CURLOPT_CUSTOMREQUEST, $method);
        }
    }

    /**
     * Sets the CURLOPT options for the current request
     *
     * @param string $url
     * @param string $vars
     * @return void
     * @access protected
     **/
    protected function set_request_options($url, $vars)
    {
        curl_setopt($this->request, CURLOPT_URL, $url);
        if (!empty($vars)) {
            curl_setopt($this->request, CURLOPT_POSTFIELDS, $vars);
        }

        # Set some default CURL options
        curl_setopt($this->request, CURLOPT_HEADER, true);
        curl_setopt($this->request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->request, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->request, CURLOPT_CONNECTTIMEOUT, intval(PayqrConfig::$maxTimeOut));
    }
}