<?php
/**
 * Anubis Service Class
 * 
 * @todo refactoring would be nice (soap requests differ only in params, so this solution is not really DRY)
 */
class AnubisService
{
    public $enableDebug = true;

    // connection params
    public $userName      = ANUBIS_USER_NAME;
    public $password      = ANUBIS_PASSWORD;
    public $mandatorId    = ANUBIS_MANDATOR_ID;
    public $customerId    = ANUBIS_CUSTOMER_ID;
    public $contractId    = ANUBIS_CONTRACT_ID;
    public $globalOrderId = ANUBIS_GLOBAL_ORDER_ID;
    public $sourceId      = ANUBIS_SOURCE_ID;
    public $orderNumber   = ANUBIS_ORDER_NUMBER;

    // filter constants
    const FILTER_NO_CLOSED_CONTRACTS = 'NO_CLOSED_CONTRACTS';

    // exception constants
    const EX_WEB_NO_SUBSCRIPTIONS_FOUND = 'WEB-NO-SUBSCRIPTIONS-FOUND';

    /**
     * check if a customer has one or more subscriptions
     *
     * @param string $email - customer email
     * @param string $zipcode - customer zipcode
     * @return boolean
     */
    public function hasSubscriptions($email, $zipcode)
    {
        return $this->getSubscriptionCount($email, $zipcode) > 0;
    }

    /**
     * check if there are subscriptions for the customer with the specified number
     * @param string $customerNumber - the customer number
     * @return boolean
     */
    public function hasSubscriptionsByCustomerNumber($customerNumber)
    {
        return $this->getSubscriptionCountByCustomerNumber($customerNumber) > 0;
    }

    /**
     * create connection
     *
     * @return SoapClient
     */
    private function getSoapConnection()
    {
        //Path to wsdl
        $wsdl = __DIR__ . '/contract.wsdl';
        //options
        $options = array(
            'location' => "https://epsb-ws.verlagsinfo.de/epsb-ws/ContractService/Contract",
            'uri'      => "http://order.ws.epsb.bitech.de",
            'trace'    => $this->enableDebug,
        );
        //Create the a new Soap Client
        return new SoapClient($wsdl, $options);
    }

    /**
     * get count of subscriptions for customer
     *
     * @param string $email - customer email
     * @param string $zipcode - customer zipcode
     * @return integer
     */
    private function getSubscriptionCount($email, $zipcode)
    {
        $subscriptions = $this->getCustomerSubscriptions($email, $zipcode);

        if (false === $subscriptions) {
            $count = 0;
        } else {
            $count = count($subscriptions);
        }

        return $count;
    }
	

    /**
     * get count of subscriptions for customer
     *
     * @param string $email - customer email
     * @param string $zipcode - customer zipcode
     * @return integer
     */
    private function getSubscriptionCountByCustomerNumber($customerNumber)
    {
        $subscriptions = $this->getCustomerSubscriptionsByCustomerNumber($customerNumber);

        if (false === $subscriptions) {
            $count = 0;
        } else {
            $count = count($subscriptions);
        }

        return $count;
    }

    /**
     * get subscriptions based on customer email and zipcode
     *
     * @param string $email - customer email
     * @param string $zipcode - customer zipcode
     * @param string $filterLogic - filter logic to apply, see api documentation
     * @return mixed - array of subscription objects; false if no client has been found
     */
    public function getCustomerSubscriptions($email, $zipcode, $filterLogic = self::FILTER_NO_CLOSED_CONTRACTS)
    {

        $client = $this->getSoapConnection();

        $param = array('username' => new SoapVar($this->userName, XSD_STRING),
            'password'               => new SoapVar($this->password, XSD_STRING),
            'mandatorId'             => $this->mandatorId,
            // 'customerId'             => $this->customerId,
            // 'contractId'             => $this->contractId,
         
            'zipCode'                => $zipcode,
            'eMail'                  => $email,
            'filterLogic'            => $filterLogic,
          
            // 'orderNumber'            => $this->orderNumber,
            //    ,'productShortname' => "SPC"
        );

        try {
            $result = $client->__soapCall("readCustomerSubscriptions", array('parameters' => $param));
        } catch (Exception $ex) {
            echo '</pre>';
            // no customer found equals exception

            if (self::EX_WEB_NO_SUBSCRIPTIONS_FOUND == $this->getExceptionMessage($ex)) {
                $result = false;
            }
        }

        if ($this->enableDebug) {

            $this->debug($client);
        }

        return false === $result ? false : $result->customerSubscriptions;
    }

    /**
     * get subscriptions based on customer email and zipcode
     *
     * @param string $email - customer email
     * @param string $zipcode - customer zipcode
     * @param string $filterLogic - filter logic to apply, see api documentation
     * @return mixed - array of subscription objects; false if no client has been found
     */
    public function getCustomerSubscriptionsByCustomerNumber($customerNumber, $filterLogic = self::FILTER_NO_CLOSED_CONTRACTS)
    {

        $client = $this->getSoapConnection();

        // die($customerNumber);

        $param = array('username' => new SoapVar($this->userName, XSD_STRING),
            'password'               => new SoapVar($this->password, XSD_STRING),
            'mandatorId'             => $this->mandatorId,
            'customerIdStr'          => $customerNumber,
            // 'contractId'             => $this->contractId,
            'filterLogic'            => $filterLogic,
            // 'orderNumber'            => $this->orderNumber,
            //    ,'productShortname' => "SPC"
        );

        try {
            $result = $client->__soapCall("readCustomerSubscriptions", array('parameters' => $param));
        } catch (Exception $ex) {
            echo '</pre>';
            // no customer found equals exception

            if (self::EX_WEB_NO_SUBSCRIPTIONS_FOUND == $this->getExceptionMessage($ex)) {
                $result = false;
            }
        }

        if ($this->enableDebug) {

            $this->debug($client);
        }

        return false === $result ? false : $result->customerSubscriptions;
    }

    /**
     * extract the exception message
     *
     * @param Exception $ex
     * @return string
     */
    private function getExceptionMessage($ex)
    {
        $message  = $ex->getMessage();
        $msgParts = explode(':', $message);
        return $msgParts[0];

    }

    /**
     * write request and response into debug file
     *
     * @param SoapClient $client
     * @return void
     */
    private function debug(SoapClient $client)
    {
        $debugFileName = __DIR__ . '/debug.log';

        if (file_exists($debugFileName)) {
            $log = file_get_contents($debugFileName);
        } else {
            $log = '';
        }

        $date = new DateTime();

        $log .= 'Date: ' . $date->format('Y-m-d H:i:s') . PHP_EOL;
        $log .= 'Last Request: ' . PHP_EOL;
        $log .= '=============' . PHP_EOL;
        $log .= $client->__getLastRequest();
        $log .= PHP_EOL . PHP_EOL;

        $log .= 'Last Request Headers: ' . PHP_EOL;
        $log .= '=====================' . PHP_EOL;
        $log .= $client->__getLastRequestHeaders();
        $log .= PHP_EOL . PHP_EOL;

        $log .= 'Last Response: ' . PHP_EOL;
        $log .= '=====================' . PHP_EOL;
        $log .= $client->__getLastResponse();
        $log .= PHP_EOL . PHP_EOL;

        $log .= 'Last Response Headers: ' . PHP_EOL;
        $log .= '=====================' . PHP_EOL;
        $log .= $client->__getLastResponseHeaders();
        $log .= PHP_EOL . PHP_EOL;

        file_put_contents($debugFileName, $log);
    }
}
