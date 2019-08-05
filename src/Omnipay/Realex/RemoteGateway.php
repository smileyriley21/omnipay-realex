<?php

namespace Omnipay\Realex;

use Omnipay\Common\AbstractGateway;
use Omnipay\Realex\Message\AuthRequest;
use Omnipay\Realex\Message\AuthResponse;
use Omnipay\Realex\Message\RemoteAbstractResponse;
use Omnipay\Realex\Message\VerifySigRequest;
use Omnipay\Realex\Message\VerifySigResponse;

/**
 * Realex Remote Gateway
 */
class RemoteGateway extends AbstractGateway
{
    public function getName()
    {
        return 'Realex Remote';
    }

    public function getDefaultParameters()
    {
        return array(
            'merchantId' => '',
            'account'    => '',
            'secret'     => '',
            '3dSecure'   => 0
        );
    }

    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    public function getAccount()
    {
        return $this->getParameter('account');
    }

    public function setAccount($value)
    {
        return $this->setParameter('account', $value);
    }

    public function getSecret()
    {
        return $this->getParameter('secret');
    }

    public function setSecret($value)
    {
        return $this->setParameter('secret', $value);
    }

    public function getRefundPassword()
    {
        return $this->getParameter('refundPassword');
    }

    /**
     * Although Omnipay terminology deals with 'refunds', you need
     * to actually supply the 'rebate' password that Realex gives you
     * in order for this to work.
     *
     * @param string $value The 'rebate' password supplied by Realex
     * @return $this
     */
    public function setRefundPassword($value)
    {
        return $this->setParameter('refundPassword', $value);
    }

    public function get3dSecure()
    {
        return $this->getParameter('3dSecure');
    }

    public function set3dSecure($value)
    {
        return $this->setParameter('3dSecure', $value);
    }

    public function purchase(array $parameters = array())
    {
        if ($this->get3dSecure()) {
            return $this->createRequest('\Omnipay\Realex\Message\EnrolmentRequest', $parameters);
        } else {
            return $this->createRequest('\Omnipay\Realex\Message\AuthRequest', $parameters);
        }
    }

    /**
     * This will always be called as the result of returning from 3D Secure.
     * Verify that the 3D Secure message we've received is legit
     */
    public function completePurchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Realex\Message\VerifySigRequest', $parameters);
    }

    public function refund(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Realex\Message\RefundRequest', $parameters);
    }

    public function void(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Realex\Message\VoidRequest', $parameters);
    }

    public function fetchTransaction(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Realex\Message\FetchTransactionRequest', $parameters);
    }


    /**
     * Create Card
     *
     * This call can be used to create a new customer or add a card
     * to an existing customer.  If a customerReference is passed in then
     * a card is added to an existing customer.  If there is no
     * customerReference passed in then a new customer is created.  The
     * response in that case will then contain both a customer token
     * and a card token, and is essentially the same as CreateCustomerRequest
     *
     * @param array $parameters
     * @return \Omnipay\Realex\Message\CreateCardRequest
     */
    public function createCard(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Realex\Message\CreateCardRequest', $parameters);
    }

    /**
     * Delete a card.
     *
     * This is normally used to delete a credit card from an existing
     * customer.
     *
     * You can delete cards from a customer or recipient. If you delete a
     * card that is currently the default card on a customer or recipient,
     * the most recently added card will be used as the new default. If you
     * delete the last remaining card on a customer or recipient, the
     * default_card attribute on the card's owner will become null.
     *
     * Note that for cards belonging to customers, you may want to prevent
     * customers on paid subscriptions from deleting all cards on file so
     * that there is at least one default card for the next invoice payment
     * attempt.
     *
     * In deference to the previous incarnation of this gateway, where
     * all CreateCard requests added a new customer and the customer ID
     * was used as the card ID, if a cardReference is passed in but no
     * customerReference then we assume that the cardReference is in fact
     * a customerReference and delete the customer.  This might be
     * dangerous but it's the best way to ensure backwards compatibility.
     *
     * @param array $parameters
     * @return \Omnipay\Realex\Message\DeleteCardRequest
     */
    public function deleteCard(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Realex\Message\DeleteCardRequest', $parameters);
    }


    /**
     * Create Payer
     *
     * To enable adding cards you have to first add a Payer
     *
     * @param array $parameters
     * @return \Omnipay\Realex\Message\CreateCardRequest
     */
    public function createPayer(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Realex\Message\CreatePayerRequest', $parameters);
    }

    /**
     * Create ReceiptIn
     *
     * Create a payment using an existing card
     *
     * @param array $parameters
     * @return \Omnipay\Realex\Message\CreateCardRequest
     */
    public function createReceiptIn(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Realex\Message\ReceiptInRequest', $parameters);
    }

    /**
     * Check the 3d secure version that a card supports
     *
     *
     * @param array $parameters
     * @return \Omnipay\Realex\Message\3DSecureVersionResponse
     */
    public function check3dSecureVersion(array $parameters = array()){

        return $this->createRequest('\Omnipay\Realex\Message\Check3DSecureVersionRequest', $parameters);

    }


    /**
     * Create and initialize a request object
     *
     * This function is usually used to create objects of type
     * Omnipay\Common\Message\AbstractRequest (or a non-abstract subclass of it)
     * and initialise them with using existing parameters from this gateway.
     *
     * Example:
     *
     * <code>
     *   class MyRequest extends \Omnipay\Common\Message\AbstractRequest {};
     *
     *   class MyGateway extends \Omnipay\Common\AbstractGateway {
     *     function myRequest($parameters) {
     *       $this->createRequest('MyRequest', $parameters);
     *     }
     *   }
     *
     *   // Create the gateway object
     *   $gw = Omnipay::create('MyGateway');
     *
     *   // Create the request object
     *   $myRequest = $gw->myRequest($someParameters);
     * </code>
     *
     * @see \Omnipay\Common\Message\AbstractRequest
     * @param string $class The request class name
     * @param array $parameters
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    protected function createRequest($class, array $parameters)
    {


        $obj = new $class($this->httpClient, $this->httpRequest);



        return $obj->initialize(array_replace($this->getParameters(), $parameters));
    }

}
