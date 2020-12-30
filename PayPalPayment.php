<?php
/**
 * @creator David
 * @project Udictate
 */

require_once "vendor/autoload.php";
require_once "DB.class.php";

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

//namespace User;


class PayPalPayment extends DB
{
    public $userId;
    public $amount;
    public $balance;
    public $paymentsTable;
    public $apiContext;
    public $amountDeposit;

    function __construct()
    {
                $clientId = 'AZgq9NqhPK3jtjpuUy6YOBNcPcb7VlNCc6lkK_E033QeCnbTUNT9m4hYdkN431CdfySRMFM-tURwnRRI';
                $clientSecret = 'ECGr3nMw2FTSLCAKudYgh8amIdMcN_9_0GbkkeOpX11lt5HMWS9t03GI-3pI9KzCwTNZiOBPqN3APDuj';

                /**
                 * All default curl options are stored in the array inside the PayPalHttpConfig class. To make changes to those settings
                 * for your specific environments, feel free to add them using the code shown below
                 * Uncomment below line to override any default curl options.
                 */
        // \PayPal\Core\PayPalHttpConfig::$defaultCurlOptions[CURLOPT_SSLVERSION] = CURL_SSLVERSION_TLSv1_2;


                /** @var \Paypal\Rest\ApiContext $this->apiContext */
                $this->apiContext = $this->getApiContext($clientId, $clientSecret);


                /**
                 * Helper method for getting an APIContext for all calls
                 * @param string $clientId Client ID
                 * @param string $clientSecret Client Secret
                 * @return PayPal\Rest\ApiContext
                 */
    }


    public function getApiContext($clientId, $clientSecret)
    {

        // #### SDK configuration
        // Register the sdk_config.ini file in current directory
        // as the configuration source.
        /*
        if(!defined("PP_CONFIG_PATH")) {
            define("PP_CONFIG_PATH", __DIR__);
        }
        */


        // ### Api context
        // Use an ApiContext object to authenticate
        // API calls. The clientId and clientSecret for the
        // OAuthTokenCredential class can be retrieved from
        // developer.paypal.com

        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                $clientId,
                $clientSecret
            )
        );

        // Comment this line out and uncomment the PP_CONFIG_PATH
        // 'define' block if you want to use static file
        // based configuration

        $apiContext->setConfig(
            array(
                'mode' => 'sandbox',
                'log.LogEnabled' => true,
                'log.FileName' => '../PayPal.log',
                'log.LogLevel' => 'DEBUG', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
                'cache.enabled' => true,
                //'cache.FileName' => '/PaypalCache' // for determining paypal cache directory
                // 'http.CURLOPT_CONNECTTIMEOUT' => 30
                // 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
                //'log.AdapterFactory' => '\PayPal\Log\DefaultLogFactory' // Factory class implementing \PayPal\Log\PayPalLogFactory
            )
        );

        // Partner Attribution Id
        // Use this header if you are a PayPal partner. Specify a unique BN Code to receive revenue attribution.
        // To learn more or to request a BN Code, contact your Partner Manager or visit the PayPal Partner Portal
        // $this->apiContext->addRequestHeader('PayPal-Partner-Attribution-Id', '123123123');

        $this->apiContext=$apiContext;

        return $this->apiContext;
    }

    function getBaseUrl()
    {
        $currentPath = $_SERVER['PHP_SELF'];
        $pathInfo = pathinfo($currentPath);
        $hostName = $_SERVER['HTTP_HOST'];
        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https://'?'https://':'http://';
        return $protocol.$hostName.$pathInfo['dirname']."/";
    }

    // mama paul 0712528046

    public function makeDeposit($amountDeposit){

        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        $amount = new Amount();
        $amount->setCurrency("USD")
            ->setTotal($amountDeposit);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setDescription("Deposit into account Wallet")
            ->setInvoiceNumber(uniqid());

        $baseUrl = $this->getBaseUrl();
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl("$baseUrl/ExecutePayment.php?success=true")
            ->setCancelUrl("$baseUrl/ExecutePayment.php?success=false");

        $payment = new Payment();
        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));

        $request = clone $payment;
        print_r($request);

        try {
            $payment->create($this->apiContext);
        } catch (Exception $ex) {

            exit(1);
        }

        return $payment->getApprovalLink();


    }

    public function ExecutePayment(){
        if (isset($_GET['success']) && $_GET['success'] == 'true') {

            $paymentId = $_GET['paymentId'];
            $payment = Payment::get($paymentId, $this->apiContext);

            $execution = new PaymentExecution();
            $execution->setPayerId($_GET['PayerID']);

            $transaction = new Transaction();


            try {
                $result = $payment->execute($execution, $this->apiContext);

                $payment->getId();
                print_r($result);

                try {
                    $payment = Payment::get($paymentId, $this->apiContext);
                    print_r($payment);
                } catch (Exception $ex) {
                    $ex;
                    exit(1);
                }
            } catch (Exception $ex) {
                return $ex;
                exit(1);
            }

            $payment->getId();
            print_r($payment);

            return $payment;
        } else {
            return json_encode(["message"=>"user cancelled transaction"]);
            exit;
        }

    }

    public function refundPayment(){

    }

}