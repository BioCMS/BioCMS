<?php

namespace App\Http\Controllers;

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                'ARHBJsX4QGnmqw2X0s_nURyoJYM0sP6aIDJM7cpMZiiy_8QCT1y6ZCHnKj8hctzS2i3H_LPjKma1U3Zv',
                'EEUYrG_jIqDzzOOZSigtYPeeiYJTp2G6fqu6hsXy4KaiNomZn3Uql0KAbsvN-PUipmkOX-PPdbfh7Wcf'
            )
        );

        $invoiceNumber = uniqid();

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $item = new Item();

        $item->setName('Test 1')
            ->setCurrency('USD')
            ->setQuantity(1)
            ->setPrice('17.50');

        $itemList = new ItemList();
        $itemList->setItems([$item]);

        $details = new Details();
        $details->setShipping('10.50')
            ->setTax('10.50')
            ->setSubtotal('10.50');

        $amount = new Amount();
        $amount->setCurrency('USD')->setTotal('17.50')->setDetails($details);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription('Description')
            ->setInvoiceNumber($invoiceNumber);

        $baseUrl = 'http://localhost:8000/check';
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($baseUrl . '?success=true')
            ->setCancelUrl($baseUrl . '?success=false');

        $payment = new Payment();
        $payment->setIntent('order')
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions([$transaction]);
        $request = clone $payment;

        try {
            $payment->create($apiContext);
        } catch (PayPalConnectionException $e) {
            throw new \Exception($e->getData());
        }

        $approvalUrl = $payment->getApprovalLink();

        $data = [
            'approvalUrl' => $approvalUrl,
            'invoiceNumber' => $invoiceNumber,
            'paymentID' => $payment->getId(),
        ];
        return view('home', $data);
    }

    public function checkInvoice() {
        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                'ARHBJsX4QGnmqw2X0s_nURyoJYM0sP6aIDJM7cpMZiiy_8QCT1y6ZCHnKj8hctzS2i3H_LPjKma1U3Zv',
                'EEUYrG_jIqDzzOOZSigtYPeeiYJTp2G6fqu6hsXy4KaiNomZn3Uql0KAbsvN-PUipmkOX-PPdbfh7Wcf'
            )
        );

        try {
            $orderResult = Payment::get('PAY-3KU3327671913491HLDFMRCA', $apiContext);

            echo '<pre>';
            print_r($orderResult);
            echo '</pre>';
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
}
