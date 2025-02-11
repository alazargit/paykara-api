<?php namespace PayKara\Api;

use PayKara\Common\PayKaraModel;

/**
 * Class Transaction
 * @property \PayKara\Api\Amount amount
 *
 */

class Transaction extends PayKaraModel
{

    /**
     * @param \PayKara\Api\Amount $amount
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function getAmount()
    {
        return $this->amount;
    }
}