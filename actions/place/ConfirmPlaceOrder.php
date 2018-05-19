<?php
/**
 * @name ConfirmPlaceOrder.php
 * @desc ConfirmPlaceOrder.php
 * @author yu.jin03@ele.me
 */

class Action_ConfirmPlaceOrder extends Order_Base_Action
{
    protected $boolCheckAuth = false;
    protected $boolCheckLogin = false;
    /**
     * method post
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * params
     * @var array
     */
    protected $arrInputParams = [
        'place_order_id' => 'int|required',
        'skus' => [
            'validate' => 'json|decode|required',
            'type' => 'array',
            'params' => [
                'sku_id' => 'int|required',
                'place_amount' => 'int|required',
                'expire_date' => 'int|required',
                'place_location_id' => 'str|required',
            ],
        ],
    ];

    /**
     * @return mixed|void
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Place_ConfirmPlaceOrder();
    }

    /**
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        return $data;
    }
}