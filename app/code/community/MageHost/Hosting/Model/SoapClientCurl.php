<?php
/**
 * MageHost_Hosting
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this Module to
 * newer versions in the future.
 *
 * @category     MageHost
 * @package      MageHost_Hosting
 * @copyright    Copyright (c) 2016 MageHost BVBA (http://www.magentohosting.pro)
 */

/**
 * Class SoapClientCurl extends SoapClient __doRequest method with curl powered method
 *
 * Based on https://gist.github.com/stefanvangastel/698d5f08c7901f62744d
 */
class MageHost_Hosting_Model_SoapClientCurl extends SoapClient
{
    // Config variables
    /** @var array */
    public $curl_options = null;
    /** @var array */
    public $curl_headers = array();
    // Status variables
    public $curl_statuscode = null;
    public $curl_errorno = null;
    public $curl_errormsg = null;
    // Internal
    protected $curl = null;

    //Overwrite constructor and add our variables
    public function __construct($wsdl, $options = array())
    {
        parent::__construct($wsdl, $options);
        foreach ($options as $field => $value) {
            if (!isset($this->$field) || 0 === strpos($field,'curl_')) {
                $this->$field = $value;
            }
        }
    }

    public function __destruct()
    {
        if (!empty($this->curl)) {
            curl_close($this->curl);
        }
    }

    /**
     * Overwrite __doRequest and replace with cURL. Return XML body to be parsed with SoapClient
     *
     * @param string $request
     * @param string $location
     * @param string $action
     * @param int $version
     * @param int $one_way
     * @return mixed
     */
    public function __doRequest($request, $location, $action, $version, $one_way = 0) {
        $ch = $this->getCurl();

        curl_setopt($ch, CURLOPT_URL, $location);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        $headers = $this->curl_headers;
        $headers[] = 'Content-Type: application/xml';
        $headers[] = sprintf('SOAPAction: "%s"', $action);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        $this->curl_errorno = curl_errno($ch);
        if ($this->curl_errorno == CURLE_OK) {
            $this->curl_statuscode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }
        $this->curl_errormsg = curl_error($ch);
        if (!empty($this->curl_errorno)) {
            $errMsg = sprintf("%s:%s ERROR %d doing SOAP request: %s",
                              __CLASS__, __FUNCTION__, $this->curl_errorno, $this->curl_errormsg);
            Mage::log($errMsg, Zend_log::ERR);
        }

        return ($one_way) ? null : $response;
    }

    protected function getCurl() {
        if (empty($this->curl)) {
            $this->curl = curl_init();
            curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->curl, CURLOPT_TIMEOUT, ini_get('default_socket_timeout'));
            curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, false);
            if (!empty($this->curl_options)) {
                curl_setopt_array($this->curl, $this->curl_options);
            }
        }
        return $this->curl;
    }
}