<?php
namespace PayKara\Api;

use PayKara\Common\PayKaraModel;

/**
 * Class Payer
 * @property string paymentMethod
 *
 */
class Payer extends PayKaraModel
{

    /**
     * Valid Values: ["paykara"]
     * method will be like paykara, paypal, stripe etc
     * @param  string  $method
     * @return $this
     */
    public function setPaymentMethod($method)
    {
        $this->paymentMethod = $method;
        return $this;
    }

    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

}
