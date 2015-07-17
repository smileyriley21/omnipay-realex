<?php
/**
 * Realex Create Credit Card Request
 */
namespace Omnipay\Realex\Message;
/**
 * Realex Create Credit Card Request
 *
 * In using RealVault, each customer is given a token reference called a payer reference (or just payerref)
 * and each payment method, i.e. the card to be stored in RealVault, is given a payment reference
 * (or card reference). These references are assigned by the merchant.
 *
 * Once the details are captured, the merchant can use the full suite of RealVault XML API messages to
 * manage the payer and perform transactions using references/tokens to the payer and card rather than
 * card numbers. Therefore it is important that the merchant store the references/tokens that are
 * returned in their database
 *
 * Example.  This example assumes that you have already created a
 * customer, and that the customer reference is stored in $customer_id.
 * See CreateCustomerRequest for the first part of this transaction.
 *
 * <code>
 *   // Create a credit card object
 *   // This card can be used for testing.
 *   // The CreditCard object is also used for creating customers.
 *   $new_card = new CreditCard(array(
 *               'firstName'    => 'Example',
 *               'lastName'     => 'Customer',
 *               'number'       => '5555555555554444',
 *               'expiryMonth'  => '01',
 *               'expiryYear'   => '2020',
 *               'cvv'          => '456',
 *               'email'                 => 'customer@example.com',
 *               'billingAddress1'       => '1 Lower Creek Road',
 *               'billingCountry'        => 'AU',
 *               'billingCity'           => 'Upper Swan',
 *               'billingPostcode'       => '6999',
 *               'billingState'          => 'WA',
 *   ));
 *
 *   // Do a create card transaction on the gateway
 *   $response = $gateway->createCard(array(
 *       'card'              => $new_card,
 *       'customerReference' => $customer_id,
 *   ))->send();
 *   if ($response->isSuccessful()) {
 *       echo "Gateway createCard was successful.\n";
 *       // Find the card ID
 *       $card_id = $response->getCardReference();
 *       echo "Card ID = " . $card_id . "\n";
 *   }
 * </code>
 *
 * @see CreateCustomerRequest
 * @link https://resourcecentre.realexpayments.com/documents/pdf.html?id=152
 */

class CreateCardRequest extends AbstractRequest{

    protected $endpoint = 'https://epage.payandshop.com/epage-remote-plugins.cgi ';



    public function getData()
    {
        $data = array();
        // Only set the description if we are creating a new customer.
        if (!$this->getCustomerReference()) {
            $data['description'] = $this->getDescription();
        }
        if ($this->getSource()) {
            $data['source'] = $this->getSource();
        } elseif ($this->getToken()) {
            $data['source'] = $this->getToken();
        } elseif ($this->getCard()) {
            $this->getCard()->validate();
            $data['source'] = $this->getCardData();
            // Only set the email address if we are creating a new customer.
            if (!$this->getCustomerReference()) {
                $data['email'] = $this->getCard()->getEmail();
            }
        } else {
            // one of token or card is required
            $this->validate('source');
        }
        return $data;
    }

    public function getEndpoint()
    {
        if ($this->getCustomerReference()) {
            // Create a new card on an existing customer
            return $this->endpoint . '/customers/' .
            $this->getCustomerReference() . '/cards';
        }
        // Create a new customer and card
        return $this->endpoint . '/customers';
    }
}