<?php



class OrderStepFeefo extends OrderStep
{

    /**
     * The OrderStatusLog that is relevant to the particular step.
     *
     * @var string
     */
    protected $relevantLogEntryClassName = 'OrderStatusLog_FeefoLog';

    private static $db = array(
        'SendData' => 'Boolean',
        'FeedbackDelay' => 'Int'
    );


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
        $fields->removeByName('FeedbackDelay');

        $fields->addFieldToTab(
            'Root.Feefo',
            NumericField::create(
                'FeedbackDelay',
                'Feedback Delay'
            )
        );

        return $fields;
    }

    public function initStep(Order $order)
    {
        return true;
    }

    public function doStep(Order $order)
    {
        if ($this->SendData) {

            $api = Injector::inst()->get('EntersaleremotelyAPIConnector');

            $result = $api->sendOrderDataToFeefo($order, $this->FeedbackDelay);
            $result = $this->convertArrayToHTMLList($result);



            $className = $this->getRelevantLogEntryClassName();

            if (class_exists($className)) {
                $obj = $className::create();
                if (is_a($obj, Object::getCustomClass('OrderStatusLog'))) {
                    $obj->OrderID = $order->ID;
                    $obj->Title = $this->Name;
                    $obj->DetailedInfo = $result;
                    $obj->write();
                }
            }
        }

        return true;
    }

    /**
     * can continue if emails has been sent or if there is no need to send a receipt.
     * @param DataObject $order Order
     * @return DataObject | Null - DataObject = next OrderStep
     **/
    public function nextStep(Order $order)
    {
        return parent::nextStep($order);
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

    public function convertArrayToHTMLList($array){
        $html = '<ul>';
        foreach($array as $arrayItem){
            $html .= '<li>' . $arrayItem . '</li>';
        }
        $html .= '</ul>';
        return $html;
    }
}
