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



    public function getCardRef(){
        return str_replace(' ', '', $this->getParameter('cardRef'));
    }

    public function setCardRef($value){
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

        //$this->validate('amount', 'currency', 'transactionId');

        // Build the hash string
        $timestamp = strftime("%Y%m%d%H%M%S");
        $merchantId = $this->getMerchantId();
        $orderId = time() + rand(0, 1000); // Random number
        $payerRef = $this->getPayerReference();

        // Get the credit card
        $credit_card = $this->getCard();

        $secret = $this->getSecret();
        $tmp = "$timestamp.$merchantId.$orderId..." . $payerRef . "." . $credit_card->getName() . "." . $credit_card->getNumber();


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

        // Create the card
        $card =  $domTree->createElement('card');

        // Payer ref. The person this card is being assigned to
        $card->appendChild($domTree->createElement('payerref', $this->getPayerReference()));


        // Card Reference (Customers name for it, e.g. Current Account)
        $card->appendChild($domTree->createElement('ref', $this->getCardRef()));

        // Card Number
        $card->appendChild($domTree->createElement('number', $credit_card->getNumber()));

        // Expiry date
        $card->appendChild($domTree->createElement('expdate', $credit_card->getExpiryDate('my')));

        // Card holder name
        $card->appendChild($domTree->createElement('chname', $credit_card->getName()));

        // Type or Brand (e.g. VISA - Auto calculated)
        $card->appendChild($domTree->createElement('type', $credit_card->getBrand()));


        $root->appendChild($card);

        // Issue number?
        // To add

        // Hash
        $sha1El = $domTree->createElement('sha1hash', $sha1hash);
        $root->appendChild($sha1El);

        $xmlString = $domTree->saveXML($root);

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