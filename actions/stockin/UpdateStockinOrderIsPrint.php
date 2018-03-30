<?php
/**
 * @name Action_UpdateStockinOrderIsPrint
 * @desc Action_UpdateStockinOrderIsPrint
 * @author huabang.xue@ele.me
 */

class Action_UpdateStockinOrderIsPrint extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'stockin_order_ids' => 'regex|patern[/^(ASN|SIO)\d{13}(\,(ASN|SIO)\d{13})*$/]',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * construct
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Stockin_UpdateStockinOrderIsPrint();
    }

    /**
     * format
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        return $data;
    }
}