<?php

// PayPal normal
$paypal = new rex_ypaypal();

// Paypal Plus
// $paypal = new rex_ypaypal_plus();

if (rex_post('finish', 'bool')) {

    // 4. Schritt: Bezahlung ausführen und Bestätigung anzeigen

    // Entweder automatisch durchgeschleifte GET-Parameter nutzen (so wie hier), oder die paymentId und payerId jeweils als Parameter übergeben
    $payment = $paypal->getPayment(/* $paymentId */);
    $paypal->executePayment($payment/*, $payerId */);
    $state = $payment->getState();

    echo 'Kauf abgeschlossen';

} elseif ($paypal->isCancelation()) {

    // Abbruch: Käufer hat auf PayPal-Seite auf "Abbrechen" geklickt

    echo 'Kauf abgebrochen';

} elseif ($paypal->isReturn()) {

    // 3. Schritt: Käufer hat Zahlung auf PayPal bestätigt
    // Bestellübersicht anzeigen

    // Payment-Objekt auslesen
    // $payment = $paypal->getPayment();

    // Entweder GET-Parameter durchschleifen (so wie hier), oder $paypal->getPayerId() und $paypal->getPaymentId() anders übertragen
    echo '<form method="POST"><button name="finish" value="1">Kauf abschließen</button></form>';

} elseif (rex_post('create', 'bool')) {

    // 2. Schritt: Payment-Objekt erstellen, und zu PayPal weiterleiten (bzw. Payment Wall anzeigen bei PayPal Plus)

    // Name und Rechnungsanschrift können scheinbar nicht vorbelegt werden

    $details = new PayPal\Api\Details();
    $details
        ->setSubtotal('9.50')
        // ->setTax()
        // ->setShipping()
    ;

    $amount = new PayPal\Api\Amount();
    $amount
        ->setCurrency('EUR')
        ->setTotal('9.50')
        ->setDetails($details)
    ;

    $item = new PayPal\Api\Item();
    $item
        ->setName('Test-Item')
        ->setDescription('Test-Beschreibung')
        ->setPrice('9.50')
        // ->setTax()
        ->setCurrency('EUR')
        ->setQuantity(1)
    ;

    $itemList = new PayPal\Api\ItemList();
    $itemList
        ->addItem($item)
        // ->setShippingAddress(/* PayPal\Api\ShippingAddress */)
    ;

    $transaction = new PayPal\Api\Transaction();
    $transaction
        ->setDescription('Testkauf')
        //->setInvoiceNumber()
        ->setAmount($amount)
        ->setItemList($itemList)
    ;

    $payment = $paypal->createPayment($transaction);

    // PayPal normal: Weiterleiten zu PayPal
    $paypal->redirect($payment);

    // PayPal Plus: Bezahlmethoden-Auswahl anzeigen und Weiter-Button (Weiterleitung zu PayPal)
    // echo $paypal->getPaymentWall($payment);
    // echo $paypal->getContinueButton('Weiter');

} else {

    // 1. Schritt: Weiter zu PayPal / Weiter zur Bezahlmethode

    echo '<form method="POST"><button name="create" value="1">Weiter zu PayPal</button></form>';

}

?>
