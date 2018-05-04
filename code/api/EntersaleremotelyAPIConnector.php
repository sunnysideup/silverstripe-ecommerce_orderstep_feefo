<?php

/**
 * Class used to connecto the the Entersaleremotely API
 *@author nicolaas [at] sunnysideup.co.nz
 */


class EntersaleremotelyAPIConnector extends Object
{

    /**
     * REQUIRED!
     * @var String
     */
    private static $api_key = "";


    /**
     * REQUIRED!
     * @var String
     */
    private static $merchant_identifier = "";


    /**
     * sends the order data from the website to feefo using the Entersaleremotely API
     *
     * @param  Order $order - the order to be assessed
     * @param  int $delay - number of days to wait before feefo will send the feedback email
     *
     * @return array $messages - an array of message regarding the success of each curl request - one for each Order Item
     */
    public function sendOrderDataToFeefo($order, $delay = 0)
    {
        $messages = [];
        //api details
        $apiKey = Config::inst()->get('EntersaleremotelyAPIConnector', 'api_key');
        $merchant = Config::inst()->get('EntersaleremotelyAPIConnector', 'merchant_identifier');

        //member specific details
        $member = $order->Member();
        $email = $member->Email;
        $name = $member->FirstName;
        $locale = $member->Locale;
        $customerRef = $member->ID;

        //order specific details
        $orderRef = $order->ID;
        $currency = $order->CurrencyUsed()->Code;
        $dateAsString = strtotime($order->Created);
        $date = date('Y-m-d', $dateAsString);

        $feedbackDate = '';
        if($delay){
            $feedbackDate = date( 'Y-m-d', strtotime('+' . $delay . ' days', $dateAsString) );
        }

        foreach ($order->Items() as $orderItem) {
            $amount = $orderItem->CalculatedTotal;
            $product = $orderItem->Product();
            $productTitle = $product->Title;
            $link = Director::absoluteURL($product->Link());

            $params = [
                'apikey' => $apiKey,
                'merchantidentifier' => $merchant,
                'feedbackdate' => $feedbackDate,
                'email' =>$email,
                'name' => $name,
                'locale' => $locale,
                'customerref' => $customerRef,
                'orderref' => $orderRef,
                'date' => $date,
                'currency' => $currency,
                'amount ' => $orderItem->CalculatedTotal,
                'description' => $productTitle,
                'productsearchcode'=> $productTitle,
                'productlink' => 'https://www.picspeanutbutter.com/buy/buy-online/380g-smooth-no-salt/'
            ];

            $result = $this->sendCurlRequest($params);

            $result .= ' Order ID: ' . $orderRef . '; Product: ' . $productTitle . ';';

            array_push($messages, $result);
        }

        return $messages;
    }

    /**
     * performs the curl request
     *
     * @param  array $params - the data to send to FEEFO
     *
     * @return string $reply
     */
    public function sendCurlRequest($params)
    {
        $reply = '';
        $url = 'https://api.feefo.com/api/entersaleremotely';

        $data = http_build_query($params, '', '&');

        $ch=curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $reply = curl_exec($ch);

        curl_close($ch);
        return $reply;
    }


}
