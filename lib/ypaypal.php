<?php

use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\WebhookEvent;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class rex_ypaypal
{
    protected $settings;

    private $apiContext;

    public function __construct()
    {
        $this->settings = OOAddon::getProperty('ypaypal', 'settings');
    }

    public function createPayment(Transaction $transaction, $redirectUrl = null)
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        if ($redirectUrl instanceof RedirectUrls) {
            $redirectUrls = $redirectUrl;
        } else {
            if (!$redirectUrl) {
                $redirectUrl = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
                $redirectUrl .= isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'];
                $redirectUrl .= $_SERVER['REQUEST_URI'];
            }
            $redirectUrls = new RedirectUrls();
            $redirectUrls
                ->setReturnUrl($this->addParamToUrl($redirectUrl, 'paypal_return', 1))
                ->setCancelUrl($this->addParamToUrl($redirectUrl, 'paypal_cancel', 1))
            ;
        }

        $payment = new Payment();
        $payment
            ->setIntent('sale')
            ->setPayer($payer)
            ->setTransactions(array($transaction))
            ->setRedirectUrls($redirectUrls)
        ;

        $payment->create($this->getApiContext());

        return $payment;
    }

    public function redirect(Payment $payment)
    {
        header('Location: '.$payment->getApprovalLink());
        exit;
    }

    public function isCancelation()
    {
        return rex_get('paypal_cancel', 'bool') && rex_get('token', 'bool');
    }

    public function isReturn()
    {
        return rex_get('paypal_return', 'bool') && rex_get('token', 'bool');
    }

    public function getPayerId()
    {
        return rex_get('PayerID', 'string');
    }

    public function getPaymentId()
    {
        return rex_get('paymentId', 'string');
    }

    public function getPayment($paymentId = null)
    {
        $paymentId = $paymentId ? $paymentId : $this->getPaymentId();

        return Payment::get($paymentId, $this->getApiContext());
    }

    public function executePayment(Payment $payment, $payerId = null)
    {
        $payerId = $payerId ? $payerId : $this->getPayerId();

        $execution = new PaymentExecution();
        $execution->setPayerId($payerId);

        $payment->execute($execution, $this->getApiContext());
    }

    public function getWebhookEvent()
    {
        $body = file_get_contents('php://input');

        return WebhookEvent::validateAndGetReceivedEvent($body, $this->getApiContext());
    }

    private function getApiContext()
    {
        if (!$this->apiContext) {
            $this->apiContext = new ApiContext(new OAuthTokenCredential(
                $this->settings['clientId'],
                $this->settings['secret']
            ));
        }

        return $this->apiContext;
    }

    private function addParamToUrl($url, $key, $value)
    {
        $url .= false === strpos($url, '?') ? '?' : '&';
        $url .= $key.'='.$value;

        return $url;
    }
}
