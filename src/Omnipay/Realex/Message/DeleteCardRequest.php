<?php

namespace Omnipay\Realex\Message;

use Omnipay\Realex\Message\DeleteCardResponse;
/**
 * Realex Delete a Credit/Debit Card
 *
 * Remove a card from the Realex system
 *
 */
class DeleteCardRequest extends RemoteAbstractRequest
{

    //protected $endpoint = 'https://epage.payandshop.com/epage-remote-plugins.cgi';
    protected $endpoint = 'https://api.realexpayments.com/epage-remote-plugins.cgi';

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
        $cardRef = $this->getCardRef();


        $secret = $this->getSecret();
        $tmp = "$timestamp.$merchantId.$payerRef.$cardRef";


        $sha1hash = sha1($tmp);
        $tmp2 = "$sha1hash.$secret";
        $sha1hash = sha1($tmp2);

        // Its DOM time
        $domTree = new \DOMDocument('1.0', 'UTF-8');

        // root element
        $root = $domTree->createElement('request');
        $root->setAttribute('type', 'card-cancel-card');
        $root->setAttribute('timestamp', $timestamp);
        $root = $domTree->appendChild($root);

        // merchant ID
        $root->appendChild($domTree->createElement('merchantid', $merchantId));


        // Create the card
        $card =  $domTree->createElement('card');

        // Payer ref. The person this card is being assigned to
        $card->appendChild($domTree->createElement('payerref', $this->getPayerReference()));

        // Card Reference (Customers name for it, e.g. Current Account)
        $card->appendChild($domTree->createElement('ref', $this->getCardRef()));

        // Add the card to the root node
        $root->appendChild($card);


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
        return $this->response = new DeleteCardResponse($this, $data);
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }
}