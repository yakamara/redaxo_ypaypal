<?php

use PayPal\Api\Payment;

class rex_ypaypal_plus extends rex_ypaypal
{
    const WALL_ID = 'paypal-plus-payment-wall';
    const CONTINUE_BUTTON_ID = 'paypal-plus-continue-button';

    public function getPaymentWall(Payment $payment, array $parameters = array())
    {
        return $this->getPlaceholder().$this->getJsLib().$this->getJsCode($payment, $parameters);
    }

    public function getJsLib()
    {
        return '<script src="https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js" type="text/javascript"></script>';
    }

    public function getPlaceholder()
    {
        return '<div id="'.self::WALL_ID.'"></div>';
    }

    public function getJsCode(Payment $payment, array $parameters = array())
    {
        $parameters = array_merge(array(
            'country' => $this->settings['country'],
            'language' => $this->settings['language'],
            'buttonLocation' => 'outside',
            'disableContinue' => self::CONTINUE_BUTTON_ID,
            'enableContinue' => self::CONTINUE_BUTTON_ID,
        ), $parameters);

        $parameters['mode'] = $this->settings['sandbox'] ? 'sandbox' : 'live';
        $parameters['approvalUrl'] = $payment->getApprovalLink();
        $parameters['placeholder'] = self::WALL_ID;

        return '
<script type="application/javascript">
    var ppp = PAYPAL.apps.PPP('.json_encode($parameters).');
</script>
';
    }

    public function getContinueButton($text)
    {
        return sprintf(
            '<button type="submit" id="%s" onclick="PAYPAL.apps.PPP.doCheckout();">%s</button>',
            self::CONTINUE_BUTTON_ID,
            $text
        );
    }
}
