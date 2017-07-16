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

    //protected $endpoint = 'https://epage.payandshop.com/epage-remote-plugins.cgi';
    protected $endpoint = 'https://api.realexpayments.com/epage-remote-plugins.cgi';



    /**
     * Customer Title (Mr, Mrs etc)
     *
     * @param $value
     */
    public function setPayerTitle($value)
    {
        $this->setParameter('payerTitle', $value);
    }


    /**
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setPayerFirstname($value)
    {
        return $this->setParameter('payerFirstname', $value);
    }

    /**
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setPayerEmail($value)
    {
        return $this->setParameter('payerEmail', $value);
    }


    /**
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setPayerSurname($value)
    {
        return $this->setParameter('payerSurname', $value);
    }


    /**
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setPayerTelephone($value)
    {
        return $this->setParameter('payerTelephone', $value);
    }



    /**
     * Return customer title
     */
    public function getPayerTitle()
    {
        return  $this->getParameter('payerTitle');
    }

    /**
     * Return customer firstname
     */
    public function getPayerFirstname()
    {
        return  $this->getParameter('payerFirstname');
    }

    /**
     * Return customer surname
     */
    public function getPayerSurname()
    {
        return $this->getParameter('payerSurname');
    }

    /**
     * Return customer email
     */
    public function getPayerEmail()
    {
        return $this->getParameter('payerEmail');
    }

    /**
     * Return customer telephone
     */
    public function getPayerTelephone()
    {
        return $this->getParameter('payerTelephone');
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

        // Title
        $title = $domTree->createElement('title', $this->getPayerTitle());
        $payer->appendChild($title);

        // Firstname
        $firstname = $domTree->createElement('firstname', $this->getPayerFirstname());
        $payer->appendChild($firstname);

        // Email
        $email = $domTree->createElement('email', $this->getPayerEmail());
        $payer->appendChild($email);

        // Surname
        $surname = $domTree->createElement('surname', $this->getPayerSurname());
        $payer->appendChild($surname);

        // Telephone
        $phone_numbers = $domTree->createElement('phonenumbers');
        $home = $domTree->createElement('home', $this->getPayerTelephone());
        $phone_numbers->appendChild($home);
        $payer->appendChild($phone_numbers);

        $root->appendChild($payer);

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