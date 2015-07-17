<?php

namespace Omnipay\Realex\Message;

use Omnipay\Realex\Message\CreatePayerResponse;
/**
 * Realex Create Payer Request
 *
 * Prior to adding a credit card (and thus getting a token) you must
 * create a payer to assign the card to.
 *
 */
class CreatePayerRequest extends RemoteAbstractRequest
{

    protected $endpoint = 'https://epage.payandshop.com/epage-remote-plugins.cgi ';

    /**
     * This needs to be unique, ideally if you have a
     * customer table, is the primary key field (id)
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setPayerReference($value)
    {
        return $this->setParameter('payerRef', $value);
    }

    /**
     * Customer Title (Mr, Mrs etc)
     *
     * @param $value
     */
    public function setTitle($value)
    {
        $this->setParameter('payerTitle', $value);
    }

    /**
     * Customer firstname (e.g Margaret, Rob)
     * @param $value
     */
    public function setFirstname($value)
    {
        $this->setParameter('payerFirstname', $value);
    }

    /**
     * Customer Surname (e.g Van Der Brooke)
     * @param $value
     */
    public function setSurname($value)
    {
        $this->setParameter('payerSurname', $value);
    }


    /**
     * Return the payer reference
     *
     * @return mixed
     */
    public function getPayerReference()
    {
        return $this->getParameter('payerRef');
    }


    /**
     * Return customer title
     */
    public function getTitle()
    {
        $this->getParameter('payerTitle');
    }

    /**
     * Return customer firstname
     */
    public function getFirstname()
    {
        $this->getParameter('payerFirstname');
    }

    /**
     * Return customer surname
     */
    public function getSurname()
    {
        $this->getParameter('payerSurname');
    }

    /**
     * Return the data required by this request
     *
     * @return string
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        $this->validate('amount', 'currency', 'transactionId');

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
        $root->setAttribute('type', 'payer-new');
        $root->setAttribute('timestamp', $timestamp);
        $root = $domTree->appendChild($root);

        // merchant ID
        $merchantEl = $domTree->createElement('merchantid', $merchantId);
        $root->appendChild($merchantEl);

        // Unique id for this request
        $merchantEl = $domTree->createElement('orderid', $orderId);
        $root->appendChild($merchantEl);

        // Payer reference
        $payer = $domTree->createElement('payer');
        $payer->setAttribute('type', 'Website Customer');
        $payer->setAttribute('ref', $payerRef);
        $root = $domTree->appendChild($payer);

        // Title
        $title = $domTree->createElement('title', $this->getTitle());
        $root->appendChild($title);

        // Firstname
        $firstname = $domTree->createElement('firstname', $this->getFirstname());
        $root->appendChild($firstname);

        // Surname
        $surname = $domTree->createElement('surname', $this->getSurname());
        $root->appendChild($surname);

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
        return $this->response = new CreatePayerResponse($this, $data);
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }
}