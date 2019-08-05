<?php

namespace Omnipay\Realex\Message;

use Omnipay\Realex\Message\Create3DSecureVersionResponse;



class Check3DSecureVersionRequest extends RemoteAbstractRequest
{

    //protected $endpoint = 'https://epage.payandshop.com/epage-remote-plugins.cgi';
    //protected $endpoint = 'https://api.globalpay.com/3ds-protocol-versions';
    protected $endpoint = 'https://authentications.sandbox.realexpayments.com/3ds/protocol-versions';



    /**
     * Return the data required by this request
     *
     * @return string
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {



        // Get required fields
        $timestamp = strftime("%Y%m%d%H%M%S");
        $merchantId = $this->getMerchantId();
        $account = $this->getAccount();
        $creditCard = $this->getCard();
        $cardNumber = $creditCard->getNumber();
        $secret = $this->getSecret();

        // Build the Hash string
        $tmp = "$timestamp.$merchantId.$cardNumber";

        $sha1hash = sha1($tmp);
        $tmp2 = "$sha1hash.$secret";
        $sha1hash = sha1($tmp2);


        // root element


        $data=[
            'request_timestamp' => $timestamp,
            'merchant_id' => $merchantId,
            'account_id' => $account,
            'number' => $cardNumber,
            'scheme' => strtoupper($creditCard->getBrand()),
            'method_notification_url'=>''

        ];


        $headers=[
            'Content-type'=>'application/json',
            'Authorization'=> 'securehash ' . $sha1hash

        ];

        $response = [
                'headers'=>$headers,
                'data'=>json_encode($data)
        ];

        return $response;

    }

    /**
     * Lets do this.. return the response
     *
     * @param $data
     * @return CreatePayerResponse
     */
    protected function createResponse($data)
    {
        return $this->response = new Check3DSecureVersionResponse($this, $data);
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }
}