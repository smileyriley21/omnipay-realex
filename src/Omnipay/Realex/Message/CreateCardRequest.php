<?php

namespace Omnipay\Realex\Message;

use Omnipay\Realex\Message\CreateCardResponse;
/**
 * Realex Create Card Request
 *
 * Add a new card to the Realex system
 *
 */
class CreateCardRequest extends RemoteAbstractRequest
{

    protected $endpoint = 'https://epage.payandshop.com/epage-remote-plugins.cgi';


    public function getCardReference(){
        return $this->getParameter('cardRef');
    }

    public function setCardReference($value){
        $this->setParameter('cardRef', $value);
    }

    /**
     * Return the data required by this request
     *
     * @return string
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {


        // $this->validate('amount', 'currency', 'transactionId');

        // Build the hash string
        $timestamp = strftime("%Y%m%d%H%M%S");
        $merchantId = $this->getMerchantId();
        $orderId = time() + rand(0, 1000); // Random number
        $payerRef = $this->getPayerReference();


        $secret = $this->getSecret();
        $tmp = "$timestamp.$merchantId.$orderId...$payerRef";
        $sha1hash = sha1($tmp);
        $tmp2 = "$sha1hash.$secret";
        $sha1hash = sha1($tmp2);


        // Its DOM time
        $domTree = new \DOMDocument('1.0', 'UTF-8');

        // root element
        $root = $domTree->createElement('request');
        $root->setAttribute('type', 'card-new');
        $root->setAttribute('timestamp', $timestamp);
        $root = $domTree->appendChild($root);

        // merchant ID
        $root->appendChild($domTree->createElement('merchantid', $merchantId));

        // Unique id for this request (normally use timestamp + rand(1,5000) or similar
        $root->appendChild($domTree->createElement('orderid', $orderId));

        // Payer ref. The person this card is being assigned to
        $root->appendChild($domTree->createElement('payerref', $this->getPayerReference()));

        // Card Reference (Customers name for it, e.g. Current Account)
        $root->appendChild($domTree->createElement('cardref', $this->getCardRef()));

        // Card Number
        $root->appendChild($domTree->createElement('number', $this->getNumber()));

        // Card Number
        $root->appendChild($domTree->createElement('number', $this->getExpMonth() . $this->getExpYear()));

        // Hash
        $sha1El = $domTree->createElement('sha1hash', $sha1hash);
        $root->appendChild($sha1El);

        $xmlString = $domTree->saveXML($root);

        dd($xmlString);

        return $xmlString;


    }


    /**
     * Lets do this.. return the response
     *
     * @param $data
     * @return CreatePayerResponse
     */
    protected function createResponse($data)
    {
        return $this->response = new CreateCardResponse($this, $data);
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }
}