<?php



class OrderStepFeefo extends OrderStep
{
    private static $verbose = false;


    private static $defaults = array(
        'CustomerCanEdit' => 0,
        'CustomerCanCancel' => 0,
        'CustomerCanPay' => 0,
        'Name' => 'Send Order To Feefo',
        'Code' => 'FEEFO',
        "ShowAsInProcessOrder" => true,
        "HideStepFromCustomer" => true
    );


    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        return $fields;
    }

    public function initStep(Order $order)
    {
        return true;
    }

    public function doStep(Order $order)
    {
        $api = Injector::inst()->get('EntersaleremotelyAPIConnector');
        try {
            $result = $api->sendOrderDataToFeefo($order);
        }
        catch (Exception $e) {
            $e->getMessage();
        }

        return false;
    }

    /**
     * can continue if emails has been sent or if there is no need to send a receipt.
     * @param DataObject $order Order
     * @return DataObject | Null - DataObject = next OrderStep
     **/
    public function nextStep(Order $order)
    {

        return null;
    }

    /**
     * For some ordersteps this returns true...
     * @return Boolean
     **/
    protected function hasCustomerMessage()
    {
        return false;
    }

    /**
     * Explains the current order step.
     * @return String
     */
    protected function myDescription()
    {
        return "The customer and order data is sent to Feefo via the Entersaleremotely API.";
    }


}
