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
     * Update Card
     *
     * If you need to update only some card details, like the billing
     * address or expiration date, you can do so without having to re-enter
     * the full card details. Realex also works directly with card networks
     * so that your customers can continue using your service without
     * interruption.
     *
     * When you update a card, Realex will automatically validate the card.
     *
     * This requires both a customerReference and a cardReference.
     *
     * @link https://stripe.com/docs/api#update_card
     * @param array $parameters
     * @return \Omnipay\Realex\Message\UpdateCardRequest
     */
    public function updateCard(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Realex\Message\UpdateCardRequest', $parameters);
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

}
