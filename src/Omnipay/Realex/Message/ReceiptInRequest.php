<?php

namespace Omnipay\Realex\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;

/**
 * Realex Auth Request
 */
class ReceiptInRequest extends RemoteAbstractRequest
{
    protected $endpoint = 'https://epage.payandshop.com/epage-remote-plugins.cgi';


    /**
     * Get the XML registration string to be sent to the gateway
     *
     * @return string
     */
    public function getData()
    {
        $this->validate('amount', 'currency', 'transactionId');

        // Create the hash
        $timestamp = strftime("%Y%m%d%H%M%S");
        $merchantId = $this->getMerchantId();
        $orderId = $this->getTransactionId();
        $amount = $this->getAmountInteger();
        $currency = $this->getCurrency();
        $payerRef = $this->getPayerReference();
        $cardRef = $this->getCardRef();
        $secret = $this->getSecret();
        $tmp = "$timestamp.$merchantId.$orderId.$amount.$currency.$payerRef";
        $sha1hash = sha1($tmp);
        $tmp2 = "$sha1hash.$secret";
        $sha1hash = sha1($tmp2);

        $domTree = new \DOMDocument('1.0', 'UTF-8');

        // root element
        $root = $domTree->createElement('request');
        $root->setAttribute('type', 'receipt-in');
        $root->setAttribute('timestamp', $timestamp);
        $root = $domTree->appendChild($root);

        // merchant ID
        $merchantEl = $domTree->createElement('merchantid', $merchantId);
        $root->appendChild($merchantEl);

        // account
        $merchantEl = $domTree->createElement('account', $this->getAccount());
        $root->appendChild($merchantEl);

        // order ID
        $orderId = $domTree->createElement('orderid', $orderId);
        $root->appendChild($orderId);

        // Payment data
        $paymentData = $domTree->createElement('paymentdata');

        // Auto settle
        $autoSettle = $domTree->createElement('autosettle');
        $autoSettle->setAttribute('flag', "1");
        $paymentData->appendChild($autoSettle);

        // amount
        $amountEl = $domTree->createElement('amount', $amount);
        $amountEl->setAttribute('currency', $this->getCurrency());
        $paymentData->appendChild($amountEl);

        // Payer ref. The person this card is being assigned to
        $paymentData->appendChild($domTree->createElement('payerref', $payerRef));

        // Card Reference (Customers name for it, e.g. Current Account and that in Realex)
        $paymentData->appendChild($domTree->createElement('paymentmethod', $cardRef));

        // Add to root node
        $root->appendChild($paymentData);

        $xmlString = $domTree->saveXML($root);

        // dd($xmlString);

        return $xmlString;
    }

    protected function createResponse($data)
    {
        return $this->response = new ReceiptInResponse($this, $data);
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }
}
