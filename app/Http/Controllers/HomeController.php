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
                'AXq-XpRWY0sS2HLlMcIUxUapw73R676WmX3cELjRxp7zOqPbymolCSn-Lf1W3zyQC75j1GIJiRDoS76z',
                'EK4_IAuGXs48wlJ9N3yRMUBr4vTYIl4RscVfyNs6R_s_43cC5H8kyazEKmxpsdX2GmstUNgJhMt-kSmR'
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
        $amount->setCurrency('USD')->setTotal('17.50');

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
                'AXq-XpRWY0sS2HLlMcIUxUapw73R676WmX3cELjRxp7zOqPbymolCSn-Lf1W3zyQC75j1GIJiRDoS76z',
                'EK4_IAuGXs48wlJ9N3yRMUBr4vTYIl4RscVfyNs6R_s_43cC5H8kyazEKmxpsdX2GmstUNgJhMt-kSmR'
            )
        );

        try {
            $orderResult = Payment::get('PAY-1KK263486S429580SLDFIITA', $apiContext);

            echo '<pre>';
            print_r($orderResult);
            echo '</pre>';
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
}
