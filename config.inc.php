<?php

$REX['ADDON']['author']['ypaypal'] = 'Gregor Harlan';
$REX['ADDON']['version']['ypaypal'] = '0.2';
$REX['ADDON']['supportpage']['ypaypal'] = 'www.redaxo.org/de/forum';
$REX['ADDON']['perm']['ypaypal'] = 'admin[]';

$REX['ADDON']['settings']['ypaypal'] = array(
    'sandbox' => true,
    'clientId' => '',
    'secret' => '',
    'country' => 'DE',
    'language' => 'de_DE',
);

require_once __DIR__ . '/vendor/autoload.php';

// Webhook in den PayPal App Einstellungen aktivieren: https://developer.paypal.com/developer/applications
// Alle relevanten "Payment Sale X" Events auswählen
// Als URL die Url der Website mit Parameter paypal_webhook=1 eingeben
if (rex_get('paypal_webhook', 'bool')) {
    $paypal = new rex_ypaypal();
    $event = $paypal->getWebhookEvent();
    $sale = $event->getResource();
    $state = $sale->state;

    exit;
}
