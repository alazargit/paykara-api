<?php

namespace PayKara\Api;

use PayKara\Common\PayKaraModel;

/**
 * Class RedirectUrls
 * @property string returnUrl
 * @property string cancelUrl
 *
 */

class RedirectUrls extends PayKaraModel
{
    public function setSuccessUrl($url)
    {
        $this->successUrl = $url;
        return $this;
    }

    public function getSuccessUrl()
    {
        return $this->successUrl;
    }

    public function setCancelUrl($url)
    {
        $this->cancelUrl = $url;
        return $this;
    }

    public function getCancelUrl()
    {
        return $this->cancelUrl;
    }
}