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
     *
     */
    public function sendOrderDataToFeefo($order)
    {
        //api details
        $apiKey = Config::inst()->get('EntersaleremotelyAPIConnector', 'api_key');
        $merchant = Config::inst()->get('EntersaleremotelyAPIConnector', 'merchant_identifier');

        //member specifi details
        $member = $order->Member();
        $email = $member->Email;
        $name = $member->FirstName;
        $locale = $member->Locale;
        $customerRef = $member->ID;

        //order specific details
        $orderRef = $order->ID;
        $currency = $order->CurrencyUsed()->Code;
        $date = date('Y-m-d', strtotime($order->Created));;

        foreach ($order->Items() as $orderItem) {
            $amount = $orderItem->CalculatedTotal;
            $product = $orderItem->Product();
            $link = Director::absoluteURL($product->Link());

            $params = [
                'apikey' => $apiKey,
                'merchantidentifier' => $merchant,
                'email' =>$email,
                'name' => $name,
                'locale' => $locale,
                'customerref' => $customerRef,
                'orderref' => $orderRef,
                'date' => $date,
                'currency' => $currency,
                'amount ' => $orderItem->CalculatedTotal,
                'description' => $product->Title,
                'productsearchcode'=> $product->Title,
                'productlink' => 'https://www.picspeanutbutter.com/buy/buy-online/380g-smooth-no-salt/'
            ];
            var_dump($params);
            $this->sendCurlRequest($params);
            die('sdfdsf');
            # code...
        }




        return 'test';
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

        var_dump($reply);
        return $reply;
    }


}
